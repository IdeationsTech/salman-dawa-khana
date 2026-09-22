<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : Carbon::now()->endOfDay();

        $reportType = $request->get('report_type', 'summary');

        $totalPatients = Patient::whereBetween('created_at', [$from, $to])->count();

        $totalVisits = Visit::whereBetween('visit_date', [$from, $to])->count();

        $totalIncome = Payment::whereBetween('payment_date', [$from, $to])
            ->sum('amount');

        $totalExpenses = Expense::whereBetween('expense_date', [$from, $to])
            ->sum('amount');

        $netIncome = $totalIncome - $totalExpenses;

        $payments = Payment::whereBetween('payment_date', [$from, $to])
            ->latest('payment_date')
            ->get();

        $expenses = Expense::whereBetween('expense_date', [$from, $to])
            ->latest('expense_date')
            ->get();

        return view('reports.index', compact(
            'from',
            'to',
            'reportType',
            'totalPatients',
            'totalVisits',
            'totalIncome',
            'totalExpenses',
            'netIncome',
            'payments',
            'expenses'
        ));
    }
}