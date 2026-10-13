@extends('layouts.app')

@section('title', $payment ? 'Payment Receipt' : 'Patient Bill')

@section('content')
@php
    $preferences = \App\Models\Setting::query()
        ->where('clinic_id', $clinic->clinic_id)
        ->whereIn('setting_key', [
            'show_previous_due',
            'decimal_places',
        ])
        ->pluck('setting_value', 'setting_key');

    $showPreviousDue =
        (string) $preferences->get('show_previous_due', '1') === '1';

    $decimals =
        (string) $preferences->get('decimal_places', '2') === '0'
            ? 0
            : 2;

    $money = fn ($value) =>
        $currency . ' ' . number_format((float) $value, $decimals);

    $documentNumber = $payment
        ? 'PAY-' . $payment->payment_id
        : $bill->bill_no;

    $documentDate = $payment
        ? $payment->payment_date
        : $bill->bill_date;

    $method = strtolower(trim($payment?->method ?? ''));

    $methodLabel = in_array(
        $method,
        ['bank', 'bank transfer', 'bank_transfer'],
        true
    ) ? 'Bank transfer' : ucfirst($method);

    $billStatus = $bill
        ? (in_array($bill->status, ['cancelled', 'void'], true)
            ? ucfirst($bill->status)
            : ((float) $billDue <= 0
                ? 'Paid'
                : ((float) $billPaid > 0 ? 'Partially paid' : 'Unpaid')))
        : null;

    $otherBillsDue = max(
        0,
        (float) $patientOutstanding - (float) ($billDue ?? 0)
    );

    $dob = $patient->date_of_birth;

    $ageAtDocument = $dob && $dob->lte($documentDate)
        ? (int) floor($dob->diffInYears($documentDate))
        : null;
@endphp

<div class="app-shell">
    @include('payments._sidebar')

    <main class="dashboard-main">
        @if(session('success'))
            <div class="alert alert-success no-print" role="status">
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

        {{-- Receipt — Screen Actions --}}
        <div class="payment-detail-toolbar no-print">
            <a href="{{ route('payments.index') }}" class="back-link">
                <i class="bi bi-arrow-left app-icon" aria-hidden="true"></i>
                Back to payments
            </a>

            <div class="payment-toolbar-actions flex-wrap">
                @if($payment)
                    <a
                        href="{{ route('payments.edit', $payment) }}"
                        class="btn btn-outline-secondary"
                    >
                        <i class="bi bi-pencil-square app-icon" aria-hidden="true"></i>
                        Edit payment
                    </a>
                @endif

                @if(
                    $bill &&
                    (float) $billDue > 0 &&
                    !in_array($bill->status, ['cancelled', 'void'], true)
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
                    <i class="bi bi-printer app-icon" aria-hidden="true"></i>
                    Print / Save PDF
                </button>
            </div>
        </div>

        <article class="payment-receipt receipt-v2">

            {{-- Receipt — Clinic and Document Header --}}
            <header class="r2-header">
                <div class="r2-brand">
                    <div class="r2-logo">
                        @if($clinic->logo_path)
                            <img
                                src="{{ asset('storage/' . $clinic->logo_path) }}"
                                alt="{{ $clinic->name }} logo"
                            >
                        @else
                            <i class="bi bi-hospital" aria-hidden="true"></i>
                        @endif
                    </div>

                    <div>
                        <h1>{{ $clinic->name }}</h1>

                        @if($clinic->address)
                            <p>{{ $clinic->address }}</p>
                        @endif

                        @if($clinic->phone)
                            <p>{{ $clinic->phone }}</p>
                        @endif
                    </div>
                </div>

                <div class="r2-document">
                    <span class="r2-eyebrow">
                        {{ $payment ? 'Payment receipt' : 'Patient bill' }}
                    </span>

                    <strong>{{ $documentNumber }}</strong>

                    <p>{{ $documentDate->format('d M Y') }}</p>
                    <p>{{ $documentDate->format('h:i A') }}</p>
                </div>
            </header>

            {{-- Receipt — Patient Details --}}
            <section class="r2-patient">
                <div>
                    <span>Patient name</span>
                    <strong>{{ $patient->full_name }}</strong>
                </div>

                <div>
                    <span>Patient code</span>
                    <strong>{{ $patient->patient_code }}</strong>
                </div>

                <div>
                    <span>Date of birth / Age at receipt date</span>

                    <strong>
                        @if($dob)
                            {{ $dob->format('d M Y') }}
                            @if($ageAtDocument !== null)
                                / {{ $ageAtDocument }} years
                            @endif
                        @else
                            —
                        @endif
                    </strong>
                </div>

                <div>
                    <span>Phone number</span>
                    <strong>{{ $patient->phone ?: '—' }}</strong>
                </div>
            </section>

            {{-- Receipt — Charges --}}
            @if($bill)
                <table class="r2-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="r2-number">Amount ({{ $currency }})</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($bill->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>

                                <td class="r2-number">
                                    {{ number_format($item->line_total, $decimals) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td>Bill charges</td>
                                <td class="r2-number">
                                    {{ number_format($bill->subtotal, $decimals) }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif

            {{-- Receipt — Payment Details and Balance Summary --}}
            <section class="r2-bottom">
                <div class="r2-details">
                    <div>
                        <span>Bill number</span>
                        <strong>
                            {{ $bill?->bill_no ?? 'Unallocated payment' }}
                        </strong>
                    </div>

                    @if($payment)
                        <div>
                            <span>Payment method</span>
                            <strong>{{ $methodLabel }}</strong>
                        </div>

                        <div>
                            <span>Received by</span>
                            <strong>{{ $payment->receivedBy?->name ?? '—' }}</strong>
                        </div>

                        @if($payment->reference_no)
                            <div>
                                <span>Transaction reference</span>
                                <strong>{{ $payment->reference_no }}</strong>
                            </div>
                        @endif
                    @endif

                    @if($bill)
                        <div>
                            <span>Current bill status</span>
                            <strong>{{ $billStatus }}</strong>
                        </div>
                    @endif
                </div>

                <div class="r2-summary">
                    @if($bill)
                        <div>
                            <span>Subtotal</span>
                            <strong>{{ $money($bill->subtotal) }}</strong>
                        </div>

                        <div>
                            <span>Discount</span>
                            <strong>{{ $money($bill->discount) }}</strong>
                        </div>

                        <div class="r2-total">
                            <span>Bill total</span>
                            <strong>{{ $money($bill->total_amount) }}</strong>
                        </div>
                    @endif

                    @if($payment)
                        <div>
                            <span>This payment</span>
                            <strong>{{ $money($payment->amount) }}</strong>
                        </div>
                    @endif

                    @if($bill)
                        <div>
                            <span>Total paid on this bill</span>
                            <strong>{{ $money($billPaid) }}</strong>
                        </div>

                        <div class="r2-due">
                            <span>This bill remaining</span>
                            <strong>{{ $money($billDue) }}</strong>
                        </div>
                    @endif

                    @if($showPreviousDue)
                        @if($bill)
                            <div>
                                <span>Other bills outstanding</span>
                                <strong>{{ $money($otherBillsDue) }}</strong>
                            </div>
                        @endif

                        <div class="r2-total">
                            <span>Patient total outstanding</span>
                            <strong>{{ $money($patientOutstanding) }}</strong>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Receipt — Notes --}}
            @if($payment?->notes)
                <div class="r2-notes">
                    <span>Payment notes</span>
                    <p>{{ $payment->notes }}</p>
                </div>
            @endif

            <p class="r2-balance-note">
                Balances shown are current when this receipt is generated,
                including subsequent payments.
                Unallocated payments are not deducted from bill balances.
            </p>

            <div class="r2-signature">
                Authorized signature:
                <span></span>
            </div>

            <footer class="r2-footer">
                <p>Thank you for visiting {{ $clinic->name }}.</p>

                <small>
                    {{ $payment
                        ? 'Computer-generated payment receipt.'
                        : 'Computer-generated bill. This document alone is not proof of payment.' }}
                </small>
            </footer>

            {{-- Receipt — Payment History, Screen Only --}}
            @if($bill && $billPayments->isNotEmpty())
                <section class="r2-history no-print">
                    <h2>Payments against this bill</h2>

                    <table class="r2-table">
                        <thead>
                            <tr>
                                <th>Receipt</th>
                                <th>Date</th>
                                <th class="r2-number">Amount</th>
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

                                    <td class="r2-number">
                                        {{ $money($record->amount) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
            @endif
        </article>
    </main>
</div>
@endsection