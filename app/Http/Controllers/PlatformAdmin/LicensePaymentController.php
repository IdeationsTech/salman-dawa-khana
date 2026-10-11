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
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
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
            'from' => ['nullable', 'date_format:Y-m-d'],
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

        $query = $this->paymentQuery();

        if ($filters['search'] !== '') {
            $search = '%' . $filters['search'] . '%';

            $query->where(function (Builder $query) use (
                $search,
                $filters
            ) {
                $query
                    ->where('clinics.name', 'like', $search)
                    ->orWhere('customers.contact_name', 'like', $search)
                    ->orWhere('customers.email', 'like', $search)
                    ->orWhere('payments.reference_no', 'like', $search);

                if (ctype_digit($filters['search'])) {
                    $query->orWhere(
                        'payments.license_payment_id',
                        $filters['search']
                    );
                }
            });
        }

        if ($filters['status'] !== 'all') {
            $query->where('payments.status', $filters['status']);
        }

        if ($filters['method'] !== 'all') {
            $query->where(
                'payments.payment_method',
                $filters['method']
            );
        }

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

    public function show(int $payment): View
    {
        return view('platform-admin.payments.show', [
            'payment' => $this->findPayment($payment),
        ]);
    }

    public function receipt(int $payment): View
    {
        $record = $this->findPayment($payment);

        abort_unless(
            in_array(
                $record->status,
                ['verified', 'complimentary'],
                true
            ),
            403,
            'A receipt is available after payment verification.'
        );

        return view('platform-admin.payments.receipt', [
            'payment' => $record,
        ]);
    }

    private function findPayment(int $payment): object
    {
        $record = $this->paymentQuery()
            ->where('payments.license_payment_id', $payment)
            ->first();

        abort_if(! $record, 404);

        return $record;
    }

    private function paymentQuery(): Builder
    {
        return DB::table('license_payments as payments')
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
            ->leftJoin(
                'platform_admins as recorder',
                'recorder.platform_admin_id',
                '=',
                'payments.recorded_by_platform_admin_id'
            )
            ->leftJoin(
                'platform_admins as verifier',
                'verifier.platform_admin_id',
                '=',
                'payments.verified_by_platform_admin_id'
            )
            ->select([
                'payments.*',

                'licenses.starts_at as license_starts_at',
                'licenses.ends_at as license_ends_at',
                'licenses.grant_type',

                'clinics.clinic_id',
                'clinics.name as clinic_name',
                'clinics.phone as clinic_phone',
                'clinics.address as clinic_address',

                'customers.contact_name',
                'customers.company_name',
                'customers.email as customer_email',

                'plans.name as plan_name',
                'plans.term_type',

                'recorder.name as recorded_by_name',
                'verifier.name as verified_by_name',
            ]);
    }
}