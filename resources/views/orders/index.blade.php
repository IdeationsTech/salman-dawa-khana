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

                <p class="page-subtitle">
                    Manage walk-in and phone orders without a patient record.
                </p>
            </div>

            <a href="{{ route('orders.create') }}" class="btn btn-primary">
                <i class="bi bi-bag-plus app-icon" aria-hidden="true"></i>
                New order
            </a>
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="orders-panel">
            <form
                method="GET"
                action="{{ route('orders.index') }}"
                class="orders-filter-grid"
            >
                <div class="form-field orders-search-field">
                    <label for="order_search" class="form-label">
                        Search orders
                    </label>

                    <input
                        type="search"
                        id="order_search"
                        name="search"
                        class="form-control"
                        value="{{ request('search') }}"
                        maxlength="160"
                        placeholder="Customer name, phone or order number"
                    >
                </div>

                <div class="form-field">
                    <label for="order_status" class="form-label">Status</label>

                    <select
                        id="order_status"
                        name="status"
                        class="form-select"
                    >
                        <option value="all">All statuses</option>

                        @foreach ($statuses as $status)
                            <option
                                value="{{ $status }}"
                                @selected(request('status') === $status)
                            >
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="order_fulfillment" class="form-label">
                        Fulfillment
                    </label>

                    <select
                        id="order_fulfillment"
                        name="fulfillment"
                        class="form-select"
                    >
                        <option value="all">All types</option>

                        <option
                            value="pickup"
                            @selected(request('fulfillment') === 'pickup')
                        >
                            Pickup
                        </option>

                        <option
                            value="delivery"
                            @selected(request('fulfillment') === 'delivery')
                        >
                            Delivery
                        </option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="order_from" class="form-label">From date</label>

                    <input
                        type="date"
                        id="order_from"
                        name="from"
                        class="form-control"
                        value="{{ request('from') }}"
                    >
                </div>

                <div class="form-field">
                    <label for="order_to" class="form-label">To date</label>

                    <input
                        type="date"
                        id="order_to"
                        name="to"
                        class="form-control"
                        value="{{ request('to') }}"
                    >
                </div>

                <div class="orders-filter-actions">
                    <button type="submit" class="btn btn-primary">Apply</button>

                    <a href="{{ route('orders.index') }}" class="btn btn-light">
                        Reset
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table orders-table align-middle">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Fulfillment</th>
                            <th>Total</th>
                            <th>Received</th>
                            <th>Remaining</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($orders as $order)
                            @php
                                $paid = (float) ($order->payments_sum_amount ?? 0);
                                $due = max(0, (float) $order->total_amount - $paid);

                                $terminal = in_array(
                                    $order->status,
                                    ['completed', 'cancelled'],
                                    true
                                );

                                $statusClass = match ($order->status) {
                                    'completed' => 'active-badge',
                                    'cancelled' => 'due-badge',
                                    default => '',
                                };
                            @endphp

                            <tr>
                                <td>
                                    <strong>{{ $order->order_no }}</strong>

                                    <small>
                                        {{ $order->order_source === 'phone'
                                            ? 'Phone order'
                                            : 'Walk-in' }}
                                    </small>
                                </td>

                                <td>
                                    <strong>{{ $order->customer_name }}</strong>
                                    <small>{{ $order->customer_phone }}</small>
                                </td>

                                <td>
                                    {{ $order->ordered_at->format('d M Y') }}
                                    <small>
                                        {{ $order->ordered_at->format('h:i A') }}
                                    </small>
                                </td>

                                <td>{{ ucfirst($order->fulfillment_method) }}</td>

                                <td class="text-nowrap">
                                    {{ $order->currency }}
                                    {{ number_format($order->total_amount, $decimalPlaces) }}
                                </td>

                                <td class="text-nowrap">
                                    {{ $order->currency }}
                                    {{ number_format($paid, $decimalPlaces) }}
                                </td>

                                <td class="text-nowrap">
                                    {{ $order->currency }}
                                    {{ number_format($due, $decimalPlaces) }}
                                </td>

                                <td>
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>

                                <td class="text-end text-nowrap">
                                    <div class="d-flex justify-content-end align-items-center gap-3">
                                        <a
                                            href="{{ route('orders.show', $order) }}"
                                            class="table-action"
                                        >
                                            View
                                        </a>

                                        @if (!$terminal)
                                            <a
                                                href="{{ route('orders.edit', $order) }}"
                                                class="table-action"
                                            >
                                                <i
                                                    class="bi bi-pencil"
                                                    aria-hidden="true"
                                                ></i>
                                                Edit
                                            </a>
                                        @else
                                            <span class="text-muted small">
                                                Closed
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    No nuskha orders found.
                                </td>
                            </tr>
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