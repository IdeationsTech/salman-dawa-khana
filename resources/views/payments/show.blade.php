@extends('layouts.app')

@section('title', $payment ? 'Payment Receipt' : 'Patient Bill')

@section('content')
@php
    $documentNumber = $payment
        ? 'PAY-' . $payment->payment_id
        : $bill->bill_no;

    $documentDate = $payment
        ? $payment->payment_date
        : $bill->bill_date;

    $methodValue = $payment
        ? strtolower(trim($payment->method))
        : '';

    $methodLabel = in_array(
        $methodValue,
        ['bank', 'bank transfer', 'bank_transfer'],
        true
    ) ? 'Bank transfer' : ucfirst($methodValue);

    $billStatus = $bill
        ? (in_array($bill->status, ['cancelled', 'void'], true)
            ? ucfirst($bill->status)
            : ((float) $billDue <= 0
                ? 'Paid'
                : ((float) $billPaid > 0 ? 'Partially paid' : 'Unpaid')))
        : null;
@endphp

<div class="app-shell">
    @include('payments._sidebar')

    <main class="dashboard-main">
        @if(session('success'))
            <div class="alert alert-success no-print" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger no-print" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="payment-detail-toolbar no-print">
            <a href="{{ route('payments.index') }}" class="back-link">
                ← Back to payments
            </a>

            <div class="payment-toolbar-actions flex-wrap">
                @if($payment)
                    <a
                        href="{{ route('payments.edit', $payment) }}"
                        class="btn btn-outline-secondary"
                    >
                        Edit payment
                    </a>
                @endif

                @if(
                    $bill
                    && (float) $billDue > 0
                    && !in_array($bill->status, ['cancelled', 'void'], true)
                )
                    <a
                        href="{{ route('payments.create', [
                            'mode' => 'existing_bill',
                            'patient_id' => $patient->patient_id,
                            'bill_id' => $bill->bill_id,
                        ]) }}"
                        class="btn btn-outline-secondary"
                    >
                        Receive remaining payment
                    </a>
                @endif

                <button
                    type="button"
                    class="btn btn-primary"
                    data-print-page
                >
                    Print / Save PDF
                </button>
            </div>
        </div>

        <article class="payment-receipt">
            {{-- BILL / PAYMENT — Header --}}
            <header class="receipt-header">
                <div class="receipt-brand">
                    <div class="receipt-brand-mark">+</div>

                    <div>
                        <h1>{{ $clinic->name }}</h1>

                        @if($clinic->phone)
                            <p>{{ $clinic->phone }}</p>
                        @endif

                        @if($clinic->address)
                            <p>{{ $clinic->address }}</p>
                        @endif
                    </div>
                </div>

                <div class="receipt-meta">
                    <span>
                        {{ $payment ? 'PAYMENT RECEIPT' : 'PATIENT BILL' }}
                    </span>

                    <strong>{{ $documentNumber }}</strong>

                    <small>{{ $documentDate->format('d F Y') }}</small>

                    <small>
                        {{ $documentDate->format('h:i A') }} — UAE time
                    </small>
                </div>
            </header>

            <div class="receipt-divider"></div>

            <div class="receipt-status-row">
                <div>
                    <span>{{ $payment ? 'Payment' : 'Current bill status' }}</span>

                    <strong>
                        {{ $payment ? 'Received' : $billStatus }}
                    </strong>
                </div>

                <div>
                    <span>{{ $payment ? 'Payment method' : 'Bill date' }}</span>

                    <strong>
                        {{ $payment
                            ? $methodLabel
                            : $bill->bill_date->format('d M Y') }}
                    </strong>
                </div>

                <div>
                    <span>{{ $payment ? 'Received by' : 'Bill number' }}</span>

                    <strong>
                        {{ $payment
                            ? ($payment->receivedBy?->name ?? '—')
                            : $bill->bill_no }}
                    </strong>
                </div>
            </div>

            {{-- BILL / PAYMENT — Patient --}}
            <section class="receipt-section">
                <div class="receipt-section-heading">
                    <h2>Patient information</h2>
                </div>

                <div class="receipt-info-grid">
                    <div>
                        <span>Patient name</span>
                        <strong>{{ $patient->full_name }}</strong>
                    </div>

                    <div>
                        <span>Patient code</span>
                        <strong>{{ $patient->patient_code }}</strong>
                    </div>

                    <div>
                        <span>Phone number</span>
                        <strong>{{ $patient->phone ?: '—' }}</strong>
                    </div>

                    <div>
                        <span>Related bill</span>
                        <strong>
                            {{ $bill?->bill_no ?? 'Unallocated payment' }}
                        </strong>
                    </div>
                </div>
            </section>

            {{-- BILL — Charges --}}
            @if($bill)
                <section class="receipt-section">
                    <div class="receipt-section-heading">
                        <h2>Bill charges</h2>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($bill->items as $item)
                                    <tr>
                                        <td>{{ $item->description }}</td>

                                        <td class="text-end">
                                            {{ $currency }}
                                            {{ number_format($item->line_total, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="receipt-breakdown">
                        <div>
                            <span>Subtotal</span>
                            <strong>
                                {{ $currency }}
                                {{ number_format($bill->subtotal, 2) }}
                            </strong>
                        </div>

                        <div>
                            <span>Discount</span>
                            <strong>
                                {{ $currency }}
                                {{ number_format($bill->discount, 2) }}
                            </strong>
                        </div>

                        <div class="receipt-total-row">
                            <span>Bill total</span>
                            <strong>
                                {{ $currency }}
                                {{ number_format($bill->total_amount, 2) }}
                            </strong>
                        </div>
                    </div>
                </section>
            @endif

            {{-- PAYMENT — This Receipt --}}
            @if($payment)
                <section class="receipt-section">
                    <div class="receipt-section-heading">
                        <h2>This payment</h2>
                    </div>

                    <div class="receipt-breakdown">
                        <div class="receipt-paid-row">
                            <span>Amount received</span>
                            <strong>
                                {{ $currency }}
                                {{ number_format($payment->amount, 2) }}
                            </strong>
                        </div>

                        <div>
                            <span>Transaction reference</span>
                            <strong>{{ $payment->reference_no ?: '—' }}</strong>
                        </div>
                    </div>

                    @if(!$bill)
                        <p class="text-muted small mt-2">
                            This older payment has not been allocated to a bill.
                        </p>
                    @endif
                </section>
            @endif

            {{-- BILL / PAYMENT — Current Balances --}}
            <section class="receipt-section">
                <div class="receipt-section-heading">
                    <h2>Current balances</h2>

                    <p class="text-muted small">
                        These figures include subsequent payments and show
                        the current balance, not a historical snapshot.
                    </p>
                </div>

                <div class="receipt-breakdown">
                    @if($bill)
                        <div>
                            <span>Total received against this bill</span>
                            <strong>
                                {{ $currency }}
                                {{ number_format($billPaid, 2) }}
                            </strong>
                        </div>

                        <div class="receipt-balance-row">
                            <span>This bill's remaining balance</span>
                            <strong>
                                {{ $currency }}
                                {{ number_format($billDue, 2) }}
                            </strong>
                        </div>

                        <div>
                            <span>Current bill status</span>
                            <strong>{{ $billStatus }}</strong>
                        </div>
                    @endif

                    <div class="receipt-total-row">
                        <span>All outstanding bills for this patient</span>
                        <strong>
                            {{ $currency }}
                            {{ number_format($patientOutstanding, 2) }}
                        </strong>
                    </div>
                </div>

                <small class="text-muted d-block mt-2">
                    Unallocated payments are not deducted from bill balances.
                </small>
            </section>

            {{-- BILL — Payment History --}}
            @if($bill && $billPayments->isNotEmpty())
                <section class="receipt-section">
                    <div class="receipt-section-heading">
                        <h2>Payments against this bill</h2>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Receipt</th>
                                    <th>Date</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($billPayments as $record)
                                    <tr>
                                        <td>
                                            <a href="{{ route('payments.show', $record) }}">
                                                PAY-{{ $record->payment_id }}
                                            </a>
                                        </td>

                                        <td>
                                            {{ $record->payment_date->format('d M Y') }}
                                        </td>

                                        <td class="text-end">
                                            {{ $currency }}
                                            {{ number_format($record->amount, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            @if($payment?->notes)
                <section class="receipt-notes">
                    <span>Payment notes</span>
                    <p>{{ $payment->notes }}</p>
                </section>
            @endif

            <footer class="receipt-footer">
                <p>Thank you for visiting {{ $clinic->name }}.</p>

                <small>
                    {{ $payment
                        ? 'Computer-generated payment receipt.'
                        : 'Computer-generated bill. This document alone is not proof of payment.' }}
                </small>
            </footer>
        </article>
    </main>
</div>
@endsection