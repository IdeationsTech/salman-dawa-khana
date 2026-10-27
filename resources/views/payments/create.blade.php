@extends('layouts.app')

@section('title', isset($payment) ? 'Edit Payment' : 'Bill and Payment')

@section('content')
@php
    $editing = isset($payment);

    $defaultMode = $editing
        ? ($payment->bill_id ? 'existing_bill' : 'unallocated')
        : (request('mode') === 'existing_bill' ? 'existing_bill' : 'new_bill');

    $mode = $editing ? $defaultMode : old('mode', $defaultMode);

    if (!in_array($mode, ['new_bill', 'existing_bill'], true) && !$editing) {
        $mode = 'new_bill';
    }

    $selectedPatient = (string) ($editing
        ? $payment->patient_id
        : old('patient_id', request('patient_id', '')));

    $lockPatient = !$editing
        && $selectedPatient !== ''
        && (request()->boolean('lock_patient') || old('lock_patient') === '1');

    $selectedBill = (string) ($editing
        ? $payment->bill_id
        : old('bill_id', request('bill_id', '')));

    $initialMethod = $editing
        ? strtolower(trim($payment->method))
        : ($enabledMethods[0] ?? '');

    if (in_array($initialMethod, ['bank transfer', 'bank_transfer'], true)) {
        $initialMethod = 'bank';
    }

    $selectedMethod = old('method', $initialMethod);

    $dateValue = old(
        'payment_date',
        $editing
            ? $payment->payment_date->format('Y-m-d\TH:i')
            : \App\Support\ClinicClock::now()->format('Y-m-d\TH:i')
    );
@endphp

<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <div class="form-page-header">
            <a href="{{ route('payments.index') }}" class="back-link">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                {{ __('Back to payments') }}
            </a>
            <h1>{{ $editing ? __('Edit payment') : 'Bill and payment' }}</h1>
            <p>{{ __('Create a bill or receive payment against an existing bill.') }}</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>{{ __('Please correct the following:') }}</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ __($error) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ $editing
                ? route('payments.update', $payment)
                : route('payments.store') }}"
            data-currency-payment-form
            data-bills-url="{{ route('payments.patient-bills', ['patient' => '__PATIENT__']) }}"
            data-clinic-currency="{{ $clinicCurrency }}"
            data-saved-currency="{{ $editing ? $currency : '' }}"
            data-editing="{{ $editing ? '1' : '0' }}"
            data-money-decimals="{{ $moneyDecimals }}"
            data-allow-partial="{{ $allowPartialPayments ? '1' : '0' }}"
        >
            @csrf
            <input type="hidden" name="request_key" value="{{ old('request_key', (string) \Illuminate\Support\Str::uuid()) }}">
            @if ($lockPatient)
                <input type="hidden" name="lock_patient" value="1">
            @endif
            @if ($editing)
                @method('PUT')
                <input type="hidden" name="mode" value="{{ $defaultMode }}">
                <input type="hidden" name="patient_id" value="{{ $payment->patient_id }}">
                @if ($payment->bill_id)
                    <input type="hidden" name="bill_id" value="{{ $payment->bill_id }}">
                @endif
            @elseif ($mode === 'new_bill' && request()->filled('visit_id'))
                <input
                    type="hidden"
                    name="visit_id"
                    value="{{ old('visit_id', request('visit_id')) }}"
                >
            @endif

            <section class="payment-form-panel">
                <div class="payment-form-heading">
                    <div class="form-section-number">01</div>
                    <div>
                        <h2>{{ __('Patient and billing option') }}</h2>
                        <p>
                            {{ __('Currency') }}:
                            <strong data-cp-currency>{{ $currency }}</strong>
                        </p>
                    </div>
                </div>

                <div class="payment-form-grid">
                    <div class="form-field">
                        <label for="billing_mode" class="form-label">{{ __('Billing option') }} *</label>
                        <select
                            id="billing_mode"
                            name="{{ $editing ? 'mode_display' : 'mode' }}"
                            class="form-select"
                            data-cp-mode
                            @disabled($editing)
                            required
                        >
                            @if (!$editing)
                                <option value="new_bill" @selected($mode === 'new_bill')>
                                    {{ __('New bill + payment') }}
                                </option>
                            @endif

                            <option value="existing_bill" @selected($mode === 'existing_bill')>
                                {{ __('Pay existing bill') }}
                            </option>

                            @if ($editing && $mode === 'unallocated')
                                <option value="unallocated" selected>
                                    {{ __('Existing unallocated payment') }}
                                </option>
                            @endif
                        </select>
                    </div>

                    <x-patient-search :patient="$patients->first()" :locked="$editing || $lockPatient" :billing="true" />
                </div>

                @if ($editing)
                    <small class="text-muted d-block mt-3">
                        {{ __('Patient, bill and original payment currency remain fixed.') }}
                    </small>
                @elseif ($lockPatient)
                    <small class="text-muted d-block mt-3">
                        {{ __('Patient selected from this visit. The patient cannot be changed.') }}
                    </small>
                @endif
            </section>

            <section class="payment-form-panel" data-cp-new-section>
                <div class="payment-form-heading">
                    <div class="form-section-number">02</div>
                    <div>
                        <h2>{{ __('New bill') }}</h2>
                        <p>{{ __('New bills use') }} {{ $clinicCurrency }}.</p>
                    </div>
                </div>

                <fieldset data-cp-new-fields @disabled($mode !== 'new_bill')>
                    <div class="payment-form-grid">
                        <div class="form-field payment-field-wide">
                            <label for="description" class="form-label">{{ __('Charges description') }} *</label>
                            <input
                                type="text"
                                id="description"
                                name="description"
                                class="form-control"
                                value="{{ old('description') }}"
                                maxlength="255"
                                required
                            >
                        </div>

                        <div class="form-field">
                            <label for="subtotal" class="form-label">{{ __('Charges amount') }} *</label>
                            <div class="amount-input">
                                <span>{{ $clinicCurrency }}</span>
                                <input
                                    type="number"
                                    id="subtotal"
                                    name="subtotal"
                                    class="form-control"
                                    value="{{ old('subtotal') }}"
                                    min="0.01"
                                    max="9999999999.99"
                                    step="0.01"
                                    data-cp-subtotal
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="discount" class="form-label">{{ __('Discount') }}</label>
                            <div class="amount-input">
                                <span>{{ $clinicCurrency }}</span>
                                <input
                                    type="number"
                                    id="discount"
                                    name="discount"
                                    class="form-control"
                                    value="{{ old('discount', '0.00') }}"
                                    min="0"
                                    max="9999999999.99"
                                    step="0.01"
                                    data-cp-discount
                                    required
                                >
                            </div>
                        </div>
                    </div>
                </fieldset>
            </section>

            <section class="payment-form-panel" data-cp-existing-section>
                <div class="payment-form-heading">
                    <div class="form-section-number">02</div>
                    <div>
                        <h2>{{ __('Existing bill') }}</h2>
                        <p>{{ __('Payment uses the selected bill\'s original currency.') }}</p>
                    </div>
                </div>

                <div class="payment-form-grid">
                    <div class="form-field">
                        <label for="bill" class="form-label">{{ __('Bill') }} *</label>
                        <select
                            id="bill"
                            name="{{ $editing ? 'bill_display' : 'bill_id' }}"
                            class="form-select"
                            data-cp-bill
                            data-selected-bill="{{ $selectedBill }}"
                            @disabled($editing || $mode !== 'existing_bill')
                        >
                            <option value="">{{ __('Select bill') }}</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <p class="text-muted" data-cp-empty hidden>
                            {{ __('No outstanding bills for this patient.') }}
                        </p>
                        <a class="btn btn-outline-secondary" data-cp-receipt hidden>
                            {{ __('View selected bill') }}
                        </a>
                    </div>
                </div>
            </section>

            <section class="payment-form-panel">
                <div class="payment-form-heading">
                    <div class="form-section-number">03</div>
                    <div>
                        <h2>{{ __('Balance summary') }}</h2>
                        <p>{{ __('Balances include only the displayed currency.') }}</p>
                    </div>
                </div>

                <div class="balance-summary-grid">
                    <div>
                        <span>{{ __('Other outstanding bills') }}</span>
                        <strong data-cp-other>—</strong>
                    </div>
                    <div>
                        <span>{{ __('New / selected bill due') }}</span>
                        <strong data-cp-current>—</strong>
                    </div>
                    <div>
                        <span>{{ __('Total outstanding before payment') }}</span>
                        <strong data-cp-total>—</strong>
                    </div>
                </div>

                <p class="text-muted small mt-3 mb-0" data-cp-help>
                    {{ __('Payment applies only to the new or selected bill.') }}
                </p>

                @if ($editing)
                    <small class="text-muted d-block mt-2">
                        {{ __('Preview excludes the payment being edited.') }}
                    </small>
                @endif
            </section>

            <section class="payment-form-panel">
                <div class="payment-form-heading">
                    <div class="form-section-number">04</div>
                    <div>
                        <h2>{{ __('Payment received') }}</h2>
                        <p>{{ __('For a new bill, enter 0 if nothing has been received.') }}</p>
                    </div>
                </div>

                <div class="payment-form-grid">
                    <div class="form-field">
                        <label for="amount" class="form-label">{{ __('Amount received') }} *</label>
                        <div class="amount-input">
                            <span data-cp-currency>{{ $currency }}</span>
                            <input
                                type="number"
                                id="amount"
                                name="amount"
                                class="form-control"
                                value="{{ old('amount', $payment->amount ?? '0.00') }}"
                                min="{{ $mode === 'new_bill' ? '0' : '0.01' }}"
                                max="9999999999.99"
                                step="0.01"
                                data-cp-amount
                                required
                            >
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="payment_date" class="form-label">{{ __('Bill / payment date') }} *</label>
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
                        <small class="text-muted">{{ __('UAE local time.') }}</small>
                    </div>

                    <div class="form-field">
                        <label for="method" class="form-label">{{ __('Payment method') }}</label>
                        <select
                            id="method"
                            name="method"
                            class="form-select"
                            data-cp-method
                        >
                            <option value="">{{ __('Select payment method') }}</option>
                            @foreach ([
                                'cash' => 'Cash',
                                'bank' => 'Bank transfer',
                                'card' => 'Card',
                            ] as $value => $label)
                                @if (in_array($value, $enabledMethods, true))
                                    <option value="{{ $value }}" @selected($selectedMethod === $value)>
                                        {{ __($label) }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="reference" class="form-label">
                            {{ __('Transaction reference') }}
                            <span data-cp-reference-star hidden>*</span>
                        </label>
                        <input
                            type="text"
                            id="reference"
                            name="reference_no"
                            class="form-control"
                            value="{{ old('reference_no', $payment->reference_no ?? '') }}"
                            maxlength="80"
                            data-cp-reference
                        >
                    </div>

                    <div class="form-field payment-field-wide">
                        <label for="notes" class="form-label">{{ __('Payment notes') }}</label>
                        <textarea
                            id="notes"
                            name="notes"
                            class="form-control"
                            rows="3"
                            maxlength="255"
                            data-cp-notes
                        >{{ old('notes', $payment->notes ?? '') }}</textarea>
                    </div>
                </div>

                <div class="remaining-balance-box">
                    <span>{{ __('This bill\'s remaining balance') }}</span>
                    <strong data-cp-remaining aria-live="polite">—</strong>
                </div>

                <p class="text-muted small mt-3 mb-0">
                    {{ __('Outstanding after payment, same currency:') }}
                    <strong data-cp-patient-remaining>—</strong>
                </p>
            </section>

            <div class="payment-form-actions">
                <a href="{{ route('payments.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary" data-cp-submit>
                    {{ $editing ? __('Update payment') : __('Save bill and payment') }}
                </button>
            </div>

            <script type="application/json" data-cp-bills>
                @json($billData)
            </script>
        </form>
    </main>
</div>
@endsection
