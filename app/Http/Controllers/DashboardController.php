<?php

namespace App\Http\Controllers;

use App\Models\Bill;
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
        $today = Carbon::now('Asia/Dubai')->toDateString();

        $patientIncome = Payment::query()
            ->where('clinic_id', $clinicId)
            ->whereDate('payment_date', $today)
            ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | Only Received Order Payments Count as Collections
        |--------------------------------------------------------------------------
        */

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
            ->whereDate('payments.paid_at', $today)
            ->sum('payments.amount');

        $patientOutstanding = Bill::query()
            ->where('clinic_id', $clinicId)
            ->sum('due_amount');

        $orderOutstanding = DB::table('nuskha_orders as orders')
            ->leftJoinSub(
                DB::table('nuskha_order_payments')
                    ->where('clinic_id', $clinicId)
                    ->selectRaw(
                        'nuskha_order_id, SUM(amount) as received'
                    )
                    ->groupBy('nuskha_order_id'),
                'paid',
                'paid.nuskha_order_id',
                '=',
                'orders.nuskha_order_id'
            )
            ->where('orders.clinic_id', $clinicId)
            ->where('orders.currency', $currency)
            ->where('orders.status', '<>', 'cancelled')
            ->selectRaw(
                'COALESCE(SUM(GREATEST(
                    orders.total_amount - COALESCE(paid.received, 0),
                    0
                )), 0) as outstanding'
            )
            ->value('outstanding');

        return view('dashboard.index', [
            'totalPatients' => Patient::where('clinic_id', $clinicId)->count(),

            'todaysVisits' => Visit::where('clinic_id', $clinicId)
                ->whereDate('visit_date', $today)
                ->count(),

            'todaysIncome' => (float) $patientIncome + (float) $orderIncome,

            'outstandingBalance' =>
                (float) $patientOutstanding + (float) $orderOutstanding,

            'recentPatients' => Patient::where('clinic_id', $clinicId)
                ->orderByDesc('created_at')
                ->take(5)
                ->get(),
        ]);
    }
}