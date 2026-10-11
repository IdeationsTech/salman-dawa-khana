<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LicenseController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'status' => [
                'nullable',
                Rule::in([
                    'all',
                    'active',
                    'expired',
                    'expiring',
                    'suspended',
                ]),
            ],
            'plan' => [
                'nullable',
                Rule::in([
                    'all',
                    'trial_7d',
                    'monthly',
                    'yearly',
                    'lifetime',
                ]),
            ],
        ]);

        $filters = [
            'search' => trim($validated['search'] ?? ''),
            'status' => $validated['status'] ?? 'all',
            'plan' => $validated['plan'] ?? 'all',
        ];

        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Current Licenses Only
        |--------------------------------------------------------------------------
        */

        $query = DB::table('clinics')
            ->join(
                'clinic_licenses as licenses',
                function ($join) {
                    $join
                        ->on(
                            'licenses.clinic_license_id',
                            '=',
                            'clinics.current_license_id'
                        )
                        ->on(
                            'licenses.clinic_id',
                            '=',
                            'clinics.clinic_id'
                        );
                }
            )
            ->join(
                'subscription_plans as plans',
                'plans.subscription_plan_id',
                '=',
                'licenses.subscription_plan_id'
            )
            ->leftJoin(
                'saas_customers as customers',
                'customers.saas_customer_id',
                '=',
                'clinics.saas_customer_id'
            )
            ->select([
                'licenses.clinic_license_id',
                'licenses.status as license_status',
                'licenses.grant_type',
                'licenses.starts_at',
                'licenses.ends_at',

                'clinics.clinic_id',
                'clinics.name as clinic_name',
                'clinics.onboarding_status',

                'customers.contact_name',
                'customers.email as customer_email',

                'plans.name as plan_name',
                'plans.plan_code',
                'plans.term_type',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search and Plan Filter
        |--------------------------------------------------------------------------
        */

        if ($filters['search'] !== '') {
            $search = '%' . $filters['search'] . '%';

            $query->where(function (Builder $query) use (
                $search,
                $filters
            ) {
                $query
                    ->where('clinics.name', 'like', $search)
                    ->orWhere(
                        'customers.contact_name',
                        'like',
                        $search
                    )
                    ->orWhere(
                        'customers.email',
                        'like',
                        $search
                    );

                if (ctype_digit($filters['search'])) {
                    $query->orWhere(
                        'licenses.clinic_license_id',
                        $filters['search']
                    );
                }
            });
        }

        if ($filters['plan'] !== 'all') {
            $query->where('plans.plan_code', $filters['plan']);
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filters
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $filters['status'],
                ['active', 'expiring'],
                true
            )
        ) {
            $query
                ->where('clinics.onboarding_status', 'approved')
                ->where('licenses.status', 'active')
                ->where('licenses.starts_at', '<=', $now)
                ->where(function (Builder $query) use ($now) {
                    $query
                        ->where('licenses.ends_at', '>', $now)
                        ->orWhere(function (Builder $query) {
                            $query
                                ->where('plans.term_type', 'lifetime')
                                ->whereNull('licenses.ends_at');
                        });
                });
        }

        if ($filters['status'] === 'expiring') {
            $query
                ->whereNotNull('licenses.ends_at')
                ->where(
                    'licenses.ends_at',
                    '<=',
                    $now->copy()->addDays(7)
                );
        }

        if ($filters['status'] === 'expired') {
            $query
                ->where('clinics.onboarding_status', 'approved')
                ->where(function (Builder $query) use ($now) {
                    $query
                        ->where('licenses.status', 'expired')
                        ->orWhere(function (Builder $query) use ($now) {
                            $query
                                ->where('licenses.status', 'active')
                                ->where('licenses.starts_at', '<=', $now)
                                ->whereNotNull('licenses.ends_at')
                                ->where('licenses.ends_at', '<=', $now);
                        });
                });
        }

        if ($filters['status'] === 'suspended') {
            $query->where(
                'clinics.onboarding_status',
                'suspended'
            );
        }

        $licenses = $query
            ->orderByDesc('licenses.clinic_license_id')
            ->paginate(50)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Display Actual Access Status
        |--------------------------------------------------------------------------
        */

        $licenses->getCollection()->transform(
            function ($license) use ($now) {
                $license->display_status = $this->displayStatus(
                    $license,
                    $now
                );

                return $license;
            }
        );

        return view('platform-admin.licenses.index', [
            'licenses' => $licenses,
            'filters' => $filters,
        ]);
    }

    private function displayStatus(
        object $license,
        Carbon $now
    ): string {
        if ($license->onboarding_status === 'suspended') {
            return 'suspended';
        }

        if ($license->onboarding_status !== 'approved') {
            return 'inactive';
        }

        if ($license->license_status !== 'active') {
            return $license->license_status;
        }

        if (Carbon::parse($license->starts_at)->gt($now)) {
            return 'scheduled';
        }

        if ($license->ends_at !== null) {
            return Carbon::parse($license->ends_at)->lte($now)
                ? 'expired'
                : 'active';
        }

        return $license->term_type === 'lifetime'
            ? 'active'
            : 'inactive';
    }
}