<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Clinic;
use App\Models\LedgerEntry;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Setting;
use App\Support\ClinicLocaleCatalog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = $this->clinicId();
        $clinic = $this->clinic();
        $currency = strtoupper($clinic->currency ?: 'AED');

        $moneyDecimals = (string) Setting::where('clinic_id', $clinicId)
            ->where('setting_key', 'decimal_places')
            ->value('setting_value') === '0' ? 0 : 2;

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'method' => ['nullable', Rule::in(['all', 'cash', 'bank', 'card'])],
            'date_range' => [
                'nullable',
                Rule::in(['all time', 'today', 'this week', 'this month']),
            ],
        ]);

        $method = $filters['method'] ?? 'all';
        $dateRange = $filters['date_range'] ?? 'all time';
        $search = trim($filters['search'] ?? '');

        $query = Payment::where('clinic_id', $clinicId)->with([
            'patient' => fn ($query) => $query->where('clinic_id', $clinicId),
        ]);

        if ($search !== '') {
            $query->where(function ($query) use ($search, $clinicId) {
                $query->where('reference_no', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($patient) use ($search, $clinicId) {
                        $patient->where('clinic_id', $clinicId)
                            ->where(function ($patient) use ($search) {
                                $patient->where('full_name', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%")
                                    ->orWhere('patient_code', 'like', "%{$search}%");
                            });
                    });

                $id = preg_replace('/^PAY-/i', '', $search);

                if (ctype_digit($id)) {
                    $query->orWhere('payment_id', (int) $id);
                }
            });
        }

        if ($method !== 'all') {
            $query->whereIn(DB::raw('LOWER(TRIM(method))'), $method === 'bank'
                ? ['bank', 'bank transfer', 'bank_transfer']
                : [$method]);
        }

        $today = Carbon::now('Asia/Dubai')->startOfDay();

        if ($dateRange !== 'all time') {
            [$start, $end] = match ($dateRange) {
                'today' => [$today->copy(), $today->copy()->addDay()],
                'this week' => [
                    $today->copy()->startOfWeek(Carbon::MONDAY),
                    $today->copy()->startOfWeek(Carbon::MONDAY)->addWeek(),
                ],
                'this month' => [
                    $today->copy()->startOfMonth(),
                    $today->copy()->startOfMonth()->addMonth(),
                ],
            };

            $query->where('payment_date', '>=', $start->format('Y-m-d H:i:s'))
                ->where('payment_date', '<', $end->format('Y-m-d H:i:s'));
        }

        $payments = $query->orderByDesc('payment_date')
            ->orderByDesc('payment_id')->paginate(50)->withQueryString();

        $base = Payment::where('clinic_id', $clinicId)->where('currency', $currency);
        $totalReceived = (clone $base)->sum('amount');

        $todayReceived = (clone $base)
            ->where('payment_date', '>=', $today->format('Y-m-d H:i:s'))
            ->where('payment_date', '<', $today->copy()->addDay()->format('Y-m-d H:i:s'))
            ->sum('amount');

        $monthStart = $today->copy()->startOfMonth();

        $monthReceived = (clone $base)
            ->where('payment_date', '>=', $monthStart->format('Y-m-d H:i:s'))
            ->where('payment_date', '<', $monthStart->copy()->addMonth()->format('Y-m-d H:i:s'))
            ->sum('amount');

        $unallocatedReceived = (clone $base)->whereNull('bill_id')->sum('amount');

        $paidByBill = DB::table('payments')
            ->where('clinic_id', $clinicId)->where('currency', $currency)
            ->whereNotNull('bill_id')->select('bill_id', 'patient_id')
            ->selectRaw('SUM(amount) AS received_amount')
            ->groupBy('bill_id', 'patient_id');

        $balanceRow = DB::table('bills as balance_bills')
            ->leftJoinSub($paidByBill, 'bill_payments', function ($join) {
                $join->on('bill_payments.bill_id', '=', 'balance_bills.bill_id')
                    ->on('bill_payments.patient_id', '=', 'balance_bills.patient_id');
            })
            ->where('balance_bills.clinic_id', $clinicId)
            ->where('balance_bills.currency', $currency)
            ->where(fn ($query) => $query->whereNull('balance_bills.status')
                ->orWhereNotIn('balance_bills.status', ['cancelled', 'void']))
            ->selectRaw(
                'COALESCE(SUM(GREATEST(balance_bills.total_amount
                - COALESCE(bill_payments.received_amount, 0), 0)), 0) AS outstanding'
            )->first();

        $outstandingBalance = $balanceRow->outstanding ?? '0.00';
        $unconfirmedCurrencyCount = Payment::where('clinic_id', $clinicId)
            ->whereNull('currency')->count();

        $monthLabel = $today->format('F Y');
        $paymentMethods = collect(['cash', 'bank', 'card']);

        return view('payments.index', compact(
            'payments', 'totalReceived', 'todayReceived', 'monthReceived',
            'unallocatedReceived', 'outstandingBalance', 'clinic', 'currency',
            'moneyDecimals', 'monthLabel', 'paymentMethods', 'method',
            'dateRange', 'unconfirmedCurrencyCount'
        ));
    }

    public function create()
    {
        return view('payments.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validateForm($request);

        [$bill, $payment] = DB::transaction(function () use ($data) {
            $clinic = Clinic::where('clinic_id', $this->clinicId())
                ->lockForUpdate()->firstOrFail();

            $this->lockPatient((int) $data['patient_id']);

            if ($data['mode'] === 'new_bill') {
                $subtotal = $this->cents($data['subtotal']);
                $discount = $this->cents($data['discount']);
                $total = $subtotal - $discount;

                $bill = Bill::create([
                    'clinic_id' => $this->clinicId(),
                    'currency' => strtoupper($clinic->currency ?: 'AED'),
                    'patient_id' => $data['patient_id'],
                    'visit_id' => null,
                    'created_by_user_id' => auth()->id(),
                    'bill_no' => 'B-' . Str::ulid(),
                    'bill_date' => $data['payment_date'],
                    'subtotal' => $this->decimal($subtotal),
                    'discount' => $this->decimal($discount),
                    'total_amount' => $this->decimal($total),
                    'paid_amount' => '0.00',
                    'due_amount' => $this->decimal($total),
                    'status' => 'unpaid',
                ]);

                BillItem::create([
                    'bill_id' => $bill->bill_id,
                    'description' => $data['description'],
                    'quantity' => '1.00',
                    'unit_price' => $this->decimal($subtotal),
                    'line_total' => $this->decimal($subtotal),
                ]);

                LedgerEntry::create([
                    'clinic_id' => $this->clinicId(),
                    'patient_id' => $bill->patient_id,
                    'bill_id' => $bill->bill_id,
                    'payment_id' => null,
                    'expense_id' => null,
                    'created_by_user_id' => auth()->id(),
                    'entry_date' => $data['payment_date'],
                    'entry_type' => 'bill',
                    'debit' => $this->decimal($total),
                    'credit' => '0.00',
                    'description' => 'Bill ' . $bill->bill_no,
                ]);
            } else {
                $bill = $this->lockBill(
                    $data['bill_id'],
                    (int) $data['patient_id']
                );
            }

            $billCurrency = strtoupper(trim((string) $bill->currency));

            if (!ClinicLocaleCatalog::knownCurrency($billCurrency)) {
                throw ValidationException::withMessages([
                    'bill_id' => 'This bill has no confirmed currency. Confirm its original currency before recording payment.',
                ]);
            }

            $this->checkAmount($bill, $data['amount']);
            $payment = null;

            if ($this->cents($data['amount']) > 0) {
                $payment = Payment::create([
                    ...$this->paymentValues($data),
                    'clinic_id' => $this->clinicId(),
                    'currency' => $billCurrency,
                    'patient_id' => $data['patient_id'],
                    'bill_id' => $bill->bill_id,
                    'received_by_user_id' => auth()->id(),
                ]);

                $this->writePaymentLedger($payment);
            }

            $this->refreshBill($bill);

            if ($payment) {
                app(\App\Services\ClinicNotificationService::class)
                    ->paymentReceived($payment);
            }

            return [$bill, $payment];
        }, 3);

        if ($payment) {
            return redirect()->route('payments.show', $payment)
                ->with('success', 'Bill balance and payment saved successfully.');
        }

        return redirect()->route('payments.bills.show', $bill)
            ->with('success', 'Bill saved. The full amount remains outstanding.');
    }

    public function show(Payment $payment)
    {
        $this->ensureClinicAccess($payment);

        $bill = $payment->bill_id
            ? Bill::where('clinic_id', $this->clinicId())
                ->where('patient_id', $payment->patient_id)
                ->whereKey($payment->bill_id)->firstOrFail()
            : null;

        return $this->receipt($bill, $payment);
    }

    public function billReceipt(Bill $bill)
    {
        $this->ensureClinicAccess($bill);

        return $this->receipt($bill);
    }

    private function receipt(?Bill $bill, ?Payment $payment = null)
    {
        $clinicId = $this->clinicId();
        $patientId = $bill?->patient_id ?? $payment?->patient_id;
        abort_unless($patientId, 404);

        $patient = Patient::where('clinic_id', $clinicId)
            ->whereKey($patientId)->firstOrFail();

        $clinic = $this->clinic();
        $currency = strtoupper(trim(
            (string) ($bill ? $bill->currency : $payment?->currency)
        ));

        abort_unless(
            ClinicLocaleCatalog::knownCurrency($currency),
            422,
            'This record has no confirmed currency.'
        );

        if ($payment) {
            abort_unless(
                strtoupper(trim((string) $payment->currency)) === $currency,
                422,
                'The payment currency does not match the bill currency.'
            );

            $payment->load([
                'receivedBy' => fn ($query) => $query->where('clinic_id', $clinicId),
            ]);
        }

        if ($bill) {
            $bill->load('items');
        }

        $billPayments = $bill
            ? Payment::where('clinic_id', $clinicId)
                ->where('patient_id', $patientId)
                ->where('bill_id', $bill->bill_id)
                ->orderBy('payment_date')->orderBy('payment_id')->get()
            : collect();

        $paidCents = 0;

        foreach ($billPayments as $record) {
            abort_unless(
                strtoupper(trim((string) $record->currency)) === $currency,
                422,
                'A payment against this bill has an inconsistent currency.'
            );

            $paidCents += $this->cents($record->amount);
        }

        $billPaid = $this->decimal($paidCents);
        $billDue = $bill
            ? $this->decimal(max(0, $this->cents($bill->total_amount) - $paidCents))
            : null;

        $patientBills = Bill::where('clinic_id', $clinicId)
            ->where('patient_id', $patientId)->where('currency', $currency)
            ->where(fn ($query) => $query->whereNull('status')
                ->orWhereNotIn('status', ['cancelled', 'void']))
            ->withSum([
                'payments as recorded_paid' => fn ($query) => $query
                    ->where('clinic_id', $clinicId)
                    ->where('patient_id', $patientId)
                    ->where('currency', $currency),
            ], 'amount')->get();

        $outstandingCents = 0;
        $otherBillsCents = 0;

        foreach ($patientBills as $patientBill) {
            $remaining = max(
                0,
                $this->cents($patientBill->total_amount)
                    - $this->cents($patientBill->recorded_paid ?? '0')
            );

            $outstandingCents += $remaining;

            if (!$bill || (int) $patientBill->bill_id !== (int) $bill->bill_id) {
                $otherBillsCents += $remaining;
            }
        }

        $patientOutstanding = $this->decimal($outstandingCents);
        $otherBillsDue = $this->decimal($otherBillsCents);

        return view('payments.show', compact(
            'bill', 'payment', 'patient', 'clinic', 'currency', 'billPaid',
            'billDue', 'patientOutstanding', 'otherBillsDue', 'billPayments'
        ));
    }

    public function edit(Payment $payment)
    {
        $this->ensureClinicAccess($payment);

        return view('payments.create', [
            ...$this->formData($payment),
            'payment' => $payment,
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        $this->ensureClinicAccess($payment);
        $data = $this->validateForm($request, $payment);

        DB::transaction(function () use ($payment, $data) {
            $this->lockPatient((int) $payment->patient_id);

            $locked = Payment::where('clinic_id', $this->clinicId())
                ->whereKey($payment->payment_id)
                ->lockForUpdate()->firstOrFail();

            if (
                (int) $locked->patient_id !== (int) $data['patient_id']
                || (int) $locked->bill_id !== (int) ($data['bill_id'] ?? 0)
            ) {
                throw ValidationException::withMessages([
                    'bill_id' => 'The patient or bill has changed. Reload the page.',
                ]);
            }

            $savedCurrency = strtoupper(trim((string) $locked->currency));

            if (!ClinicLocaleCatalog::knownCurrency($savedCurrency)) {
                throw ValidationException::withMessages([
                    'amount' => 'This payment has no confirmed currency.',
                ]);
            }

            $bill = $locked->bill_id
                ? $this->lockBill($locked->bill_id, (int) $locked->patient_id)
                : null;

            if ($bill && strtoupper(trim((string) $bill->currency)) !== $savedCurrency) {
                throw ValidationException::withMessages([
                    'bill_id' => 'The payment and bill currencies do not match.',
                ]);
            }

            $this->checkAmount($bill, $data['amount'], (int) $locked->payment_id);
            $locked->update($this->paymentValues($data));
            $this->writePaymentLedger($locked);

            if ($bill) {
                $this->refreshBill($bill);
            }
        }, 3);

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment and balances updated.');
    }

    public function destroy(Payment $payment)
    {
        $this->ensureClinicAccess($payment);

        DB::transaction(function () use ($payment) {
            $this->lockPatient((int) $payment->patient_id);

            $locked = Payment::where('clinic_id', $this->clinicId())
                ->whereKey($payment->payment_id)
                ->lockForUpdate()->firstOrFail();

            $bill = $locked->bill_id
                ? $this->lockBill($locked->bill_id, (int) $locked->patient_id)
                : null;

            foreach ($this->paymentLedgerEntries($locked) as $entry) {
                $entry->delete();
            }

            $locked->delete();

            if ($bill) {
                $this->refreshBill($bill);
            }
        }, 3);

        return redirect()->route('payments.index')
            ->with('success', 'Payment removed and bill balance updated.');
    }

    private function validateForm(Request $request, ?Payment $payment = null): array
    {
        $clinicId = $this->clinicId();

        $fixedMode = $payment
            ? ($payment->bill_id ? 'existing_bill' : 'unallocated')
            : null;

        $request->validate([
            'mode' => [
                'required',
                Rule::in($payment ? [$fixedMode] : ['new_bill', 'existing_bill']),
            ],
            'patient_id' => [
                'required', 'integer',
                Rule::exists('patients', 'patient_id')->where('clinic_id', $clinicId),
            ],
        ]);

        $mode = $request->input('mode');
        $patientId = (int) $request->input('patient_id');
        $newBill = $mode === 'new_bill';

        $moneyRules = [
            'bail', 'required', 'numeric', 'min:0',
            'max:9999999999.99', 'regex:/^\d{1,10}(?:\.\d{1,2})?$/',
        ];

        $rawAmount = $request->input('amount');
        $receiving = is_numeric($rawAmount) && (float) $rawAmount > 0;

        $rules = [
            'mode' => ['required', Rule::in([$mode])],
            'patient_id' => ['required', 'integer'],
            'payment_date' => [
                'bail', 'required', 'date_format:Y-m-d\TH:i',
                'after_or_equal:1000-01-01',
                'before_or_equal:9999-12-31 23:59:59',
            ],
            'amount' => [...$moneyRules, $newBill ? 'min:0' : 'min:0.01'],
            'method' => [
                Rule::requiredIf($receiving),
                Rule::excludeIf(!$receiving),
                'nullable',
                Rule::in($this->enabledPaymentMethods()),
            ],
            'reference_no' => [
                Rule::requiredIf(
                    $receiving && in_array($request->input('method'), ['bank', 'card'], true)
                ),
                'nullable', 'string', 'max:80',
            ],
            'notes' => [
                Rule::excludeIf(!$receiving), 'nullable', 'string', 'max:255',
            ],
        ];

        if ($newBill) {
            $rules['description'] = ['required', 'string', 'max:255'];
            $rules['subtotal'] = [...$moneyRules, 'min:0.01'];
            $rules['discount'] = $moneyRules;
        } elseif ($mode === 'existing_bill') {
            $rules['bill_id'] = [
                'required', 'integer',
                Rule::exists('bills', 'bill_id')
                    ->where('clinic_id', $clinicId)
                    ->where('patient_id', $patientId),
            ];
        }

        $data = $request->validate($rules, [
            'bill_id.required' => 'Select the bill you want to pay.',
            'bill_id.exists' => 'Select a bill belonging to this patient.',
            'amount.regex' => 'Use no more than two decimal places.',
            'reference_no.required' => 'Enter the bank or card transaction reference.',
            'method.in' => 'This payment method is disabled in your clinic settings.',
        ]);

        $data['bill_id'] = $newBill || $mode === 'unallocated'
            ? null
            : (int) $data['bill_id'];

        if (
            $payment
            && (
                (int) $payment->patient_id !== $patientId
                || (int) $payment->bill_id !== (int) ($data['bill_id'] ?? 0)
            )
        ) {
            throw ValidationException::withMessages([
                'patient_id' => 'The patient and bill cannot be reassigned while editing.',
            ]);
        }

        if ($newBill) {
            $subtotal = $this->cents($data['subtotal']);
            $discount = $this->cents($data['discount']);

            if ($discount >= $subtotal) {
                throw ValidationException::withMessages([
                    'discount' => 'Discount must be less than the charges amount.',
                ]);
            }

            if ($this->cents($data['amount']) > $subtotal - $discount) {
                throw ValidationException::withMessages([
                    'amount' => 'Received amount cannot exceed the new bill total.',
                ]);
            }
        }

        $data['amount'] = $this->decimal($this->cents($data['amount']));
        $data['payment_date'] = str_replace('T', ' ', $data['payment_date']) . ':00';

        return $data;
    }

    private function paymentValues(array $data): array
    {
        return [
            'payment_date' => $data['payment_date'],
            'amount' => $data['amount'],
            'method' => $data['method'],
            'reference_no' => $data['reference_no'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function formData(?Payment $payment = null): array
    {
        $clinic = $this->clinic();
        $clinicCurrency = strtoupper($clinic->currency ?: 'AED');
        $currency = $payment
            ? strtoupper(trim((string) $payment->currency))
            : $clinicCurrency;

        if ($payment && !ClinicLocaleCatalog::knownCurrency($currency)) {
            abort(422, 'This payment has no confirmed currency.');
        }

        $preferences = Setting::where('clinic_id', $this->clinicId())
            ->whereIn('setting_key', ['decimal_places', 'allow_partial_payments'])
            ->pluck('setting_value', 'setting_key');

        return [
            'clinic' => $clinic,
            'currency' => $currency,
            'clinicCurrency' => $clinicCurrency,
            'moneyDecimals' => (string) $preferences->get('decimal_places', '2') === '0' ? 0 : 2,
            'allowPartialPayments' => (string) $preferences->get('allow_partial_payments', '1') === '1',
            'enabledMethods' => $this->enabledPaymentMethods(),
            'patients' => Patient::where('clinic_id', $this->clinicId())
                ->orderBy('full_name')
                ->get(['patient_id', 'patient_code', 'full_name', 'phone']),
            'billData' => $this->billOptions(
                $payment ? (int) $payment->payment_id : null
            )->values()->all(),
        ];
    }

    private function billOptions(?int $excludePaymentId = null)
    {
        $clinicId = $this->clinicId();

        return Bill::where('clinic_id', $clinicId)
            ->where(fn ($query) => $query->whereNull('status')
                ->orWhereNotIn('status', ['cancelled', 'void']))
            ->withSum([
                'payments as recorded_paid' => function ($query) use ($clinicId, $excludePaymentId) {
                    $query->where('clinic_id', $clinicId)
                        ->whereColumn('payments.patient_id', 'bills.patient_id')
                        ->whereColumn('payments.currency', 'bills.currency');

                    if ($excludePaymentId !== null) {
                        $query->where('payment_id', '<>', $excludePaymentId);
                    }
                },
            ], 'amount')
            ->orderByDesc('bill_date')->get()
            ->map(function (Bill $bill) {
                $total = $this->cents($bill->total_amount);
                $paid = $this->cents($bill->recorded_paid ?? '0');

                return [
                    'bill_id' => (int) $bill->bill_id,
                    'patient_id' => (int) $bill->patient_id,
                    'bill_no' => $bill->bill_no,
                    'currency' => $bill->currency ? strtoupper($bill->currency) : null,
                    'total_cents' => $total,
                    'paid_cents' => $paid,
                    'due_cents' => max(0, $total - $paid),
                    'receipt_url' => route('payments.bills.show', $bill),
                ];
            });
    }

    private function lockPatient(int $patientId): Patient
    {
        return Patient::where('clinic_id', $this->clinicId())
            ->whereKey($patientId)->lockForUpdate()->firstOrFail();
    }

    private function lockBill(int $billId, int $patientId): Bill
    {
        $bill = Bill::where('clinic_id', $this->clinicId())
            ->where('patient_id', $patientId)
            ->whereKey($billId)->lockForUpdate()->first();

        if (!$bill || in_array($bill->status, ['cancelled', 'void'], true)) {
            throw ValidationException::withMessages([
                'bill_id' => 'This bill is unavailable or cancelled.',
            ]);
        }

        return $bill;
    }

    private function paidCents(Bill $bill, ?int $excludePaymentId = null): int
    {
        $query = Payment::where('clinic_id', $this->clinicId())
            ->where('bill_id', $bill->bill_id);

        if ($excludePaymentId !== null) {
            $query->where('payment_id', '<>', $excludePaymentId);
        }

        return $this->cents($query->sum('amount'));
    }

    private function checkAmount(
        ?Bill $bill,
        string $amount,
        ?int $excludePaymentId = null
    ): void {
        if (!$bill) {
            return;
        }

        $available = max(
            0,
            $this->cents($bill->total_amount)
                - $this->paidCents($bill, $excludePaymentId)
        );

        $received = $this->cents($amount);

        if ($received > $available) {
            throw ValidationException::withMessages([
                'amount' => 'Amount exceeds the remaining bill balance: '
                    . $this->decimal($available) . '.',
            ]);
        }

        $partialSetting = Setting::where('clinic_id', $this->clinicId())
            ->where('setting_key', 'allow_partial_payments')
            ->value('setting_value');

        $allowPartial = $partialSetting === null || (string) $partialSetting === '1';

        if (!$allowPartial && $received > 0 && $received < $available) {
            throw ValidationException::withMessages([
                'amount' => 'Partial payments are disabled. Enter the full remaining balance: '
                    . $this->decimal($available) . '.',
            ]);
        }
    }

    private function refreshBill(Bill $bill): void
    {
        $paid = $this->paidCents($bill);
        $due = max(0, $this->cents($bill->total_amount) - $paid);

        $bill->update([
            'paid_amount' => $this->decimal($paid),
            'due_amount' => $this->decimal($due),
            'status' => $due === 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
        ]);
    }

    private function paymentLedgerEntries(Payment $payment)
    {
        $entries = LedgerEntry::where('clinic_id', $this->clinicId())
            ->where('payment_id', $payment->payment_id)
            ->lockForUpdate()->get();

        if (
            $entries->count() > 1
            || $entries->contains(fn ($entry) =>
                $entry->entry_type !== 'payment' || $this->cents($entry->debit) !== 0)
        ) {
            throw ValidationException::withMessages([
                'payment' => 'This payment has ledger entries that need review before editing or deleting.',
            ]);
        }

        return $entries;
    }

    private function writePaymentLedger(Payment $payment): void
    {
        $entries = $this->paymentLedgerEntries($payment);

        $values = [
            'clinic_id' => $this->clinicId(),
            'patient_id' => $payment->patient_id,
            'bill_id' => $payment->bill_id,
            'payment_id' => $payment->payment_id,
            'expense_id' => null,
            'entry_date' => $payment->payment_date->format('Y-m-d H:i:s'),
            'entry_type' => 'payment',
            'debit' => '0.00',
            'credit' => $payment->amount,
            'description' => 'Payment PAY-' . $payment->payment_id,
        ];

        if ($entries->isEmpty()) {
            LedgerEntry::create([...$values, 'created_by_user_id' => auth()->id()]);
        } else {
            $entries->first()->update($values);
        }
    }

    private function clinicId(): int
    {
        return (int) auth()->user()->clinic_id;
    }

    private function clinic(): Clinic
    {
        return Clinic::findOrFail($this->clinicId());
    }

    private function ensureClinicAccess($record): void
    {
        abort_unless((int) $record->clinic_id === $this->clinicId(), 403);
    }

    private function cents($amount): int
    {
        $value = trim((string) $amount);
        $negative = str_starts_with($value, '-');
        $value = ltrim($value, '+-');

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');

        $result = ((int) $whole * 100)
            + (int) str_pad(substr($fraction, 0, 2), 2, '0');

        return $negative ? -$result : $result;
    }

    private function decimal(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $cents = abs($cents);

        return $sign . intdiv($cents, 100) . '.'
            . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    private function enabledPaymentMethods(): array
    {
        $settings = Setting::where('clinic_id', $this->clinicId())
            ->whereIn('setting_key', ['cash_enabled', 'bank_enabled', 'card_enabled'])
            ->pluck('setting_value', 'setting_key');

        $methods = [];

        foreach ([
            'cash' => 'cash_enabled',
            'bank' => 'bank_enabled',
            'card' => 'card_enabled',
        ] as $method => $key) {
            if ((string) $settings->get($key, '1') === '1') {
                $methods[] = $method;
            }
        }

        return $methods;
    }
}