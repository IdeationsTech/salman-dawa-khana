<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'report_type' => ['nullable', 'in:summary,payments,expenses'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $clinic = $request->user()->clinic;
        $clinicId = (int) $clinic->clinic_id;
        $currency = strtoupper($clinic->currency ?: 'AED');

        $from = filled($data['from'] ?? null)
            ? Carbon::parse($data['from'], 'Asia/Dubai')->startOfDay()
            : Carbon::now('Asia/Dubai')->startOfMonth();

        $to = filled($data['to'] ?? null)
            ? Carbon::parse($data['to'], 'Asia/Dubai')->endOfDay()
            : Carbon::now('Asia/Dubai')->endOfDay();

        abort_if($from->gt($to), 422, 'From date must not exceed To date.');

        $range = [
            $from->format('Y-m-d H:i:s'),
            $to->format('Y-m-d H:i:s'),
        ];

        $patientPayments = Payment::query()
            ->where('clinic_id', $clinicId)
            ->whereBetween('payment_date', $range)
            ->with([
                'patient' => fn ($query) =>
                    $query->where('clinic_id', $clinicId),
            ])
            ->get();

        $orderPayments = DB::table('nuskha_order_payments as payments')
            ->join(
                'nuskha_orders as orders',
                'orders.nuskha_order_id',
                '=',
                'payments.nuskha_order_id'
            )
            ->where('payments.clinic_id', $clinicId)
            ->where('orders.clinic_id', $clinicId)
            ->whereBetween('payments.paid_at', $range)
            ->select([
                'payments.nuskha_order_payment_id',
                'payments.nuskha_order_id',
                'payments.paid_at',
                'payments.amount',
                'payments.method',
                'orders.customer_name',
                'orders.order_no',
                'orders.currency',
            ])
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Unified Collections — Never Add Order Sales Total Again
        |--------------------------------------------------------------------------
        */

        $collections = $patientPayments->map(fn ($payment) => [
            'source' => 'Patient payment',
            'reference' => 'PAY-' . $payment->payment_id,
            'name' => $payment->patient?->full_name ?? 'Patient',
            'date' => $payment->payment_date->format('Y-m-d H:i:s'),
            'amount' => $payment->amount,
            'currency' => $currency,
            'method' => $payment->method,
            'url' => route('payments.show', $payment),
        ])->concat(
            $orderPayments->map(fn ($payment) => [
                'source' => 'Nuskha order payment',
                'reference' => $payment->order_no,
                'name' => $payment->customer_name,
                'date' => $payment->paid_at,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'method' => $payment->method,
                'url' => route('orders.show', $payment->nuskha_order_id),
            ])
        )->sortByDesc('date')->values();

        $currencyTotals = $collections
            ->groupBy('currency')
            ->map(fn ($rows) => $rows->sum('amount'));

        $patientIncome = $patientPayments->sum('amount');

        $orderIncome = $orderPayments
            ->where('currency', $currency)
            ->sum('amount');

        $totalIncome = (float) $patientIncome + (float) $orderIncome;

        $expenses = Expense::query()
            ->where('clinic_id', $clinicId)
            ->whereBetween('expense_date', $range)
            ->orderByDesc('expense_date')
            ->get();

        $totalExpenses = $expenses->sum('amount');

        $decimalSetting = Setting::query()
            ->where('clinic_id', $clinicId)
            ->where('setting_key', 'decimal_places')
            ->value('setting_value');

        return view('reports.index', [
            'clinic' => $clinic,
            'currency' => $currency,
            'decimalPlaces' => (string) $decimalSetting === '0' ? 0 : 2,
            'from' => $from,
            'to' => $to,
            'reportType' => $data['report_type'] ?? 'summary',
            'patientIncome' => $patientIncome,
            'orderIncome' => $orderIncome,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'netIncome' => $totalIncome - (float) $totalExpenses,
            'collections' => $collections,
            'currencyTotals' => $currencyTotals,
            'expenses' => $expenses,

            'totalPatients' => Patient::where('clinic_id', $clinicId)
                ->whereBetween('created_at', $range)
                ->count(),

            'totalVisits' => Visit::where('clinic_id', $clinicId)
                ->whereBetween('visit_date', $range)
                ->count(),
        ]);
    }
}