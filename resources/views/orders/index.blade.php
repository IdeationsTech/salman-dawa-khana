@extends('layouts.app')

@section('title', 'Nuskha Orders — ' . $clinic->name)

@section('content')
<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">Non-patient sales</p>
                <h1>Nuskha Orders</h1>
                <p class="page-subtitle">Manage walk-in and phone orders.</p>
            </div>
            <a href="{{ route('orders.create') }}" class="btn btn-primary">
                <i class="bi bi-bag-plus" aria-hidden="true"></i>
                New order
            </a>
        </header>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <section class="orders-panel">
            <form method="GET" action="{{ route('orders.index') }}" class="orders-filter-grid">
                <label class="form-field orders-search-field">
                    <span class="form-label">Search orders</span>
                    <input type="search" name="search" class="form-control" value="{{ request('search') }}" maxlength="160" placeholder="Customer, phone or order number">
                </label>

                <label class="form-field">
                    <span class="form-label">Status</span>
                    <select name="status" class="form-select">
                        <option value="all">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="form-field">
                    <span class="form-label">Fulfillment</span>
                    <select name="fulfillment" class="form-select">
                        <option value="all">All types</option>
                        <option value="pickup" @selected(request('fulfillment') === 'pickup')>Pickup</option>
                        <option value="delivery" @selected(request('fulfillment') === 'delivery')>Delivery</option>
                    </select>
                </label>

                <label class="form-field">
                    <span class="form-label">From date</span>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </label>

                <label class="form-field">
                    <span class="form-label">To date</span>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </label>

                <div class="orders-filter-actions">
                    <button class="btn btn-primary" type="submit">Apply</button>
                    <a href="{{ route('orders.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table orders-table align-middle">
                    <thead>
                        <tr>
                            <th>Order</th><th>Customer</th><th>Date</th>
                            <th>Fulfillment</th><th>Total</th><th>Net received</th>
                            <th>Remaining</th><th>Status</th><th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            @php
                                $paid = (float) ($order->payments_sum_amount ?? 0);
                                $due = $order->status === 'cancelled'
                                    ? 0
                                    : max(0, (float) $order->total_amount - $paid);
                            @endphp
                            <tr>
                                <td><strong>{{ $order->order_no }}</strong></td>
                                <td>
                                    <strong>{{ $order->customer_name }}</strong>
                                    <small>{{ $order->customer_phone }}</small>
                                </td>
                                <td>
                                    {{ $order->ordered_at->format('d M Y') }}
                                    <small>{{ $order->ordered_at->format('h:i A') }}</small>
                                </td>
                                <td>{{ ucfirst($order->fulfillment_method) }}</td>
                                <td>{{ $order->currency }} {{ number_format($order->total_amount, $decimalPlaces) }}</td>
                                <td>{{ $order->currency }} {{ number_format($paid, $decimalPlaces) }}</td>
                                <td>{{ $order->currency }} {{ number_format($due, $decimalPlaces) }}</td>
                                <td><span class="status-badge">{{ ucfirst($order->status) }}</span></td>
                                <td class="text-end text-nowrap">
                                    <div class="d-flex justify-content-end gap-3">
                                        <a href="{{ route('orders.show', $order) }}" class="table-action">View</a>
                                        @if ($order->status !== 'cancelled')
                                            <a href="{{ route('orders.edit', $order) }}" class="table-action">Edit</a>
                                        @else
                                            <a href="{{ route('orders.show', $order) }}#order-status-panel" class="table-action">Restore</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center py-4">No orders found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="patients-pagination">
                <span>
                    Showing {{ $orders->firstItem() ?? 0 }}
                    to {{ $orders->lastItem() ?? 0 }}
                    of {{ $orders->total() }} orders
                </span>
                {{ $orders->links('pagination::bootstrap-5') }}
            </div>
        </section>
    </main>
</div>
@endsection