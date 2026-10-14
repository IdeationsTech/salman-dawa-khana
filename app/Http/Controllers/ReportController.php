<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Visit;
use App\Support\ClinicLocaleCatalog;
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

        $range = [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')];

        $patientPayments = Payment::where('clinic_id', $clinicId)
            ->whereBetween('payment_date', $range)
            ->with(['patient' => fn ($query) => $query->where('clinic_id', $clinicId)])
            ->get();

        $orderPayments = DB::table('nuskha_order_payments as payments')
            ->join('nuskha_orders as orders', 'orders.nuskha_order_id', '=', 'payments.nuskha_order_id')
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
            ])->get();

        $collections = $patientPayments->map(fn ($payment) => [
            'source' => 'Patient payment',
            'reference' => 'PAY-' . $payment->payment_id,
            'name' => $payment->patient?->full_name ?? 'Patient unavailable',
            'date' => $payment->payment_date->format('Y-m-d H:i:s'),
            'amount' => $payment->amount,
            'currency' => $this->currencyCode($payment->currency),
            'method' => $payment->method,
            'url' => route('payments.show', $payment),
        ])->concat($orderPayments->map(fn ($payment) => [
            'source' => 'Nuskha order payment',
            'reference' => $payment->order_no,
            'name' => $payment->customer_name,
            'date' => $payment->paid_at,
            'amount' => $payment->amount,
            'currency' => $this->currencyCode($payment->currency),
            'method' => $payment->method,
            'url' => route('orders.show', $payment->nuskha_order_id),
        ]))->sortByDesc('date')->values();

        $expenses = Expense::where('clinic_id', $clinicId)
            ->whereBetween('expense_date', $range)
            ->orderByDesc('expense_date')->orderByDesc('expense_id')->get();

        $totals = [$currency => ['received' => 0, 'expenses' => 0]];
        $patientIncomeCents = 0;
        $orderIncomeCents = 0;
        $unconfirmedCurrencyCount = 0;

        foreach ($collections as $record) {
            $code = $record['currency'];

            if ($code === null) {
                $unconfirmedCurrencyCount++;
                continue;
            }

            $totals[$code] ??= ['received' => 0, 'expenses' => 0];
            $amount = $this->toCents($record['amount']);
            $totals[$code]['received'] += $amount;

            if ($code === $currency) {
                if ($record['source'] === 'Patient payment') {
                    $patientIncomeCents += $amount;
                } else {
                    $orderIncomeCents += $amount;
                }
            }
        }

        foreach ($expenses as $expense) {
            $code = $this->currencyCode($expense->currency);

            if ($code === null) {
                $unconfirmedCurrencyCount++;
                continue;
            }

            $totals[$code] ??= ['received' => 0, 'expenses' => 0];
            $totals[$code]['expenses'] += $this->toCents($expense->amount);
        }

        ksort($totals);
        $currencySummary = [];

        foreach ($totals as $code => $amounts) {
            $currencySummary[] = [
                'currency' => $code,
                'received' => $this->decimal($amounts['received']),
                'expenses' => $this->decimal($amounts['expenses']),
                'net' => $this->decimal($amounts['received'] - $amounts['expenses']),
            ];
        }

        $currentTotals = $totals[$currency];

        $decimalSetting = Setting::where('clinic_id', $clinicId)
            ->where('setting_key', 'decimal_places')->value('setting_value');

        return view('reports.index', [
            'clinic' => $clinic,
            'currency' => $currency,
            'decimalPlaces' => (string) $decimalSetting === '0' ? 0 : 2,
            'from' => $from,
            'to' => $to,
            'reportType' => $data['report_type'] ?? 'summary',
            'patientIncome' => $this->decimal($patientIncomeCents),
            'orderIncome' => $this->decimal($orderIncomeCents),
            'totalIncome' => $this->decimal($currentTotals['received']),
            'totalExpenses' => $this->decimal($currentTotals['expenses']),
            'netIncome' => $this->decimal(
                $currentTotals['received'] - $currentTotals['expenses']
            ),
            'collections' => $collections,
            'expenses' => $expenses,
            'currencySummary' => $currencySummary,
            'unconfirmedCurrencyCount' => $unconfirmedCurrencyCount,
            'totalPatients' => Patient::where('clinic_id', $clinicId)
                ->whereBetween('created_at', $range)->count(),
            'totalVisits' => Visit::where('clinic_id', $clinicId)
                ->whereBetween('visit_date', $range)->count(),
        ]);
    }

    private function currencyCode($value): ?string
    {
        $code = strtoupper(trim((string) $value));

        return ClinicLocaleCatalog::knownCurrency($code) ? $code : null;
    }

    private function toCents($amount): int
    {
        return \App\Support\Money::scaled($amount, 2);
    }

    private function decimal(int $cents): string
    {
        $prefix = $cents < 0 ? '-' : '';
        $absolute = abs($cents);

        return $prefix . intdiv($absolute, 100) . '.'
            . str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);
    }
}