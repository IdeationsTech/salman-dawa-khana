@extends('platform-admin.layouts.app')

@section('title', 'Payment Details')
@section('page_title', 'Payment Details')

@section('content')
    <div class="pa-page-heading">
        <div>
            <a
                href="{{ route('platform-admin.payments.index') }}"
                class="pa-back-link"
            >
                ← Back to payments
            </a>
            <h1 class="pa-page-title">
                {{ __('Payment #') }}{{ $payment->license_payment_id }}
            </h1>
            <p class="pa-page-description">
                {{ $payment->clinic_name }} · {{ $payment->plan_name }}
            </p>
        </div>

        @if(in_array($payment->status, ['verified', 'complimentary'], true))
            <a
                href="{{ route('platform-admin.payments.receipt', $payment->license_payment_id) }}"
                class="pa-button pa-button-primary"
                target="_blank"
                rel="noopener"
            >
                {{ __('Print Receipt') }}
            </a>
        @endif
    </div>

    <div class="pa-detail-grid">
        <section class="pa-panel">
            <header class="pa-panel-header">
                <h2 class="pa-panel-title">{{ __('Payment information') }}</h2>
            </header>
            <dl class="pa-detail-list">
                <div>
                    <dt>{{ __('Amount') }}</dt>
                    <dd>
                        {{ $payment->currency }}
                        {{ number_format((float) $payment->amount, 2) }}
                    </dd>
                </div>
                <div>
                    <dt>{{ __('Status') }}</dt>
                    <dd>{{ \Illuminate\Support\Str::headline($payment->status) }}</dd>
                </div>
                <div>
                    <dt>{{ __('Method') }}</dt>
                    <dd>{{ \Illuminate\Support\Str::headline($payment->payment_method) }}</dd>
                </div>
                <div>
                    <dt>{{ __('Reference') }}</dt>
                    <dd>{{ $payment->reference_no ?? 'Not provided' }}</dd>
                </div>
                <div>
                    <dt>{{ __('Payment date') }}</dt>
                    <dd>
                        {{ \Illuminate\Support\Carbon::parse($payment->paid_at)->format('d M Y') }}
                    </dd>
                </div>
                <div>
                    <dt>{{ __('Recorded by') }}</dt>
                    <dd>{{ $payment->recorded_by_name ?? 'Not available' }}</dd>
                </div>
                <div>
                    <dt>{{ __('Verified by') }}</dt>
                    <dd>{{ $payment->verified_by_name ?? 'Not applicable' }}</dd>
                </div>
                <div>
                    <dt>{{ __('Verified on') }}</dt>
                    <dd>
                        {{ $payment->verified_at
                            ? \Illuminate\Support\Carbon::parse($payment->verified_at)->format('d M Y, h:i A')
                            : 'Not applicable' }}
                    </dd>
                </div>
            </dl>
        </section>

        <section class="pa-panel">
            <header class="pa-panel-header">
                <h2 class="pa-panel-title">{{ __('Clinic and subscription') }}</h2>
            </header>
            <dl class="pa-detail-list">
                <div>
                    <dt>{{ __('Clinic') }}</dt>
                    <dd>
                        <a
                            href="{{ route('platform-admin.clinics.show', $payment->clinic_id) }}"
                            class="pa-link"
                        >
                            {{ $payment->clinic_name }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt>{{ __('Customer') }}</dt>
                    <dd>{{ $payment->contact_name ?? 'Not provided' }}</dd>
                </div>
                <div>
                    <dt>{{ __('Email') }}</dt>
                    <dd>{{ $payment->customer_email ?? 'Not provided' }}</dd>
                </div>
                <div>
                    <dt>{{ __('License') }}</dt>
                    <dd>#{{ $payment->clinic_license_id }}</dd>
                </div>
                <div>
                    <dt>{{ __('Plan') }}</dt>
                    <dd>{{ $payment->plan_name }}</dd>
                </div>
                <div>
                    <dt>{{ __('Starts') }}</dt>
                    <dd>
                        {{ \Illuminate\Support\Carbon::parse($payment->license_starts_at)->format('d M Y') }}
                    </dd>
                </div>
                <div>
                    <dt>{{ __('Expires') }}</dt>
                    <dd>
                        {{ $payment->license_ends_at
                            ? \Illuminate\Support\Carbon::parse($payment->license_ends_at)->format('d M Y')
                            : 'No expiry' }}
                    </dd>
                </div>
            </dl>
        </section>
    </div>

    <section class="pa-panel">
        <header class="pa-panel-header">
            <h2 class="pa-panel-title">{{ __('Notes') }}</h2>
        </header>
        <div class="pa-panel-body pa-payment-notes">
            {{ $payment->notes ?? 'No notes recorded.' }}
        </div>
    </section>
@endsection