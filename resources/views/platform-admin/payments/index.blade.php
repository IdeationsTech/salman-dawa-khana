@extends('platform-admin.layouts.app')

@section('title', 'License Payments')
@section('page_title', 'License Payments')

@section('content')
    <div class="pa-page-heading">
        <div>
            <h1 class="pa-page-title">
                License payments
            </h1>

            <p class="pa-page-description">
                Manual payments recorded for clinic subscriptions.
            </p>
        </div>
    </div>

    <section class="pa-panel">
        <header class="pa-panel-header">
            <div>
                <h2 class="pa-panel-title">
                    Payment history
                </h2>

                <p class="pa-panel-description">
                    {{ $payments->total() }} payment records
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
                        <label for="payment-search">
                            Search
                        </label>

                        <input
                            id="payment-search"
                            type="search"
                            name="search"
                            class="pa-input"
                            placeholder="Clinic, email, reference or payment ID"
                            value="{{ $filters['search'] }}"
                        >
                    </div>

                    <div class="pa-field">
                        <label for="payment-status">
                            Status
                        </label>

                        <select
                            id="payment-status"
                            name="status"
                            class="pa-input"
                        >
                            <option
                                value="all"
                                @selected($filters['status'] === 'all')
                            >
                                All statuses
                            </option>

                            <option
                                value="verified"
                                @selected($filters['status'] === 'verified')
                            >
                                Verified
                            </option>

                            <option
                                value="pending_verification"
                                @selected($filters['status'] === 'pending_verification')
                            >
                                Pending verification
                            </option>
                        </select>
                    </div>

                    <div class="pa-field">
                        <label for="payment-method-filter">
                            Method
                        </label>

                        <select
                            id="payment-method-filter"
                            name="method"
                            class="pa-input"
                        >
                            <option
                                value="all"
                                @selected($filters['method'] === 'all')
                            >
                                All methods
                            </option>

                            <option
                                value="cash"
                                @selected($filters['method'] === 'cash')
                            >
                                Cash
                            </option>

                            <option
                                value="bank_transfer"
                                @selected($filters['method'] === 'bank_transfer')
                            >
                                Bank transfer
                            </option>
                        </select>
                    </div>

                    <div class="pa-field">
                        <label for="payment-from">
                            From
                        </label>

                        <input
                            id="payment-from"
                            type="date"
                            name="from"
                            class="pa-input"
                            value="{{ $filters['from'] }}"
                        >
                    </div>

                    <div class="pa-field">
                        <label for="payment-to">
                            To
                        </label>

                        <input
                            id="payment-to"
                            type="date"
                            name="to"
                            class="pa-input"
                            value="{{ $filters['to'] }}"
                        >
                    </div>
                </div>

                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button
                        type="submit"
                        class="pa-button pa-button-primary"
                    >
                        Apply filters
                    </button>

                    <a
                        href="{{ route('platform-admin.payments.index') }}"
                        class="pa-button"
                    >
                        Clear
                    </a>
                </div>
            </form>
        </div>

        @if($payments->isEmpty())
            <div class="pa-empty">
                <p class="pa-empty-title">
                    No payments found
                </p>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="pa-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Payment</th>
                            <th>Clinic</th>
                            <th>Plan</th>
                            <th>Method</th>
                            <th>Paid on</th>
                            <th>Status</th>
                            <th>Amount</th>
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
                                        License #{{ $payment->clinic_license_id }}
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

                                <td>
                                    {{ \Illuminate\Support\Carbon::parse($payment->paid_at)->format('d M Y') }}
                                </td>

                                <td>
                                    <span class="pa-badge">
                                        {{ \Illuminate\Support\Str::headline($payment->status) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $payment->currency }}
                                    {{ number_format((float) $payment->amount, 2) }}
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