@extends('layouts.app')

@section('title', $order->order_no . ' — Nuskha Order')

@section('content')
@php
    $money = fn ($value) =>
        $order->currency . ' ' . number_format((float) $value, $decimalPlaces);

    $methodLabels = [
        'cash' => 'Cash',
        'bank' => 'Bank transfer',
        'card' => 'Card',
    ];

    $cancelled = $order->status === 'cancelled';
@endphp

<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        @if (session('success'))
            <div class="alert alert-success no-print" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger no-print" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="payment-detail-toolbar no-print">
            <a href="{{ route('orders.index') }}" class="back-link">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Back to orders
            </a>

            <div class="d-flex flex-wrap gap-2">
                @if (!$cancelled)
                    <a
                        href="{{ route('orders.edit', $order) }}"
                        class="btn btn-outline-secondary"
                    >
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                        Edit order
                    </a>
                @endif

                <button type="button" class="btn btn-primary" data-print-page>
                    <i class="bi bi-printer" aria-hidden="true"></i>
                    Print customer receipt
                </button>
            </div>
        </div>

        <article class="payment-receipt receipt-v2">
            <header class="r2-header">
                <div class="r2-brand">
                    <div class="r2-logo">
                        @if ($clinic->logo_path)
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
                        @if ($clinic->phone)
                            <p>{{ $clinic->phone }}</p>
                        @endif
                        @if ($clinic->address)
                            <p>{{ $clinic->address }}</p>
                        @endif
                    </div>
                </div>

                <div class="r2-document">
                    <span class="r2-eyebrow">Nuskha order</span>
                    <strong>{{ $order->order_no }}</strong>
                    <p>{{ $order->ordered_at->format('d M Y, h:i A') }}</p>
                    <p>{{ ucfirst($order->status) }}</p>
                </div>
            </header>

            <section class="r2-patient">
                <div>
                    <span>Customer name</span>
                    <strong>{{ $order->customer_name }}</strong>
                </div>
                <div>
                    <span>Customer phone</span>
                    <strong>{{ $order->customer_phone }}</strong>
                </div>
                <div>
                    <span>Order source</span>
                    <strong>
                        {{ $order->order_source === 'phone' ? 'Phone order' : 'Walk-in' }}
                    </strong>
                </div>
                <div>
                    <span>Fulfillment</span>
                    <strong>{{ ucfirst($order->fulfillment_method) }}</strong>
                </div>
            </section>

            <table class="r2-table">
                <thead>
                    <tr>
                        <th>Nuskha</th>
                        <th class="r2-number">Quantity</th>
                        <th class="r2-number">Unit price</th>
                        <th class="r2-number">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>
                                {{ $item->nuskha_name }}
                                @if ($item->instructions)
                                    <small class="d-block">{{ $item->instructions }}</small>
                                @endif
                            </td>
                            <td class="r2-number">
                                {{ rtrim(rtrim((string) $item->quantity, '0'), '.') }}
                                {{ $item->unit }}
                            </td>
                            <td class="r2-number">{{ $money($item->unit_price) }}</td>
                            <td class="r2-number">{{ $money($item->line_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <section class="r2-bottom">
                <div class="r2-details">
                    @if ($order->delivery_address)
                        <div>
                            <span>Delivery address</span>
                            <strong>{{ $order->delivery_address }}</strong>
                        </div>
                    @endif

                    <div>
                        <span>Payment status</span>
                        <strong>
                            {{ (float) $dueAmount <= 0
                                ? 'Paid'
                                : ((float) $paidAmount > 0 ? 'Partially paid' : 'Unpaid') }}
                        </strong>
                    </div>
                </div>

                <div class="r2-summary">
                    <div>
                        <span>Subtotal</span>
                        <strong>{{ $money($order->subtotal) }}</strong>
                    </div>
                    <div>
                        <span>Discount</span>
                        <strong>{{ $money($order->discount) }}</strong>
                    </div>
                    <div>
                        <span>Delivery fee</span>
                        <strong>{{ $money($order->delivery_fee) }}</strong>
                    </div>
                    <div class="r2-total">
                        <span>Order total</span>
                        <strong>{{ $money($order->total_amount) }}</strong>
                    </div>
                    <div>
                        <span>Total received</span>
                        <strong>{{ $money($paidAmount) }}</strong>
                    </div>
                    <div class="r2-due">
                        <span>Remaining balance</span>
                        <strong>{{ $money($dueAmount) }}</strong>
                    </div>
                </div>
            </section>

            <p class="r2-balance-note">
                Balances include all payments currently recorded against this order.
            </p>

            <div class="r2-signature">
                Authorized signature: <span></span>
            </div>

            <footer class="r2-footer">
                <p>Thank you for visiting {{ $clinic->name }}.</p>
                <small>Order receipt. An unpaid order is not proof of payment.</small>
            </footer>
        </article>

        <section
            class="payment-form-panel mt-4 no-print"
            id="order-status-panel"
        >
            @if ($cancelled)
                <h2 class="h5">Restore cancelled order</h2>
                <p>Restore this order to Confirmed to resume editing and preparation.</p>

                <form
                    method="POST"
                    action="{{ route('orders.status.update', $order) }}"
                >
                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="status" value="confirmed">

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                        Restore order
                    </button>
                </form>
            @else
                <h2 class="h5">
                    {{ $order->status === 'completed' ? 'Reopen / change status' : 'Order status' }}
                </h2>

                <form
                    method="POST"
                    action="{{ route('orders.status.update', $order) }}"
                    class="orders-filter-actions"
                >
                    @csrf
                    @method('PATCH')

                    <select name="status" class="form-select w-auto" required>
                        @foreach ($statuses as $status)
                            <option
                                value="{{ $status }}"
                                @selected($order->status === $status)
                            >
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary">
                        Update status
                    </button>
                </form>
            @endif
        </section>

        @if ((float) $dueAmount > 0 && !$cancelled)
            <section class="payment-form-panel no-print">
                <h2 class="h5 mb-3">Receive remaining payment</h2>

                <form
                    method="POST"
                    action="{{ route('orders.payments.store', $order) }}"
                >
                    @csrf

                    <div class="payment-form-grid">
                        <label class="form-field">
                            <span class="form-label">Amount *</span>
                            <input
                                type="number"
                                name="amount"
                                class="form-control"
                                value="{{ old('amount', $dueAmount) }}"
                                min="0.01"
                                max="{{ $dueAmount }}"
                                step="0.01"
                                required
                            >
                        </label>

                        <label class="form-field">
                            <span class="form-label">Payment date/time *</span>
                            <input
                                type="datetime-local"
                                name="paid_at"
                                class="form-control"
                                value="{{ old('paid_at', now('Asia/Dubai')->format('Y-m-d\TH:i')) }}"
                                min="1000-01-01T00:00"
                                max="9999-12-31T23:59"
                                required
                            >
                        </label>

                        <label class="form-field">
                            <span class="form-label">Method *</span>
                            <select name="method" class="form-select" required>
                                <option value="">Select method</option>
                                @foreach ($enabledMethods as $method)
                                    <option
                                        value="{{ $method }}"
                                        @selected(old('method') === $method)
                                    >
                                        {{ $methodLabels[$method] }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="form-field">
                            <span class="form-label">Bank/card transaction reference</span>
                            <input
                                type="text"
                                name="reference_no"
                                class="form-control"
                                value="{{ old('reference_no') }}"
                                maxlength="100"
                            >
                        </label>

                        <label class="form-field payment-field-wide">
                            <span class="form-label">Payment notes</span>
                            <input
                                type="text"
                                name="notes"
                                class="form-control"
                                value="{{ old('notes') }}"
                                maxlength="500"
                            >
                        </label>
                    </div>

                    <div class="settings-panel-actions">
                        <button type="submit" class="btn btn-primary">
                            Save payment
                        </button>
                    </div>
                </form>
            </section>
        @endif

        <section class="orders-panel no-print">
            @if ($order->notes)
                <h2 class="h5">Internal order notes</h2>
                <p class="text-break">{{ $order->notes }}</p>
            @endif

            <h2 class="h5">Payment history</h2>

            <div class="table-responsive">
                <table class="table orders-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->payments as $record)
                            <tr>
                                <td>{{ $record->paid_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    {{ $methodLabels[$record->method] ?? ucfirst($record->method) }}
                                </td>
                                <td>{{ $record->reference_no ?: '—' }}</td>
                                <td>{{ $money($record->amount) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No payments recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
@endsection