@extends('platform-admin.layouts.app')

@section('title', 'License Payments')
@section('page_title', 'License Payments')

@section('content')
    <div class="pa-page-heading">
        <div>
            <h1 class="pa-page-title">{{ __('License payments') }}</h1>
            <p class="pa-page-description">
                {{ __('Manual payments recorded for clinic subscriptions.') }}
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="pa-notice pa-notice-error" role="alert">
            <strong>{{ __('Please correct the following:') }}</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ __($error) }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="pa-panel">
        <header class="pa-panel-header">
            <div>
                <h2 class="pa-panel-title">{{ __('Payment history') }}</h2>
                <p class="pa-panel-description">
                    {{ $payments->total() }} {{ __('payment records') }}
                </p>
            </div>
        </header>

        <div class="pa-panel-body">
            <form
                method="GET"
                action="{{ route('platform-admin.payments.index') }}"
                class="pa-form"
            >
                <div class="pa-form-grid">
                    <div class="pa-field">
                        <label for="payment-search">{{ __('Search') }}</label>
                        <input
                            id="payment-search"
                            type="search"
                            name="search"
                            class="pa-input"
                            maxlength="160"
                            placeholder="{{ __('Clinic, email, reference or payment ID') }}"
                            value="{{ $filters['search'] }}"
                        >
                    </div>

                    <div class="pa-field">
                        <label for="payment-status">{{ __('Status') }}</label>
                        <select id="payment-status" name="status" class="pa-input">
                            @foreach([
                                'all' => 'All statuses',
                                'verified' => 'Verified',
                                'pending_verification' => 'Pending verification',
                                'complimentary' => 'Complimentary',
                            ] as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected($filters['status'] === $value)
                                >
                                    {{ __($label) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pa-field">
                        <label for="payment-method-filter">{{ __('Method') }}</label>
                        <select
                            id="payment-method-filter"
                            name="method"
                            class="pa-input"
                        >
                            @foreach([
                                'all' => 'All methods',
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank transfer',
                                'complimentary' => 'Complimentary',
                            ] as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected($filters['method'] === $value)
                                >
                                    {{ __($label) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pa-field">
                        <label for="payment-from">{{ __('From') }}</label>
                        <input
                            id="payment-from"
                            type="date"
                            name="from"
                            class="pa-input"
                            value="{{ $filters['from'] }}"
                        >
                    </div>

                    <div class="pa-field">
                        <label for="payment-to">{{ __('To') }}</label>
                        <input
                            id="payment-to"
                            type="date"
                            name="to"
                            class="pa-input"
                            value="{{ $filters['to'] }}"
                        >
                    </div>
                </div>

                <div class="pa-payment-actions">
                    <button type="submit" class="pa-button pa-button-primary">
                        {{ __('Apply filters') }}
                    </button>
                    <a
                        href="{{ route('platform-admin.payments.index') }}"
                        class="pa-button"
                    >
                        {{ __('Clear') }}
                    </a>
                </div>
            </form>
        </div>

        @if($payments->isEmpty())
            <div class="pa-empty">
                <p class="pa-empty-title">{{ __('No payments found') }}</p>
            </div>
        @else
            <div class="pa-table-wrap">
                <table class="pa-table">
                    <thead>
                        <tr>
                            <th>{{ __('Payment') }}</th>
                            <th>{{ __('Clinic') }}</th>
                            <th>{{ __('Plan') }}</th>
                            <th>{{ __('Method') }}</th>
                            <th>{{ __('Paid on') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="pa-amount">{{ __('Amount') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr>
                                <td>
                                    <span class="pa-cell-title">
                                        #{{ $payment->license_payment_id }}
                                    </span>
                                    <span class="pa-cell-subtitle">
                                        {{ __('License #') }}{{ $payment->clinic_license_id }}
                                    </span>
                                </td>
                                <td>
                                    <a
                                        href="{{ route('platform-admin.clinics.show', $payment->clinic_id) }}"
                                        class="pa-cell-title"
                                    >
                                        {{ $payment->clinic_name }}
                                    </a>
                                    <span class="pa-cell-subtitle">
                                        {{ $payment->customer_email ?? 'No customer email' }}
                                    </span>
                                </td>
                                <td>{{ $payment->plan_name }}</td>
                                <td>
                                    {{ \Illuminate\Support\Str::headline($payment->payment_method) }}
                                </td>
                                <td class="pa-nowrap">
                                    {{ \Illuminate\Support\Carbon::parse($payment->paid_at)->format('d M Y') }}
                                </td>
                                <td>
                                    <span class="pa-badge">
                                        {{ \Illuminate\Support\Str::headline($payment->status) }}
                                    </span>
                                </td>
                                <td class="pa-amount">
                                    {{ $payment->currency }}
                                    {{ number_format((float) $payment->amount, 2) }}
                                </td>
                                <td>
                                    <a
                                        href="{{ route('platform-admin.payments.show', $payment->license_payment_id) }}"
                                        class="pa-link"
                                    >
                                        {{ __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="pa-panel-body">
                    {{ $payments->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @endif
    </section>
@endsection