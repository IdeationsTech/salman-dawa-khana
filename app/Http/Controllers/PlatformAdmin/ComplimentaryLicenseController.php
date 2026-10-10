<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ComplimentaryLicenseController extends Controller
{
    public function create(int $clinic): View
    {
        $clinicRecord = DB::table('clinics')
            ->where('clinic_id', $clinic)
            ->first();

        abort_if(! $clinicRecord, 404);

        return view('platform-admin.clinics.complimentary', [
            'clinic' => $clinicRecord,
        ]);
    }

    public function store(
        Request $request,
        int $clinic
    ): RedirectResponse {
        $validated = $request->validate([
            'reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        DB::transaction(function () use (
            $request,
            $clinic,
            $validated
        ) {
            $clinicRecord = DB::table('clinics')
                ->where('clinic_id', $clinic)
                ->lockForUpdate()
                ->first();

            abort_if(! $clinicRecord, 404);

            if (
                ! in_array(
                    $clinicRecord->onboarding_status,
                    ['pending_review', 'approved', 'suspended'],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'reason' =>
                        'A rejected clinic cannot receive a license.',
                ]);
            }

            $plan = DB::table('subscription_plans')
                ->where('plan_code', 'lifetime')
                ->lockForUpdate()
                ->first();

            if (
                ! $plan ||
                $plan->term_type !== 'lifetime'
            ) {
                throw ValidationException::withMessages([
                    'reason' =>
                        'The Lifetime plan is missing or misconfigured.',
                ]);
            }

            $oldLicense = null;

            if ($clinicRecord->current_license_id !== null) {
                $oldLicense = DB::table('clinic_licenses')
                    ->where(
                        'clinic_license_id',
                        $clinicRecord->current_license_id
                    )
                    ->where('clinic_id', $clinic)
                    ->lockForUpdate()
                    ->first();

                if (! $oldLicense) {
                    throw ValidationException::withMessages([
                        'reason' =>
                            'The current license does not belong to this clinic.',
                    ]);
                }
            } elseif (
                DB::table('clinic_licenses')
                    ->where('clinic_id', $clinic)
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'reason' =>
                        'This clinic has license history but no current license. Review its records first.',
                ]);
            }

            $now = now();
            $adminId = Auth::guard('platform_admin')->id();

            if ($oldLicense) {
                DB::table('clinic_licenses')
                    ->where(
                        'clinic_license_id',
                        $oldLicense->clinic_license_id
                    )
                    ->update([
                        'status' => 'replaced',
                        'updated_at' => $now,
                    ]);

                $this->recordAudit(
                    $request,
                    $clinic,
                    'license.replaced',
                    'clinic_licenses',
                    $oldLicense->clinic_license_id,
                    ['status' => $oldLicense->status],
                    [
                        'status' => 'replaced',
                        'reason' => $validated['reason'],
                    ]
                );
            }

            $licenseId = DB::table('clinic_licenses')
                ->insertGetId([
                    'clinic_id' => $clinic,
                    'subscription_plan_id' =>
                        $plan->subscription_plan_id,
                    'issued_by_platform_admin_id' => $adminId,
                    'status' => 'active',
                    'grant_type' => 'complimentary',
                    'starts_at' => $now,
                    'ends_at' => null,
                    'grant_reason' => $validated['reason'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'clinic_license_id');

            $updates = [
                'current_license_id' => $licenseId,
                'updated_at' => $now,
            ];

            if (
                $clinicRecord->onboarding_status ===
                'pending_review'
            ) {
                $updates['onboarding_status'] = 'approved';
                $updates['reviewed_by_platform_admin_id'] =
                    $adminId;
                $updates['reviewed_at'] = $now;
                $updates['rejection_reason'] = null;
            }

            DB::table('clinics')
                ->where('clinic_id', $clinic)
                ->update($updates);

            if (
                $clinicRecord->onboarding_status ===
                'pending_review'
            ) {
                $ownerRoleId = DB::table('roles')
                    ->where('name', 'Owner')
                    ->value('role_id');

                $owner = DB::table('users')
                    ->where('clinic_id', $clinic)
                    ->where('role_id', $ownerRoleId)
                    ->lockForUpdate()
                    ->first();

                if (! $owner) {
                    throw ValidationException::withMessages([
                        'reason' =>
                            'This clinic has no owner account.',
                    ]);
                }

                DB::table('users')
                    ->where('user_id', $owner->user_id)
                    ->update([
                        'is_active' => true,
                        'updated_at' => $now,
                    ]);

                $this->recordAudit(
                    $request,
                    $clinic,
                    'user.owner_activated',
                    'users',
                    $owner->user_id,
                    [
                        'is_active' =>
                            (bool) $owner->is_active,
                    ],
                    ['is_active' => true]
                );
            }

            /*
             * A zero-value record makes the free grant visible
             * in payment history without presenting it as cash received.
             */
            $paymentId = DB::table('license_payments')
                ->insertGetId([
                    'clinic_license_id' => $licenseId,
                    'recorded_by_platform_admin_id' => $adminId,
                    'verified_by_platform_admin_id' => null,
                    'amount' => 0,
                    'currency' => $plan->currency,
                    'payment_method' => 'complimentary',
                    'reference_no' => null,
                    'paid_at' => $now,
                    'status' => 'complimentary',
                    'receipt_path' => null,
                    'verified_at' => null,
                    'notes' => $validated['reason'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'license_payment_id');

            $this->recordAudit(
                $request,
                $clinic,
                'license.complimentary_issued',
                'clinic_licenses',
                $licenseId,
                null,
                [
                    'plan_code' => 'lifetime',
                    'grant_type' => 'complimentary',
                    'status' => 'active',
                    'previous_license_id' =>
                        $oldLicense?->clinic_license_id,
                    'reason' => $validated['reason'],
                ]
            );

            $this->recordAudit(
                $request,
                $clinic,
                'license.complimentary_recorded',
                'license_payments',
                $paymentId,
                null,
                [
                    'clinic_license_id' => $licenseId,
                    'amount' => '0.00',
                    'currency' => $plan->currency,
                    'payment_method' => 'complimentary',
                    'status' => 'complimentary',
                ]
            );
        }, 3);

        return redirect()
            ->route('platform-admin.clinics.show', $clinic)
            ->with(
                'success',
                'Complimentary Lifetime license issued.'
            );
    }

    private function recordAudit(
        Request $request,
        int $clinicId,
        string $action,
        string $entityType,
        int $entityId,
        ?array $oldValues,
        array $newValues
    ): void {
        DB::table('platform_audit_logs')->insert([
            'platform_admin_id' =>
                Auth::guard('platform_admin')->id(),
            'clinic_id' => $clinicId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues === null
                ? null
                : json_encode(
                    $oldValues,
                    JSON_THROW_ON_ERROR
                ),
            'new_values' => json_encode(
                $newValues,
                JSON_THROW_ON_ERROR
            ),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit(
                (string) $request->userAgent(),
                500,
                ''
            ),
            'created_at' => now(),
        ]);
    }
}