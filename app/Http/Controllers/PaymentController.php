<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = Auth::user()->clinic_id;

        $query = Payment::where(
            'clinic_id',
            $clinicId
        );

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where(
                    'payment_id',
                    'like',
                    "%{$search}%"
                )
                    ->orWhere(
                        'reference_no',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'method',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'patient_id',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        if (
            $request->filled('method') &&
            $request->method !== 'all'
        ) {
            $query->where('method', $request->method);
        }

        $dateRange = strtolower(
            $request->get('date_range', 'all time')
        );

        if ($dateRange === 'today') {
            $query->whereDate(
                'payment_date',
                Carbon::today()
            );
        }

        if ($dateRange === 'this week') {
            $query->whereBetween('payment_date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ]);
        }

        if ($dateRange === 'this month') {
            $query->whereBetween('payment_date', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ]);
        }

        $payments = $query
            ->latest('payment_date')
            ->paginate(10)
            ->withQueryString();

        $paymentMethods = Payment::where(
            'clinic_id',
            $clinicId
        )
            ->whereNotNull('method')
            ->distinct()
            ->orderBy('method')
            ->pluck('method');

        $totalReceived = Payment::where(
            'clinic_id',
            $clinicId
        )->sum('amount');

        $todayReceived = Payment::where(
            'clinic_id',
            $clinicId
        )
            ->whereDate(
                'payment_date',
                Carbon::today()
            )
            ->sum('amount');

        $monthReceived = Payment::where(
            'clinic_id',
            $clinicId
        )
            ->whereBetween('payment_date', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->sum('amount');

        return view('payments.index', compact(
            'payments',
            'paymentMethods',
            'totalReceived',
            'todayReceived',
            'monthReceived'
        ));
    }

    public function create()
    {
        return view('payments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => [
                'required',
                'integer',
                'exists:patients,patient_id',
            ],

            'bill_id' => [
                'nullable',
                'integer',
                'exists:bills,bill_id',
            ],

            'payment_date' => [
                'required',
                'date',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'method' => [
                'required',
                'string',
                'max:20',
            ],

            'reference_no' => [
                'nullable',
                'string',
                'max:80',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $validated['clinic_id'] = Auth::user()->clinic_id;
        $validated['received_by_user_id'] = Auth::id();

        Payment::create($validated);

        return redirect()
            ->route('payments.index')
            ->with(
                'success',
                'Payment recorded successfully.'
            );
    }

    public function show(Payment $payment)
    {
        $this->ensureClinicAccess($payment);

        $payment->load([
            'patient',
            'bill',
            'receivedBy',
        ]);

        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $this->ensureClinicAccess($payment);

        return view('payments.create', compact('payment'));
    }

    public function update(
        Request $request,
        Payment $payment
    ) {
        $this->ensureClinicAccess($payment);

        $validated = $request->validate([
            'patient_id' => [
                'required',
                'integer',
                'exists:patients,patient_id',
            ],

            'bill_id' => [
                'nullable',
                'integer',
                'exists:bills,bill_id',
            ],

            'payment_date' => [
                'required',
                'date',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'method' => [
                'required',
                'string',
                'max:20',
            ],

            'reference_no' => [
                'nullable',
                'string',
                'max:80',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $payment->update($validated);

        return redirect()
            ->route('payments.show', $payment)
            ->with(
                'success',
                'Payment updated successfully.'
            );
    }

    public function destroy(Payment $payment)
    {
        $this->ensureClinicAccess($payment);

        $payment->delete();

        return redirect()
            ->route('payments.index')
            ->with(
                'success',
                'Payment deleted successfully.'
            );
    }

    private function ensureClinicAccess(Payment $payment)
    {
        abort_if(
            $payment->clinic_id !== Auth::user()->clinic_id,
            403
        );
    }
}