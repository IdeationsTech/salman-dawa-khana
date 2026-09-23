<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $clinicId = Auth::user()->clinic_id;
        $today = Carbon::today();

        $totalPatients = Patient::where(
            'clinic_id',
            $clinicId
        )->count();

        $todaysVisits = Visit::where(
            'clinic_id',
            $clinicId
        )
            ->whereDate('visit_date', $today)
            ->count();

        $todaysIncome = Payment::where(
            'clinic_id',
            $clinicId
        )
            ->whereDate('payment_date', $today)
            ->sum('amount');

        $outstandingBalance = Bill::where(
            'clinic_id',
            $clinicId
        )->sum('due_amount');

        $recentPatients = Patient::where(
            'clinic_id',
            $clinicId
        )
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('dashboard.index', [
            'totalPatients' => $totalPatients,
            'todaysVisits' => $todaysVisits,
            'todaysIncome' => $todaysIncome,
            'outstandingBalance' => $outstandingBalance,
            'recentPatients' => $recentPatients,
        ]);
    }
}