@extends('layouts.app')

@section('title', 'Nuskha Orders — ' . $clinic->name)

@section('content')
<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">{{ __('Non-patient sales') }}</p>
                <h1>{{ __('Nuskha Orders') }}</h1>
                <p class="page-subtitle">{{ __('Manage walk-in and phone orders.') }}</p>
            </div>
            @clinicCan('orders.create')<a href="{{ route('orders.create') }}" class="btn btn-primary">
                <i class="bi bi-bag-plus" aria-hidden="true"></i>
                {{ __('New order') }}
            </a>@endclinicCan
        </header>

        @if (session('success'))
            <div class="alert alert-success">{{ __(session('success')) }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)<li>{{ __($error) }}</li>@endforeach
                </ul>
            </div>
        @endif

        <section class="orders-panel">
            <form method="GET" action="{{ route('orders.index') }}" class="orders-filter-grid">
                <label class="form-field orders-search-field">
                    <span class="form-label">{{ __('Search orders') }}</span>
                    <input type="search" name="search" class="form-control" value="{{ request('search') }}" maxlength="160" placeholder="{{ __('Customer, phone or order number') }}">
                </label>

                <label class="form-field">
                    <span class="form-label">{{ __('Status') }}</span>
                    <select name="status" class="form-select">
                        <option value="all">{{ __('All statuses') }}</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="form-field">
                    <span class="form-label">{{ __('Fulfillment') }}</span>
                    <select name="fulfillment" class="form-select">
                        <option value="all">{{ __('All types') }}</option>
                        <option value="pickup" @selected(request('fulfillment') === 'pickup')>{{ __('Pickup') }}</option>
                        <option value="delivery" @selected(request('fulfillment') === 'delivery')>{{ __('Delivery') }}</option>
                    </select>
                </label>

                <label class="form-field">
                    <span class="form-label">{{ __('From date') }}</span>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </label>

                <label class="form-field">
                    <span class="form-label">{{ __('To date') }}</span>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </label>

                <div class="orders-filter-actions">
                    <button class="btn btn-primary" type="submit">{{ __('Apply') }}</button>
                    <a href="{{ route('orders.index') }}" class="btn btn-light">{{ __('Reset') }}</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table orders-table align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Order') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Date') }}</th>
                            <th>{{ __('Fulfillment') }}</th><th>{{ __('Total') }}</th><th>{{ __('Net received') }}</th>
                            <th>{{ __('Remaining') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            @php
                                $paid = (float) ($order->payments_sum_amount ?? 0);
                                $statusBadgeClass = match ($order->status) {
                                    'confirmed', 'ready', 'completed', 'paid' => 'active-badge',
                                    'cancelled', 'canceled', 'refunded', 'void' => 'inactive-badge',
                                    'preparing', 'pending', 'partial' => 'pending-badge',
                                    'unpaid' => 'status-neutral-badge',
                                    default => 'status-neutral-badge',
                                };
                                $due = $order->status === 'cancelled'
                                    ? 0
                                    : max(0, (float) $order->total_amount - $paid);
                            @endphp
                            <tr>
                                <td><strong>{{ $order->order_no }}</strong></td>
                                <td>
                                    <strong>{{ $order->customer_name }}</strong>
                                    <small><bdi class="phone-number" dir="ltr">&#x2066;{{ $order->customer_phone }}&#x2069;</bdi></small>
                                </td>
                                <td>
                                    {{ $order->ordered_at->format('d M Y') }}
                                    <small>{{ $order->ordered_at->format('h:i A') }}</small>
                                </td>
                                <td>{{ ucfirst($order->fulfillment_method) }}</td>
                                <td>{{ $order->currency }} {{ number_format($order->total_amount, $decimalPlaces) }}</td>
                                <td>{{ $order->currency }} {{ number_format($paid, $decimalPlaces) }}</td>
                                <td>{{ $order->currency }} {{ number_format($due, $decimalPlaces) }}</td>
                                <td><span class="status-badge {{ $statusBadgeClass }}">{{ ucfirst($order->status) }}</span></td>
                                <td class="text-end text-nowrap">
                                    <div class="d-flex justify-content-end gap-3">
                                        <a href="{{ route('orders.show', $order) }}" class="table-action">{{ __('View') }}</a>
                                        @if ($order->status !== 'cancelled')
                                            @clinicCan('orders.edit')<a href="{{ route('orders.edit', $order) }}" class="table-action">{{ __('Edit') }}</a>@endclinicCan
                                        @else
                                            <a href="{{ route('orders.show', $order) }}#order-status-panel" class="table-action">{{ __('Restore') }}</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center py-4">{{ __('No orders found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="patients-pagination">
                <span>
                    {{ __('Showing') }} {{ $orders->firstItem() ?? 0 }}
                    {{ __('to') }} {{ $orders->lastItem() ?? 0 }}
                    {{ __('of') }} {{ $orders->total() }} {{ __('orders') }}
                </span>
                {{ $orders->links('pagination::bootstrap-5') }}
            </div>
        </section>
    </main>
</div>
@endsection