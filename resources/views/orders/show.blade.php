@extends('layouts.app')

@section('title', $order->order_no . ' — Nuskha Order')

@section('content')
@php
    $money = fn ($value) =>
        $order->currency . ' ' . number_format((float) $value, 2);

    $methodLabels = ['cash' => __('Cash'), 'bank' => __('Bank transfer'), 'card' => __('Card')];
    $cancelled = $order->status === 'cancelled';

    $canRefund = in_array(auth()->user()->role?->name, ['Owner', 'Doctor'], true)
        && !$cancelled
        && (float) $paidAmount > 0;
@endphp

<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        @if (session('success'))
            <div class="alert alert-success no-print" role="status">
                {{ __(session('success')) }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger no-print" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ __($error) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="payment-detail-toolbar no-print">
            <a href="{{ route('orders.index') }}" class="back-link">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                {{ __('Back to orders') }}
            </a>

            <div class="d-flex flex-wrap gap-2">
                @if (!$cancelled)
                    @clinicCan('orders.edit')<a href="{{ route('orders.edit', $order) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                        {{ __('Edit order') }}
                    </a>@endclinicCan
                @endif

                @if ($canRefund)
                    @clinicCan('orders.refunds.create')<a href="{{ route('orders.refunds.create', $order) }}" class="btn btn-outline-danger">
                        <i class="bi bi-arrow-return-left" aria-hidden="true"></i>
                        {{ __('Refund') }}
                    </a>@endclinicCan
                @endif

                <button type="button" class="btn btn-primary" data-print-page>
                    <i class="bi bi-printer" aria-hidden="true"></i>
                    {{ __('Print customer receipt') }}
                </button>
            </div>
        </div>

        <article class="payment-receipt receipt-v2">
            <header class="r2-header">
                <div class="r2-brand">
                    <div class="r2-logo">
                        @if ($clinic->logo_path)
                            <img src="{{ asset('storage/' . $clinic->logo_path) }}" alt="{{ $clinic->name }} logo">
                        @else
                            <i class="bi bi-hospital" aria-hidden="true"></i>
                        @endif
                    </div>
                    <div>
                        <h1>{{ $clinic->name }}</h1>
                        @if ($clinic->phone)<p>{{ $clinic->phone }}</p>@endif
                        @if ($clinic->address)<p>{{ $clinic->address }}</p>@endif
                    </div>
                </div>

                <div class="r2-document">
                    <span class="r2-eyebrow">{{ __('Nuskha order') }}</span>
                    <strong>{{ $order->order_no }}</strong>
                    <p>{{ $order->ordered_at->format('d M Y, h:i A') }}</p>
                    <p>{{ ucfirst($order->status) }}</p>
                </div>
            </header>

            <section class="r2-patient">
                <div><span>{{ __('Customer name') }}</span><strong>{{ $order->customer_name }}</strong></div>
                <div><span>{{ __('Customer phone') }}</span><strong>{{ $order->customer_phone }}</strong></div>
                <div>
                    <span>{{ __('Order source') }}</span>
                    <strong>{{ $order->order_source === 'phone' ? __('Phone order') : __('Walk-in') }}</strong>
                </div>
                <div><span>{{ __('Fulfillment') }}</span><strong>{{ ucfirst($order->fulfillment_method) }}</strong></div>
            </section>

            <table class="r2-table">
                <thead>
                    <tr>
                        <th>{{ __('Nuskha') }}</th>
                        <th class="r2-number">{{ __('Quantity') }}</th>
                        <th class="r2-number">{{ __('Unit price') }}</th>
                        <th class="r2-number">{{ __('Amount') }}</th>
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
                            <span>{{ __('Delivery address') }}</span>
                            <strong>{{ $order->delivery_address }}</strong>
                        </div>
                    @endif
                    <div>
                        <span>{{ __('Payment status') }}</span>
                        <strong>
                            {{ $cancelled ? __('Cancelled') : ((float) $dueAmount <= 0 ? __('Paid') : ((float) $paidAmount > 0 ? 'Partially paid' : __('Unpaid'))) }}
                        </strong>
                    </div>
                </div>

                <div class="r2-summary">
                    <div><span>{{ __('Subtotal') }}</span><strong>{{ $money($order->subtotal) }}</strong></div>
                    <div><span>{{ __('Discount') }}</span><strong>{{ $money($order->discount) }}</strong></div>
                    <div><span>{{ __('Delivery fee') }}</span><strong>{{ $money($order->delivery_fee) }}</strong></div>
                    <div class="r2-total"><span>{{ __('Order total') }}</span><strong>{{ $money($order->total_amount) }}</strong></div>
                    <div><span>{{ __('Payments received') }}</span><strong>{{ $money($grossAmount) }}</strong></div>
                    <div><span>{{ __('Refunded') }}</span><strong>{{ $money($refundedAmount) }}</strong></div>
                    <div><span>{{ __('Net received') }}</span><strong>{{ $money($paidAmount) }}</strong></div>
                    <div class="r2-due"><span>{{ __('Remaining balance') }}</span><strong>{{ $money($dueAmount) }}</strong></div>
                </div>
            </section>

            <div class="r2-signature">{{ __('Authorized signature:') }} <span></span></div>

            <footer class="r2-footer">
                <p>{{ __('Thank you for visiting') }} {{ $clinic->name }}.</p>
                <small>{{ __('Order receipt. An unpaid order is not proof of payment.') }}</small>
            </footer>
        </article>

        <section class="payment-form-panel mt-4 no-print" id="order-status-panel">
            @if ($cancelled)
                <h2 class="h5">{{ __('Restore cancelled order') }}</h2>

                <form method="POST" action="{{ route('orders.status.update', $order) }}">
                    @csrf
            <input type="hidden" name="request_key" value="{{ old('request_key', (string) \Illuminate\Support\Str::uuid()) }}">
                    @method('PATCH')
                    <input type="hidden" name="status" value="confirmed">
                    <button type="submit" class="btn btn-primary">{{ __('Restore order') }}</button>
                </form>
            @else
                <h2 class="h5">{{ __('Order status') }}</h2>

                <form method="POST" action="{{ route('orders.status.update', $order) }}" class="orders-filter-actions">
                    @csrf
            <input type="hidden" name="request_key" value="{{ old('request_key', (string) \Illuminate\Support\Str::uuid()) }}">
                    @method('PATCH')
                    <select name="status" class="form-select w-auto" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected($order->status === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">{{ __('Update status') }}</button>
                </form>
            @endif
        </section>

        @if ((float) $dueAmount > 0 && !$cancelled)
            <section class="payment-form-panel no-print">
                <h2 class="h5 mb-3">{{ __('Receive remaining payment') }}</h2>

                <form method="POST" action="{{ route('orders.payments.store', $order) }}">
                    @csrf
            <input type="hidden" name="request_key" value="{{ old('request_key', (string) \Illuminate\Support\Str::uuid()) }}">

                    <div class="payment-form-grid">
                        <label class="form-field">
                            <span class="form-label">{{ __('Amount') }} *</span>
                            <input type="number" name="amount" class="form-control" value="{{ old('amount', $dueAmount) }}" min="0.01" max="{{ $dueAmount }}" step="0.01" required>
                        </label>

                        <label class="form-field">
                            <span class="form-label">{{ __('Payment date/time') }} *</span>
                            <input type="datetime-local" name="paid_at" class="form-control" value="{{ old('paid_at', \App\Support\ClinicClock::now()->format('Y-m-d\TH:i')) }}" min="1000-01-01T00:00" max="9999-12-31T23:59" required>
                        </label>

                        <label class="form-field">
                            <span class="form-label">{{ __('Method') }} *</span>
                            <select name="method" class="form-select" required>
                                <option value="">{{ __('Select method') }}</option>
                                @foreach ($enabledMethods as $method)
                                    <option value="{{ $method }}" @selected(old('method') === $method)>{{ __($methodLabels[$method]) }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="form-field">
                            <span class="form-label">{{ __('Bank/card reference') }}</span>
                            <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no') }}" maxlength="100">
                        </label>

                        <label class="form-field payment-field-wide">
                            <span class="form-label">{{ __('Payment notes') }}</span>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" maxlength="500">
                        </label>
                    </div>

                    <div class="settings-panel-actions">
                        <button type="submit" class="btn btn-primary">{{ __('Save payment') }}</button>
                    </div>
                </form>
            </section>
        @endif

        <section class="orders-panel no-print">
            @if ($order->notes)
                <h2 class="h5">{{ __('Internal order notes') }}</h2>
                <p class="text-break">{{ $order->notes }}</p>
            @endif

            <h2 class="h5">{{ __('Payment and refund history') }}</h2>

            <div class="table-responsive">
                <table class="table orders-table">
                    <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Type') }}</th><th>{{ __('Method') }}</th><th>{{ __('Reference') }}</th><th>{{ __('Amount') }}</th></tr></thead>
                    <tbody>
                        @forelse ($order->payments as $record)
                            <tr>
                                <td>{{ $record->paid_at->format('d M Y, h:i A') }}</td>
                                <td>{{ (float) $record->amount < 0 ? __('Refund') : __('Payment') }}</td>
                                <td>{{ $methodLabels[$record->method] ?? $record->method }}</td>
                                <td>{{ $record->reference_no ?: '—' }}</td>
                                <td>{{ $money($record->amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">{{ __('No payments recorded.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($refunds->isNotEmpty())
                <h2 class="h5 mt-4">{{ __('Refund reasons') }}</h2>
                @foreach ($refunds as $refund)
                    <p class="text-break">
                        <strong>{{ __('Refund #') }}{{ $refund->nuskha_order_refund_id }}:</strong>
                        {{ $refund->reason }}
                    </p>
                @endforeach
            @endif
        </section>
    </main>
</div>
@endsection