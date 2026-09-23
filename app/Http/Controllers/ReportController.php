<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'report_type' => [
                'nullable',
                'in:summary,payments,expenses',
            ],

            'from' => [
                'nullable',
                'date',
            ],

            'to' => [
                'nullable',
                'date',
                'after_or_equal:from',
            ],
        ]);

        $clinicId = Auth::user()->clinic_id;

        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : Carbon::now()->endOfDay();

        $reportType = $request->get(
            'report_type',
            'summary'
        );

        $totalPatients = Patient::where(
            'clinic_id',
            $clinicId
        )
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $totalVisits = Visit::where(
            'clinic_id',
            $clinicId
        )
            ->whereBetween('visit_date', [$from, $to])
            ->count();

        $totalIncome = Payment::where(
            'clinic_id',
            $clinicId
        )
            ->whereBetween('payment_date', [$from, $to])
            ->sum('amount');

        $totalExpenses = Expense::where(
            'clinic_id',
            $clinicId
        )
            ->whereBetween('expense_date', [$from, $to])
            ->sum('amount');

        $netIncome = $totalIncome - $totalExpenses;

        $payments = Payment::where(
            'clinic_id',
            $clinicId
        )
            ->whereBetween('payment_date', [$from, $to])
            ->latest('payment_date')
            ->get();

        $expenses = Expense::where(
            'clinic_id',
            $clinicId
        )
            ->whereBetween('expense_date', [$from, $to])
            ->latest('expense_date')
            ->get();

        $expenseBreakdown = Expense::where(
            'clinic_id',
            $clinicId
        )
            ->whereBetween('expense_date', [$from, $to])
            ->selectRaw(
                'category, SUM(amount) as total_amount'
            )
            ->groupBy('category')
            ->orderByDesc('total_amount')
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
            'expenses',
            'expenseBreakdown'
        ));
    }
}