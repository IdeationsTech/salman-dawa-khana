<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Expense;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Visit;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $totalPatients = Patient::count();

        $todaysVisits = Visit::whereDate(
            'visit_date',
            $today
        )->count();

        $todaysIncome = Payment::whereDate(
            'payment_date',
            $today
        )->sum('amount');

        $outstandingBalance = Bill::sum('due_amount');

        $recentPatients = Patient::latest()
            ->take(5)
            ->get();

        $dashboardData = [
            'totalPatients' => $totalPatients,
            'todaysVisits' => $todaysVisits,
            'todaysIncome' => $todaysIncome,
            'outstandingBalance' => $outstandingBalance,
            'recentPatients' => $recentPatients,
        ];

        return view('dashboard.index', $dashboardData);
    }
}