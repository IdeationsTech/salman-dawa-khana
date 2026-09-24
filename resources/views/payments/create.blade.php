@extends('layouts.app')

@section('title', isset($payment) ? 'Edit Payment' : 'Bill and Payment')

@section('content')
@php
    $editing = isset($payment);

    $defaultMode = $editing
        ? ($payment->bill_id ? 'existing_bill' : 'unallocated')
        : (request('mode') === 'existing_bill' ? 'existing_bill' : 'new_bill');

    $mode = old('mode', $defaultMode);

    if (!in_array($mode, ['new_bill', 'existing_bill', 'unallocated'], true)) {
        $mode = $defaultMode;
    }

    $selectedPatient = (string) old(
        'patient_id',
        $editing ? $payment->patient_id : request('patient_id', '')
    );

    $selectedBill = (string) old(
        'bill_id',
        $editing ? $payment->bill_id : request('bill_id', '')
    );

    $initialMethod = $editing
        ? strtolower(trim($payment->method))
        : 'cash';

    if (in_array($initialMethod, ['bank transfer', 'bank_transfer'], true)) {
        $initialMethod = 'bank';
    }

    $selectedMethod = old('method', $initialMethod);

    $dateValue = old(
        'payment_date',
        $editing
            ? $payment->payment_date->format('Y-m-d\TH:i')
            : now('Asia/Dubai')->format('Y-m-d\TH:i')
    );
@endphp

<div class="app-shell">
    @include('payments._sidebar')

    <main class="dashboard-main">
        <div class="form-page-header">
            <a href="{{ route('payments.index') }}" class="back-link">
                ← Back to payments
            </a>

            <h1>{{ $editing ? 'Edit payment' : 'Bill and payment' }}</h1>

            <p>
                Create a patient bill or receive payment against an existing bill.
            </p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Please correct the following:</strong>

                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ $editing
                ? route('payments.update', $payment)
                : route('payments.store') }}"
            data-payment-form
            data-currency="{{ $currency }}"
            data-editing="{{ $editing ? '1' : '0' }}"
        >
            @csrf

            @if($editing)
                @method('PUT')
            @endif

            {{-- BILLING — Mode and Patient --}}
            <section class="payment-form-panel">
                <div class="payment-form-heading">
                    <div class="form-section-number">01</div>

                    <div>
                        <h2>Patient and billing option</h2>
                        <p>Choose what you want to record.</p>
                    </div>
                </div>

                <div class="payment-form-grid">
                    <div class="form-field">
                        <label for="billing_mode" class="form-label">
                            Billing option <span>*</span>
                        </label>

                        @if($editing)
                            <input
                                type="hidden"
                                name="mode"
                                value="{{ $defaultMode }}"
                            >
                        @endif

                        <select
                            id="billing_mode"
                            name="{{ $editing ? 'mode_display' : 'mode' }}"
                            class="form-select"
                            data-payment-mode
                            @disabled($editing)
                            required
                        >
                            @if(!$editing)
                                <option value="new_bill" @selected($mode === 'new_bill')>
                                    New Bill + Payment
                                </option>
                            @endif

                            <option
                                value="existing_bill"
                                @selected($mode === 'existing_bill')
                            >
                                Pay Existing Bill
                            </option>

                            @if($editing && $defaultMode === 'unallocated')
                                <option value="unallocated" selected>
                                    Existing unallocated payment
                                </option>
                            @endif
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="patient" class="form-label">
                            Patient <span>*</span>
                        </label>

                        @if($editing)
                            <input
                                type="hidden"
                                name="patient_id"
                                value="{{ $payment->patient_id }}"
                            >
                        @endif

                        <select
                            id="patient"
                            name="{{ $editing ? 'patient_display' : 'patient_id' }}"
                            class="form-select"
                            data-payment-patient
                            @disabled($editing)
                            required
                        >
                            <option value="">Select patient</option>

                            @foreach($patients as $patient)
                                <option
                                    value="{{ $patient->patient_id }}"
                                    @selected(
                                        $selectedPatient === (string) $patient->patient_id
                                    )
                                >
                                    {{ $patient->full_name }}
                                    — {{ $patient->patient_code }}
                                    {{ $patient->phone ? ' — ' . $patient->phone : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if($editing)
                    <small class="text-muted d-block mt-3">
                        Editing changes the payment only.
                        Its patient and related bill remain fixed.
                    </small>
                @endif
            </section>

            {{-- BILLING — New Bill Charges --}}
            <section
                class="payment-form-panel"
                data-new-bill-section
                @if($mode !== 'new_bill') hidden @endif
            >
                <div class="payment-form-heading">
                    <div class="form-section-number">02</div>

                    <div>
                        <h2>New bill</h2>
                        <p>Enter the charges for this bill.</p>
                    </div>
                </div>

                <fieldset
                    data-new-bill-fields
                    @disabled($mode !== 'new_bill')
                >
                    <div class="payment-form-grid">
                        <div class="form-field payment-field-wide">
                            <label for="description" class="form-label">
                                Charges description <span>*</span>
                            </label>

                            <input
                                type="text"
                                id="description"
                                name="description"
                                class="form-control"
                                value="{{ old('description') }}"
                                maxlength="255"
                                placeholder="Enter what this bill is for"
                                required
                            >
                        </div>

                        <div class="form-field">
                            <label for="subtotal" class="form-label">
                                Charges amount <span>*</span>
                            </label>

                            <div class="amount-input">
                                <span>{{ $currency }}</span>

                                <input
                                    type="number"
                                    id="subtotal"
                                    name="subtotal"
                                    class="form-control"
                                    value="{{ old('subtotal') }}"
                                    min="0.01"
                                    max="9999999999.99"
                                    step="0.01"
                                    data-bill-subtotal
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="discount" class="form-label">
                                Discount
                            </label>

                            <div class="amount-input">
                                <span>{{ $currency }}</span>

                                <input
                                    type="number"
                                    id="discount"
                                    name="discount"
                                    class="form-control"
                                    value="{{ old('discount', '0.00') }}"
                                    min="0"
                                    max="9999999999.99"
                                    step="0.01"
                                    data-bill-discount
                                    required
                                >
                            </div>
                        </div>
                    </div>
                </fieldset>
            </section>

            {{-- BILLING — Existing Bill --}}
            <section
                class="payment-form-panel"
                data-existing-bill-section
                @if($mode !== 'existing_bill') hidden @endif
            >
                <div class="payment-form-heading">
                    <div class="form-section-number">02</div>

                    <div>
                        <h2>Existing bill</h2>
                        <p>Select this patient's outstanding bill.</p>
                    </div>
                </div>

                @if($editing)
                    <input
                        type="hidden"
                        name="bill_id"
                        value="{{ $payment->bill_id }}"
                    >
                @endif

                <div class="payment-form-grid">
                    <div class="form-field">
                        <label for="bill" class="form-label">
                            Bill <span>*</span>
                        </label>

                        <select
                            id="bill"
                            name="{{ $editing ? 'bill_display' : 'bill_id' }}"
                            class="form-select"
                            data-payment-bill
                            data-selected-bill="{{ $selectedBill }}"
                            @disabled($editing || $mode !== 'existing_bill')
                            @required($mode === 'existing_bill' && !$editing)
                        >
                            <option value="">Select bill</option>

                            @foreach($billData as $bill)
                                @if(
                                    (string) $bill['patient_id'] === $selectedPatient
                                    && (
                                        $bill['due_cents'] > 0
                                        || (string) $bill['bill_id'] === $selectedBill
                                    )
                                )
                                    <option
                                        value="{{ $bill['bill_id'] }}"
                                        @selected(
                                            (string) $bill['bill_id'] === $selectedBill
                                        )
                                    >
                                        {{ $bill['bill_no'] }}
                                        — {{ $currency }}
                                        {{ number_format($bill['due_cents'] / 100, 2) }}
                                        due
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <p class="text-muted" data-bill-empty-message hidden>
                            No outstanding bills for this patient.
                            Choose New Bill + Payment to create one.
                        </p>

                        <a
                            class="btn btn-outline-secondary"
                            data-bill-receipt-link
                            hidden
                        >
                            View selected bill
                        </a>
                    </div>
                </div>
            </section>

            {{-- BILLING — Balance Summary --}}
            <section class="payment-form-panel">
                <div class="payment-form-heading">
                    <div class="form-section-number">03</div>

                    <div>
                        <h2>Balance summary</h2>
                        <p>Previous dues are shown separately, not charged again.</p>
                    </div>
                </div>

                <div class="balance-summary-grid">
                    <div>
                        <span data-other-due-label>Previous outstanding bills</span>
                        <strong data-payment-other-due>—</strong>
                    </div>

                    <div>
                        <span data-current-bill-label>New bill total</span>
                        <strong data-total-payable>—</strong>
                    </div>

                    <div>
                        <span>Total outstanding before payment</span>
                        <strong data-payment-total-due>—</strong>
                    </div>
                </div>

                <p class="text-muted small mt-3 mb-0" data-payment-help>
                    Payment is applied only to the new or selected bill.
                </p>

                @if($editing)
                    <small class="text-muted d-block mt-2">
                        Preview excludes the payment being edited.
                    </small>
                @endif
            </section>

            {{-- BILLING — Received Payment --}}
            <section class="payment-form-panel">
                <div class="payment-form-heading">
                    <div class="form-section-number">04</div>

                    <div>
                        <h2>Payment received</h2>
                        <p>
                            For a new bill, enter 0 if nothing has been received.
                        </p>
                    </div>
                </div>

                <div class="payment-form-grid">
                    <div class="form-field">
                        <label for="amount" class="form-label">
                            Amount received <span>*</span>
                        </label>

                        <div class="amount-input">
                            <span>{{ $currency }}</span>

                            <input
                                type="number"
                                id="amount"
                                name="amount"
                                class="form-control"
                                value="{{ old('amount', $payment->amount ?? '0.00') }}"
                                min="{{ $mode === 'new_bill' ? '0' : '0.01' }}"
                                max="9999999999.99"
                                step="0.01"
                                data-payment-amount
                                required
                            >
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="payment_date" class="form-label">
                            <span class="text-reset" data-payment-date-label>
                                Bill / payment date and time
                            </span>
                            <span>*</span>
                        </label>

                        <input
                            type="datetime-local"
                            id="payment_date"
                            name="payment_date"
                            class="form-control"
                            value="{{ $dateValue }}"
                            min="1000-01-01T00:00"
                            max="9999-12-31T23:59"
                            required
                        >

                        <small class="text-muted">UAE local time.</small>
                    </div>

                    <div class="form-field">
                        <label for="payment_method" class="form-label">
                            Payment method
                        </label>

                        <select
                            id="payment_method"
                            name="method"
                            class="form-select"
                            data-payment-method
                        >
                            <option value="cash" @selected($selectedMethod === 'cash')>
                                Cash
                            </option>

                            <option value="bank" @selected($selectedMethod === 'bank')>
                                Bank transfer
                            </option>

                            <option value="card" @selected($selectedMethod === 'card')>
                                Card
                            </option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="reference" class="form-label">
                            Transaction reference
                            <span data-payment-reference-star hidden>*</span>
                        </label>

                        <input
                            type="text"
                            id="reference"
                            name="reference_no"
                            class="form-control"
                            value="{{ old('reference_no', $payment->reference_no ?? '') }}"
                            maxlength="80"
                            data-payment-reference
                        >
                    </div>

                    <div class="form-field payment-field-wide">
                        <label for="notes" class="form-label">
                            Payment notes
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            class="form-control"
                            rows="3"
                            maxlength="255"
                            data-payment-notes
                            placeholder="Saved only when a payment is received"
                        >{{ old('notes', $payment->notes ?? '') }}</textarea>
                    </div>
                </div>

                <div class="remaining-balance-box">
                    <span data-payment-remaining-label>
                        This bill's remaining balance
                    </span>

                    <strong data-remaining-balance aria-live="polite">—</strong>
                </div>

                <p class="text-muted small mt-3 mb-0">
                    Total outstanding bills after this payment:
                    <strong data-patient-remaining>—</strong>
                </p>
            </section>

            <div class="payment-form-actions">
                <a href="{{ route('payments.index') }}" class="btn btn-light">
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                    data-payment-submit
                >
                    {{ $editing ? 'Update payment' : 'Save bill and payment' }}
                </button>
            </div>

            <script type="application/json" data-payment-bills>
                @json($billData)
            </script>
        </form>
    </main>
</div>
@endsection