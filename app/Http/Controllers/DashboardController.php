<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Payment;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $clinicId = (int) Auth::user()->clinic_id;
        $currency = strtoupper(Auth::user()->clinic->currency ?: 'AED');

        $today = Carbon::now('Asia/Dubai')->startOfDay();
        $dayFrom = $today->format('Y-m-d H:i:s');
        $dayUntil = $today->copy()->addDay()->format('Y-m-d H:i:s');

        /*
        |--------------------------------------------------------------------------
        | Today's Received Payments — Same Currency
        |--------------------------------------------------------------------------
        */

        $patientIncome = Payment::query()
            ->where('clinic_id', $clinicId)
            ->where('currency', $currency)
            ->where('payment_date', '>=', $dayFrom)
            ->where('payment_date', '<', $dayUntil)
            ->sum('amount');

        $orderIncome = DB::table('nuskha_order_payments as payments')
            ->join(
                'nuskha_orders as orders',
                'orders.nuskha_order_id',
                '=',
                'payments.nuskha_order_id'
            )
            ->where('payments.clinic_id', $clinicId)
            ->where('orders.clinic_id', $clinicId)
            ->where('orders.currency', $currency)
            ->where('payments.paid_at', '>=', $dayFrom)
            ->where('payments.paid_at', '<', $dayUntil)
            ->sum('payments.amount');

        /*
        |--------------------------------------------------------------------------
        | Patient Outstanding — Calculate from Actual Payments
        |--------------------------------------------------------------------------
        */

        $paidByBill = DB::table('payments')
            ->where('clinic_id', $clinicId)
            ->where('currency', $currency)
            ->whereNotNull('bill_id')
            ->select('bill_id', 'patient_id')
            ->selectRaw('SUM(amount) AS received_amount')
            ->groupBy('bill_id', 'patient_id');

        $patientBalanceRow = DB::table('bills as bills')
            ->leftJoinSub($paidByBill, 'paid', function ($join) {
                $join
                    ->on('paid.bill_id', '=', 'bills.bill_id')
                    ->on('paid.patient_id', '=', 'bills.patient_id');
            })
            ->where('bills.clinic_id', $clinicId)
            ->where('bills.currency', $currency)
            ->where(function ($query) {
                $query
                    ->whereNull('bills.status')
                    ->orWhereNotIn(
                        'bills.status',
                        ['cancelled', 'void']
                    );
            })
            ->selectRaw(
                'COALESCE(
                    SUM(
                        GREATEST(
                            bills.total_amount
                            - COALESCE(paid.received_amount, 0),
                            0
                        )
                    ),
                    0
                ) AS outstanding'
            )
            ->first();

        $patientOutstanding =
            $patientBalanceRow->outstanding ?? '0.00';

        /*
        |--------------------------------------------------------------------------
        | Order Outstanding — Same Currency, Excluding Cancelled Orders
        |--------------------------------------------------------------------------
        */

        $paidByOrder = DB::table('nuskha_order_payments')
            ->where('clinic_id', $clinicId)
            ->select('nuskha_order_id')
            ->selectRaw('SUM(amount) AS received_amount')
            ->groupBy('nuskha_order_id');

        $orderBalanceRow = DB::table('nuskha_orders as orders')
            ->leftJoinSub(
                $paidByOrder,
                'paid',
                'paid.nuskha_order_id',
                '=',
                'orders.nuskha_order_id'
            )
            ->where('orders.clinic_id', $clinicId)
            ->where('orders.currency', $currency)
            ->where('orders.status', '<>', 'cancelled')
            ->selectRaw(
                'COALESCE(
                    SUM(
                        GREATEST(
                            orders.total_amount
                            - COALESCE(paid.received_amount, 0),
                            0
                        )
                    ),
                    0
                ) AS outstanding'
            )
            ->first();

        $orderOutstanding =
            $orderBalanceRow->outstanding ?? '0.00';

        $incomeCents =
            $this->toCents($patientIncome)
            + $this->toCents($orderIncome);

        $outstandingCents =
            $this->toCents($patientOutstanding)
            + $this->toCents($orderOutstanding);

        return view('dashboard.index', [
            'totalPatients' => Patient::query()
                ->where('clinic_id', $clinicId)
                ->count(),

            'todaysVisits' => Visit::query()
                ->where('clinic_id', $clinicId)
                ->where('visit_date', '>=', $dayFrom)
                ->where('visit_date', '<', $dayUntil)
                ->count(),

            'todaysIncome' => $this->decimal($incomeCents),

            'outstandingBalance' => $this->decimal($outstandingCents),

            'recentPatients' => Patient::query()
                ->where('clinic_id', $clinicId)
                ->orderByDesc('created_at')
                ->orderByDesc('patient_id')
                ->limit(5)
                ->get(),
        ]);
    }

    private function toCents($amount): int
    {
        [$whole, $fraction] = array_pad(
            explode('.', (string) $amount, 2),
            2,
            ''
        );

        return ((int) $whole * 100)
            + (int) str_pad(
                substr($fraction, 0, 2),
                2,
                '0',
                STR_PAD_RIGHT
            );
    }

    private function decimal(int $cents): string
    {
        return intdiv($cents, 100)
            . '.'
            . str_pad(
                (string) ($cents % 100),
                2,
                '0',
                STR_PAD_LEFT
            );
    }
}