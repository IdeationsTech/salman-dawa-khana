<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClinicController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'onboarding_status' => [
                'nullable',
                Rule::in([
                    'all',
                    'pending_review',
                    'approved',
                    'rejected',
                ]),
            ],
        ]);

        $filters = [
            'search' => trim($validated['search'] ?? ''),
            'onboarding_status' =>
                $validated['onboarding_status'] ?? 'all',
        ];

        $query = $this->clinicQuery();

        if ($filters['search'] !== '') {
            $search = '%' . $filters['search'] . '%';

            $query->where(function (Builder $query) use ($search) {
                $query
                    ->where('clinics.name', 'like', $search)
                    ->orWhere('clinics.phone', 'like', $search)
                    ->orWhere('customers.contact_name', 'like', $search)
                    ->orWhere('customers.company_name', 'like', $search)
                    ->orWhere('customers.email', 'like', $search);
            });
        }

        if ($filters['onboarding_status'] !== 'all') {
            $query->where(
                'clinics.onboarding_status',
                $filters['onboarding_status']
            );
        }

        $clinics = $query
            ->orderByDesc('clinics.created_at')
            ->orderByDesc('clinics.clinic_id')
            ->paginate(50)
            ->withQueryString();

        $stats = [
            'total' => DB::table('clinics')->count(),

            'pending' => DB::table('clinics')
                ->where('onboarding_status', 'pending_review')
                ->count(),

            'approved' => DB::table('clinics')
                ->where('onboarding_status', 'approved')
                ->count(),

            'rejected' => DB::table('clinics')
                ->where('onboarding_status', 'rejected')
                ->count(),
        ];

        return view('platform-admin.clinics.index', [
            'clinics' => $clinics,
            'stats' => $stats,
            'filters' => $filters,
        ]);
    }

    public function show(int $clinic): View
    {
        $clinicRecord = $this->clinicQuery()
            ->where('clinics.clinic_id', $clinic)
            ->first();

        abort_if(! $clinicRecord, 404);

        $license = DB::table('clinic_licenses as licenses')
            ->join(
                'subscription_plans as plans',
                'plans.subscription_plan_id',
                '=',
                'licenses.subscription_plan_id'
            )
            ->select([
                'licenses.*',
                'plans.name as plan_name',
                'plans.term_type',
            ])
            ->where('licenses.clinic_id', $clinic)
            ->first();

        $plans = DB::table('subscription_plans')
            ->where('is_active', true)
            ->whereIn('plan_code', [
                'trial_7d',
                'monthly',
                'yearly',
                'lifetime',
            ])
            ->orderBy('sort_order')
            ->get();

        $auditLogs = DB::table('platform_audit_logs as logs')
            ->leftJoin(
                'platform_admins as admins',
                'admins.platform_admin_id',
                '=',
                'logs.platform_admin_id'
            )
            ->select([
                'logs.platform_audit_log_id',
                'logs.action',
                'logs.created_at',
                'admins.name as admin_name',
            ])
            ->where('logs.clinic_id', $clinic)
            ->orderByDesc('logs.created_at')
            ->orderByDesc('logs.platform_audit_log_id')
            ->limit(10)
            ->get();

        return view('platform-admin.clinics.show', [
            'clinic' => $clinicRecord,
            'license' => $license,
            'plans' => $plans,
            'auditLogs' => $auditLogs,
        ]);
    }

    public function approve(
        Request $request,
        int $clinic
    ): RedirectResponse {
        $isTrial = $request->input('plan_code') === 'trial_7d';

        $validated = $request->validate([
            'plan_code' => [
                'required',
                Rule::in([
                    'trial_7d',
                    'monthly',
                    'yearly',
                    'lifetime',
                ]),
            ],

            'payment_method' => [
                $isTrial ? 'prohibited' : 'required',
                Rule::in(['cash', 'bank_transfer']),
            ],

            'amount' => [
                $isTrial ? 'prohibited' : 'required',
                'numeric',
                'gt:0',
                'max:9999999999.99',
            ],

            'paid_at' => [
                $isTrial ? 'prohibited' : 'required',
                'date',
                'before_or_equal:today',
            ],

            'reference_no' => [
                'nullable',
                'string',
                'max:100',
            ],

            'payment_notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        DB::transaction(function () use (
            $request,
            $clinic,
            $validated,
            $isTrial
        ) {
            $clinicRecord = DB::table('clinics')
                ->where('clinic_id', $clinic)
                ->lockForUpdate()
                ->first();

            abort_if(! $clinicRecord, 404);

            if ($clinicRecord->onboarding_status !== 'pending_review') {
                throw ValidationException::withMessages([
                    'approval' =>
                        'Only a clinic awaiting review can be approved.',
                ]);
            }

            if (
                DB::table('clinic_licenses')
                    ->where('clinic_id', $clinic)
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'approval' =>
                        'This clinic already has a license record.',
                ]);
            }

            $expectedTerms = [
                'trial_7d' => 'trial',
                'monthly' => 'monthly',
                'yearly' => 'yearly',
                'lifetime' => 'lifetime',
            ];

            $plan = DB::table('subscription_plans')
                ->where('plan_code', $validated['plan_code'])
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (
                ! $plan ||
                $plan->term_type !==
                    $expectedTerms[$validated['plan_code']]
            ) {
                throw ValidationException::withMessages([
                    'plan_code' =>
                        'Select an active subscription plan.',
                ]);
            }

            if (
                $isTrial &&
                (int) $plan->duration_days !== 7
            ) {
                throw ValidationException::withMessages([
                    'plan_code' =>
                        'The seven-day trial plan is not configured correctly.',
                ]);
            }

            if (
                ! $isTrial &&
                (
                    (float) $plan->price <= 0 ||
                    round((float) $validated['amount'], 2) <
                        round((float) $plan->price, 2)
                )
            ) {
                throw ValidationException::withMessages([
                    'amount' =>
                        'Set a plan price and verify full payment before issuing this license.',
                ]);
            }

            $adminId = Auth::guard('platform_admin')->id();
            $now = now();

            $endsAt = match ($plan->term_type) {
                'trial' => $now->copy()->addDays(7),

                'monthly' => $now->copy()->addMonthNoOverflow(),

                'yearly' => $now->copy()->addYearNoOverflow(),

                'lifetime' => null,
            };

            $oldClinicValues = [
                'onboarding_status' =>
                    $clinicRecord->onboarding_status,
                'reviewed_by_platform_admin_id' =>
                    $clinicRecord->reviewed_by_platform_admin_id,
                'reviewed_at' => $clinicRecord->reviewed_at,
                'rejection_reason' =>
                    $clinicRecord->rejection_reason,
            ];

            DB::table('clinics')
                ->where('clinic_id', $clinic)
                ->update([
                    'onboarding_status' => 'approved',
                    'reviewed_by_platform_admin_id' => $adminId,
                    'reviewed_at' => $now,
                    'rejection_reason' => null,
                    'updated_at' => $now,
                ]);

            $licenseId = DB::table('clinic_licenses')
                ->insertGetId([
                    'clinic_id' => $clinic,
                    'subscription_plan_id' =>
                        $plan->subscription_plan_id,
                    'issued_by_platform_admin_id' => $adminId,
                    'status' => 'active',
                    'grant_type' =>
                        $isTrial ? 'trial' : 'paid',
                    'starts_at' => $now,
                    'ends_at' => $endsAt,
                    'grant_reason' => $isTrial
                        ? 'Seven-day trial approved by platform admin.'
                        : 'Manual payment verified by platform admin.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'clinic_license_id');

            $this->recordAudit(
                $request,
                $clinic,
                'clinic.approved',
                'clinics',
                $clinic,
                $oldClinicValues,
                [
                    'onboarding_status' => 'approved',
                    'reviewed_by_platform_admin_id' => $adminId,
                    'reviewed_at' => $now->toDateTimeString(),
                ]
            );

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
                        'approval' => 'This clinic has no owner account.',
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
                    ['is_active' => (bool) $owner->is_active],
                    ['is_active' => true]
                );

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
                    'grant_type' =>
                        $isTrial ? 'trial' : 'paid',
                    'starts_at' => $now->toDateTimeString(),
                    'ends_at' =>
                        $endsAt?->toDateTimeString(),
                ]
            );

            if (! $isTrial) {
                $paidAt = Carbon::parse(
                    $validated['paid_at']
                )->startOfDay();

                $paymentId = DB::table('license_payments')
                    ->insertGetId([
                        'clinic_license_id' => $licenseId,
                        'recorded_by_platform_admin_id' =>
                            $adminId,
                        'verified_by_platform_admin_id' =>
                            $adminId,
                        'amount' => $validated['amount'],
                        'currency' => $plan->currency,
                        'payment_method' =>
                            $validated['payment_method'],
                        'reference_no' =>
                            $validated['reference_no'] ?? null,
                        'paid_at' => $paidAt,
                        'status' => 'verified',
                        'receipt_path' => null,
                        'verified_at' => $now,
                        'notes' =>
                            $validated['payment_notes'] ?? null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ], 'license_payment_id');

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
                        'reference_no' =>
                            $validated['reference_no'] ?? null,
                        'paid_at' =>
                            $paidAt->toDateTimeString(),
                        'status' => 'verified',
                    ]
                );
            }
        }, 3);

        return redirect()
            ->route('platform-admin.clinics.show', $clinic)
            ->with(
                'success',
                $isTrial
                    ? 'Clinic approved with a seven-day trial.'
                    : 'Clinic approved and manual payment recorded.'
            );
    }

    public function reject(
        Request $request,
        int $clinic
    ): RedirectResponse {
        $request->merge([
            'rejection_reason' => trim(
                (string) $request->input('rejection_reason')
            ),
        ]);

        $validated = $request->validate([
            'rejection_reason' => [
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
                $clinicRecord->onboarding_status !==
                'pending_review'
            ) {
                throw ValidationException::withMessages([
                    'approval' =>
                        'Only a clinic awaiting review can be rejected.',
                ]);
            }

            $adminId = Auth::guard('platform_admin')->id();
            $now = now();

            $oldValues = [
                'onboarding_status' =>
                    $clinicRecord->onboarding_status,
                'reviewed_by_platform_admin_id' =>
                    $clinicRecord->reviewed_by_platform_admin_id,
                'reviewed_at' => $clinicRecord->reviewed_at,
                'rejection_reason' =>
                    $clinicRecord->rejection_reason,
            ];

            DB::table('clinics')
                ->where('clinic_id', $clinic)
                ->update([
                    'onboarding_status' => 'rejected',
                    'reviewed_by_platform_admin_id' =>
                        $adminId,
                    'reviewed_at' => $now,
                    'rejection_reason' =>
                        $validated['rejection_reason'],
                    'updated_at' => $now,
                ]);

            $this->recordAudit(
                $request,
                $clinic,
                'clinic.rejected',
                'clinics',
                $clinic,
                $oldValues,
                [
                    'onboarding_status' => 'rejected',
                    'reviewed_by_platform_admin_id' =>
                        $adminId,
                    'reviewed_at' => $now->toDateTimeString(),
                    'rejection_reason' =>
                        $validated['rejection_reason'],
                ]
            );
        }, 3);

        return redirect()
            ->route('platform-admin.clinics.show', $clinic)
            ->with('success', 'Clinic registration rejected.');
    }

    private function clinicQuery(): Builder
    {
        return DB::table('clinics')
            ->leftJoin(
                'saas_customers as customers',
                'customers.saas_customer_id',
                '=',
                'clinics.saas_customer_id'
            )
            ->select([
                'clinics.*',
                'customers.contact_name',
                'customers.company_name',
                'customers.email as customer_email',
                'customers.phone as customer_phone',
                'customers.country_code',
                'customers.status as customer_status',
            ]);
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