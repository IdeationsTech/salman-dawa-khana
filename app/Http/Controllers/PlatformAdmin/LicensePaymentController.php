<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LicensePaymentController extends Controller
{
    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Payment Filters
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:160',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'all',
                    'verified',
                    'pending_verification',
                    'complimentary',
                ]),
            ],

            'method' => [
                'nullable',
                Rule::in([
                    'all',
                    'cash',
                    'bank_transfer',
                    'complimentary',
                ]),
            ],

            'from' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'to' => [
                'nullable',
                'date_format:Y-m-d',
                Rule::when(
                    $request->filled('from'),
                    ['after_or_equal:from']
                ),
            ],
        ]);

        $filters = [
            'search' => trim($validated['search'] ?? ''),
            'status' => $validated['status'] ?? 'all',
            'method' => $validated['method'] ?? 'all',
            'from' => $validated['from'] ?? '',
            'to' => $validated['to'] ?? '',
        ];

        /*
        |--------------------------------------------------------------------------
        | Payment History Query
        |--------------------------------------------------------------------------
        */

        $query = DB::table('license_payments as payments')
            ->join(
                'clinic_licenses as licenses',
                'licenses.clinic_license_id',
                '=',
                'payments.clinic_license_id'
            )
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
            ->leftJoin(
                'saas_customers as customers',
                'customers.saas_customer_id',
                '=',
                'clinics.saas_customer_id'
            )
            ->select([
                'payments.license_payment_id',
                'payments.clinic_license_id',
                'payments.amount',
                'payments.currency',
                'payments.payment_method',
                'payments.reference_no',
                'payments.paid_at',
                'payments.status',
                'payments.verified_at',

                'clinics.clinic_id',
                'clinics.name as clinic_name',

                'customers.contact_name',
                'customers.email as customer_email',

                'plans.name as plan_name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
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
                    )
                    ->orWhere(
                        'payments.reference_no',
                        'like',
                        $search
                    );

                if (ctype_digit($filters['search'])) {
                    $query->orWhere(
                        'payments.license_payment_id',
                        $filters['search']
                    );
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status and Method Filters
        |--------------------------------------------------------------------------
        */

        if ($filters['status'] !== 'all') {
            $query->where(
                'payments.status',
                $filters['status']
            );
        }

        if ($filters['method'] !== 'all') {
            $query->where(
                'payments.payment_method',
                $filters['method']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Payment Date Filters
        |--------------------------------------------------------------------------
        */

        if ($filters['from'] !== '') {
            $query->whereDate(
                'payments.paid_at',
                '>=',
                $filters['from']
            );
        }

        if ($filters['to'] !== '') {
            $query->whereDate(
                'payments.paid_at',
                '<=',
                $filters['to']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination and View
        |--------------------------------------------------------------------------
        */

        $payments = $query
            ->orderByDesc('payments.paid_at')
            ->orderByDesc('payments.license_payment_id')
            ->paginate(50)
            ->withQueryString();

        return view('platform-admin.payments.index', [
            'payments' => $payments,
            'filters' => $filters,
        ]);
    }
}