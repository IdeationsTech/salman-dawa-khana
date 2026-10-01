<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClinicEditController extends Controller
{
    public function edit(int $clinic): View
    {
        $clinicRecord = DB::table('clinics')
            ->where('clinic_id', $clinic)
            ->first();

        abort_if(! $clinicRecord, 404);

        $license = $clinicRecord->current_license_id
            ? DB::table('clinic_licenses as licenses')
                ->join(
                    'subscription_plans as plans',
                    'plans.subscription_plan_id',
                    '=',
                    'licenses.subscription_plan_id'
                )
                ->where(
                    'licenses.clinic_license_id',
                    $clinicRecord->current_license_id
                )
                ->where('licenses.clinic_id', $clinic)
                ->select([
                    'licenses.*',
                    'plans.name as plan_name',
                ])
                ->first()
            : null;

        $plans = DB::table('subscription_plans')
            ->where('is_active', true)
            ->whereIn('plan_code', [
                'monthly',
                'yearly',
                'lifetime',
            ])
            ->orderBy('sort_order')
            ->get();

        $licenseHistory = DB::table('clinic_licenses as licenses')
            ->join(
                'subscription_plans as plans',
                'plans.subscription_plan_id',
                '=',
                'licenses.subscription_plan_id'
            )
            ->where('licenses.clinic_id', $clinic)
            ->select([
                'licenses.clinic_license_id',
                'licenses.status',
                'licenses.starts_at',
                'licenses.ends_at',
                'plans.name as plan_name',
            ])
            ->orderByDesc('licenses.clinic_license_id')
            ->get();

        return view('platform-admin.clinics.edit', [
            'clinic' => $clinicRecord,
            'license' => $license,
            'plans' => $plans,
            'licenseHistory' => $licenseHistory,
        ]);
    }

    public function changeStatus(
        Request $request,
        int $clinic
    ): RedirectResponse {
        $validated = $request->validate([
            'action' => [
                'required',
                Rule::in(['suspend', 'reactivate']),
            ],
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

            $action = $validated['action'];

            if (
                $action === 'suspend' &&
                $clinicRecord->onboarding_status !== 'approved'
            ) {
                throw ValidationException::withMessages([
                    'action' =>
                        'Only an approved clinic can be suspended.',
                ]);
            }

            if (
                $action === 'reactivate' &&
                $clinicRecord->onboarding_status !== 'suspended'
            ) {
                throw ValidationException::withMessages([
                    'action' =>
                        'Only a suspended clinic can be reactivated.',
                ]);
            }

            if (
                $action === 'reactivate' &&
                $clinicRecord->saas_customer_id !== null
            ) {
                $license = DB::table('clinic_licenses')
                    ->where(
                        'clinic_license_id',
                        $clinicRecord->current_license_id
                    )
                    ->where('clinic_id', $clinic)
                    ->lockForUpdate()
                    ->first();

                if (
                    ! $license ||
                    $license->status !== 'active' ||
                    $license->starts_at > now() ||
                    (
                        $license->ends_at !== null &&
                        $license->ends_at <= now()
                    )
                ) {
                    throw ValidationException::withMessages([
                        'action' =>
                            'Assign a valid subscription before reactivating this clinic.',
                    ]);
                }
            }

            $newStatus = $action === 'suspend'
                ? 'suspended'
                : 'approved';

            $now = now();
            $adminId = Auth::guard('platform_admin')->id();

            DB::table('clinics')
                ->where('clinic_id', $clinic)
                ->update([
                    'onboarding_status' => $newStatus,
                    'reviewed_by_platform_admin_id' => $adminId,
                    'reviewed_at' => $now,
                    'updated_at' => $now,
                ]);

            $this->recordAudit(
                $request,
                $clinic,
                'clinic.' . (
                    $action === 'suspend'
                        ? 'suspended'
                        : 'reactivated'
                ),
                'clinics',
                $clinic,
                [
                    'onboarding_status' =>
                        $clinicRecord->onboarding_status,
                ],
                [
                    'onboarding_status' => $newStatus,
                    'reason' => $validated['reason'],
                ]
            );
        }, 3);

        return redirect()
            ->route('platform-admin.clinics.edit', $clinic)
            ->with(
                'success',
                $validated['action'] === 'suspend'
                    ? 'Clinic suspended.'
                    : 'Clinic reactivated.'
            );
    }

    public function changePlan(
        Request $request,
        int $clinic
    ): RedirectResponse {
        $validated = $request->validate([
            'plan_code' => [
                'required',
                Rule::in(['monthly', 'yearly', 'lifetime']),
            ],
            'payment_method' => [
                'required',
                Rule::in(['cash', 'bank_transfer']),
            ],
            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'max:9999999999.99',
            ],
            'paid_at' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'reference_no' => [
                'nullable',
                'string',
                'max:100',
            ],
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
                    ['approved', 'suspended'],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'plan_code' =>
                        'Approve the clinic before assigning a subscription.',
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
                        'plan_code' =>
                            'The current license does not belong to this clinic.',
                    ]);
                }
            } elseif (
                DB::table('clinic_licenses')
                    ->where('clinic_id', $clinic)
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'plan_code' =>
                        'This clinic has license history but no current license. Review its records first.',
                ]);
            }

            $plan = DB::table('subscription_plans')
                ->where('plan_code', $validated['plan_code'])
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (
                ! $plan ||
                $plan->term_type !== $validated['plan_code'] ||
                (float) $plan->price <= 0
            ) {
                throw ValidationException::withMessages([
                    'plan_code' =>
                        'Select an active plan with a configured price.',
                ]);
            }

            if (
                round((float) $validated['amount'], 2) <
                round((float) $plan->price, 2)
            ) {
                throw ValidationException::withMessages([
                    'amount' =>
                        'Verify full payment before issuing the subscription.',
                ]);
            }

            $adminId = Auth::guard('platform_admin')->id();
            $now = now();

            $endsAt = match ($plan->term_type) {
                'monthly' => $now->copy()->addMonthNoOverflow(),
                'yearly' => $now->copy()->addYearNoOverflow(),
                'lifetime' => null,
            };

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
                    'grant_type' => 'paid',
                    'starts_at' => $now,
                    'ends_at' => $endsAt,
                    'grant_reason' => $validated['reason'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'clinic_license_id');

            DB::table('clinics')
                ->where('clinic_id', $clinic)
                ->update([
                    'current_license_id' => $licenseId,
                    'updated_at' => $now,
                ]);

            $paidAt = Carbon::parse(
                $validated['paid_at']
            )->startOfDay();

            $paymentId = DB::table('license_payments')
                ->insertGetId([
                    'clinic_license_id' => $licenseId,
                    'recorded_by_platform_admin_id' => $adminId,
                    'verified_by_platform_admin_id' => $adminId,
                    'amount' => $validated['amount'],
                    'currency' => $plan->currency,
                    'payment_method' =>
                        $validated['payment_method'],
                    'reference_no' =>
                        $validated['reference_no'] ?? null,
                    'paid_at' => $paidAt,
                    'status' => 'verified',
                    'verified_at' => $now,
                    'receipt_path' => null,
                    'notes' => $validated['reason'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'license_payment_id');

            $this->recordAudit(
                $request,
                $clinic,
                'license.issued',
                'clinic_licenses',
                $licenseId,
                null,
                [
                    'plan_code' => $plan->plan_code,
                    'status' => 'active',
                    'previous_license_id' =>
                        $oldLicense?->clinic_license_id,
                    'reason' => $validated['reason'],
                ]
            );

            $this->recordAudit(
                $request,
                $clinic,
                'license.payment_verified',
                'license_payments',
                $paymentId,
                null,
                [
                    'clinic_license_id' => $licenseId,
                    'amount' => $validated['amount'],
                    'currency' => $plan->currency,
                    'payment_method' =>
                        $validated['payment_method'],
                    'status' => 'verified',
                ]
            );
        }, 3);

        return redirect()
            ->route('platform-admin.clinics.edit', $clinic)
            ->with(
                'success',
                'Subscription updated and payment recorded.'
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