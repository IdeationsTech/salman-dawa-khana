<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Dashboard Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [
            'total_clinics' => DB::table('clinics')->count(),

            'pending_approvals' => DB::table('clinics')
                ->where('onboarding_status', 'pending_review')
                ->count(),

            'active_licenses' => DB::table('clinic_licenses as licenses')
                ->join(
                    'clinics',
                    'clinics.clinic_id',
                    '=',
                    'licenses.clinic_id'
                )
                ->join(
                    'subscription_plans as plans',
                    'plans.subscription_plan_id',
                    '=',
                    'licenses.subscription_plan_id'
                )
                ->where('clinics.onboarding_status', 'approved')
                ->where('licenses.status', 'active')
                ->where('licenses.starts_at', '<=', $now)
                ->where(function (Builder $query) use ($now) {
                    $query
                        ->where('licenses.ends_at', '>', $now)
                        ->orWhere(function (Builder $lifetime) {
                            $lifetime
                                ->where('plans.term_type', 'lifetime')
                                ->whereNull('licenses.ends_at');
                        });
                })
                ->count(),

            'pending_payments' => DB::table('license_payments')
                ->where('status', 'pending_verification')
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Latest Clinics
        |--------------------------------------------------------------------------
        */

        $recentClinics = DB::table('clinics')
            ->leftJoin(
                'saas_customers as customers',
                'customers.saas_customer_id',
                '=',
                'clinics.saas_customer_id'
            )
            ->leftJoin(
                'clinic_licenses as licenses',
                'licenses.clinic_id',
                '=',
                'clinics.clinic_id'
            )
            ->leftJoin(
                'subscription_plans as plans',
                'plans.subscription_plan_id',
                '=',
                'licenses.subscription_plan_id'
            )
            ->select([
                'clinics.clinic_id',
                'clinics.name',
                'clinics.onboarding_status',
                'clinics.created_at',

                'customers.contact_name',
                'customers.company_name',
                'customers.email as customer_email',

                'licenses.clinic_license_id',
                'licenses.status as license_status',
                'licenses.starts_at',
                'licenses.ends_at',

                'plans.name as plan_name',
                'plans.term_type',
            ])
            ->orderByDesc('clinics.created_at')
            ->orderByDesc('clinics.clinic_id')
            ->limit(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Latest SaaS Payments
        |--------------------------------------------------------------------------
        */

        $recentPayments = DB::table('license_payments as payments')
            ->leftJoin(
                'clinic_licenses as licenses',
                'licenses.clinic_license_id',
                '=',
                'payments.clinic_license_id'
            )
            ->leftJoin(
                'clinics',
                'clinics.clinic_id',
                '=',
                'licenses.clinic_id'
            )
            ->select([
                'payments.license_payment_id',
                'payments.amount',
                'payments.currency',
                'payments.payment_method',
                'payments.reference_no',
                'payments.paid_at',
                'payments.status',

                'clinics.clinic_id',
                'clinics.name as clinic_name',
            ])
            ->orderByDesc('payments.paid_at')
            ->orderByDesc('payments.license_payment_id')
            ->limit(6)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Render Dashboard
        |--------------------------------------------------------------------------
        */

        return view('platform-admin.dashboard', [
            'stats' => $stats,
            'recentClinics' => $recentClinics,
            'recentPayments' => $recentPayments,
        ]);
    }
}