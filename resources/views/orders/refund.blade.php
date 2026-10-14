@extends('layouts.app')

@section('title', 'Refund — ' . $order->order_no)

@section('content')
<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <div class="form-page-header">
            <a href="{{ route('orders.show', $order) }}" class="back-link">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Back to order
            </a>

            <h1>Refund {{ $order->order_no }}</h1>
            <p>
                {{ $order->customer_name }}
                · Available to refund:
                {{ $order->currency }} {{ $availableAmount }}
            </p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('orders.refunds.store', $order) }}">
            @csrf

            <input
                type="hidden"
                name="request_key"
                value="{{ old('request_key', $requestKey) }}"
            >

            <section class="payment-form-panel">
                <div class="payment-form-heading">
                    <div>
                        <h2>Refund details</h2>
                        <p>Record money actually returned to the customer.</p>
                    </div>
                </div>

                <div class="payment-form-grid">
                    <label class="form-field">
                        <span class="form-label">Amount ({{ $order->currency }}) *</span>
                        <input
                            type="number"
                            name="amount"
                            class="form-control"
                            value="{{ old('amount', $availableAmount) }}"
                            min="0.01"
                            max="{{ $availableAmount }}"
                            step="0.01"
                            required
                        >
                    </label>

                    <label class="form-field">
                        <span class="form-label">Refund date/time *</span>
                        <input
                            type="datetime-local"
                            name="refunded_at"
                            class="form-control"
                            value="{{ old('refunded_at', now('Asia/Dubai')->format('Y-m-d\TH:i')) }}"
                            min="1000-01-01T00:00"
                            max="9999-12-31T23:59"
                            required
                        >
                    </label>

                    <label class="form-field">
                        <span class="form-label">Refund method *</span>
                        <select name="method" class="form-select" required>
                            <option value="">Select method</option>
                            <option value="cash" @selected(old('method') === 'cash')>Cash</option>
                            <option value="bank" @selected(old('method') === 'bank')>Bank transfer</option>
                            <option value="card" @selected(old('method') === 'card')>Card</option>
                        </select>
                    </label>

                    <label class="form-field">
                        <span class="form-label">Bank/card refund reference</span>
                        <input
                            type="text"
                            name="reference_no"
                            class="form-control"
                            value="{{ old('reference_no') }}"
                            maxlength="100"
                        >
                        <small class="text-muted">Required for bank/card refunds.</small>
                    </label>

                    <label class="form-field payment-field-wide">
                        <span class="form-label">Reason *</span>
                        <textarea
                            name="reason"
                            class="form-control"
                            rows="3"
                            maxlength="1000"
                            required
                        >{{ old('reason') }}</textarea>
                    </label>
                </div>

                <div class="form-check mt-3">
                    <input type="hidden" name="cancel_order" value="0">
                    <input
                        type="checkbox"
                        id="cancel_order"
                        name="cancel_order"
                        value="1"
                        class="form-check-input"
                        @checked((string) old('cancel_order', '1') === '1')
                    >
                    <label for="cancel_order" class="form-check-label">
                        Cancel the order after refunding the full available amount.
                    </label>
                </div>

                <p class="text-muted small mt-3 mb-0">
                    For a partial refund, uncheck cancellation.
                    The net received amount decreases; adjust the order total separately if needed.
                </p>
            </section>

            <div class="payment-form-actions">
                <a href="{{ route('orders.show', $order) }}" class="btn btn-light">
                    Back
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-arrow-return-left" aria-hidden="true"></i>
                    Record refund
                </button>
            </div>
        </form>
    </main>
</div>
@endsection