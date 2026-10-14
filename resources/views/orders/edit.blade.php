@extends('layouts.app')

@section('title', 'Edit Order — ' . $order->order_no)

@section('content')
@php
    $orderItems = old(
        'items',
        $order->items->map(fn ($item) => [
            'item_id' => $item->nuskha_order_item_id,
            'quantity' => $item->quantity,
            'unit' => $item->unit,
            'unit_price' => $item->unit_price,
            'instructions' => $item->instructions,
        ])->values()->all()
    );

    $itemNames = $order->items->pluck(
        'nuskha_name',
        'nuskha_order_item_id'
    );
@endphp

<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <div class="form-page-header">
            <a href="{{ route('orders.show', $order) }}" class="back-link">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Back to order
            </a>

            <h1>Edit order {{ $order->order_no }}</h1>
            <p>
                Currency: {{ $order->currency }}.
                Already received:
                {{ $order->currency }} {{ number_format((float) $paidAmount, 2) }}.
            </p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Please correct the following:</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('orders.update', $order) }}">
            @csrf
            @method('PUT')

            <section class="payment-form-panel">
                <div class="payment-form-heading">
                    <div class="form-section-number">01</div>
                    <div>
                        <h2>Customer details</h2>
                        <p>Update contact and fulfillment information.</p>
                    </div>
                </div>

                <div class="payment-form-grid">
                    <div class="form-field">
                        <label for="customer_name" class="form-label">
                            Customer name *
                        </label>

                        <input
                            type="text"
                            id="customer_name"
                            name="customer_name"
                            class="form-control"
                            value="{{ old('customer_name', $order->customer_name) }}"
                            maxlength="160"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label for="customer_phone" class="form-label">
                            Customer phone *
                        </label>

                        <x-phone-input
                            name="customer_phone"
                            id="customer_phone"
                            :value="$order->customer_phone"
                            :required="true"
                        />
                    </div>

                    <div class="form-field">
                        <label for="ordered_at" class="form-label">
                            Order date/time *
                        </label>

                        <input
                            type="datetime-local"
                            id="ordered_at"
                            name="ordered_at"
                            class="form-control"
                            value="{{ old('ordered_at', $order->ordered_at->format('Y-m-d\TH:i')) }}"
                            min="1000-01-01T00:00"
                            max="9999-12-31T23:59"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label for="order_source" class="form-label">
                            Order source *
                        </label>

                        <select
                            id="order_source"
                            name="order_source"
                            class="form-select"
                            required
                        >
                            <option
                                value="walk_in"
                                @selected(old('order_source', $order->order_source) === 'walk_in')
                            >
                                Walk-in
                            </option>

                            <option
                                value="phone"
                                @selected(old('order_source', $order->order_source) === 'phone')
                            >
                                Phone order
                            </option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="fulfillment_method" class="form-label">
                            Fulfillment *
                        </label>

                        <select
                            id="fulfillment_method"
                            name="fulfillment_method"
                            class="form-select"
                            required
                        >
                            <option
                                value="pickup"
                                @selected(old('fulfillment_method', $order->fulfillment_method) === 'pickup')
                            >
                                Pickup
                            </option>

                            <option
                                value="delivery"
                                @selected(old('fulfillment_method', $order->fulfillment_method) === 'delivery')
                            >
                                Delivery
                            </option>
                        </select>
                    </div>

                    <div class="form-field payment-field-wide">
                        <label for="delivery_address" class="form-label">
                            Delivery address — required for delivery
                        </label>

                        <textarea
                            id="delivery_address"
                            name="delivery_address"
                            class="form-control"
                            rows="2"
                            maxlength="500"
                        >{{ old('delivery_address', $order->delivery_address) }}</textarea>
                    </div>
                </div>
            </section>

            <section class="payment-form-panel">
                <div class="payment-form-heading">
                    <div class="form-section-number">02</div>
                    <div>
                        <h2>Ordered nuskhas</h2>
                        <p>Update quantity, unit price and packing instructions.</p>
                    </div>
                </div>

                @foreach ($orderItems as $index => $item)
                    <div class="order-item-row">
                        <div class="order-item-heading">
                            <strong>
                                {{ $itemNames->get($item['item_id'] ?? null, 'Nuskha') }}
                            </strong>
                        </div>

                        <input
                            type="hidden"
                            name="items[{{ $index }}][item_id]"
                            value="{{ $item['item_id'] ?? '' }}"
                        >

                        <div class="payment-form-grid">
                            <label class="form-field">
                                <span class="form-label">Quantity *</span>

                                <input
                                    type="number"
                                    name="items[{{ $index }}][quantity]"
                                    class="form-control"
                                    value="{{ $item['quantity'] ?? '' }}"
                                    min="0.001"
                                    max="1000"
                                    step="0.001"
                                    required
                                >
                            </label>

                            <label class="form-field">
                                <span class="form-label">Unit *</span>

                                <select
                                    name="items[{{ $index }}][unit]"
                                    class="form-select"
                                    required
                                >
                                    @foreach ($units as $unit)
                                        <option
                                            value="{{ $unit }}"
                                            @selected(($item['unit'] ?? '') === $unit)
                                        >
                                            {{ $unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="form-field">
                                <span class="form-label">
                                    Price per selected unit ({{ $order->currency }}) *
                                </span>

                                <input
                                    type="number"
                                    name="items[{{ $index }}][unit_price]"
                                    class="form-control"
                                    value="{{ $item['unit_price'] ?? '' }}"
                                    min="0"
                                    max="9999999999.99"
                                    step="0.01"
                                    required
                                >
                            </label>

                            <label class="form-field">
                                <span class="form-label">Packing/instructions</span>

                                <input
                                    type="text"
                                    name="items[{{ $index }}][instructions]"
                                    class="form-control"
                                    value="{{ $item['instructions'] ?? '' }}"
                                    maxlength="500"
                                >
                            </label>
                        </div>
                    </div>
                @endforeach
            </section>

            <section class="payment-form-panel">
                <div class="payment-form-heading">
                    <div class="form-section-number">03</div>
                    <div>
                        <h2>Discount and delivery</h2>
                        <p>The revised total is calculated when you save.</p>
                    </div>
                </div>

                <div class="payment-form-grid">
                    <label class="form-field">
                        <span class="form-label">
                            Discount ({{ $order->currency }}) *
                        </span>

                        <input
                            type="number"
                            name="discount"
                            class="form-control"
                            value="{{ old('discount', $order->discount) }}"
                            min="0"
                            max="9999999999.99"
                            step="0.01"
                            required
                        >
                    </label>

                    <label class="form-field">
                        <span class="form-label">
                            Delivery fee ({{ $order->currency }}) *
                        </span>

                        <input
                            type="number"
                            name="delivery_fee"
                            class="form-control"
                            value="{{ old('delivery_fee', $order->delivery_fee) }}"
                            min="0"
                            max="9999999999.99"
                            step="0.01"
                            required
                        >

                        <small class="text-muted">
                            Pickup orders save with zero delivery fee.
                        </small>
                    </label>

                    <label class="form-field payment-field-wide">
                        <span class="form-label">Internal order notes</span>

                        <textarea
                            name="notes"
                            class="form-control"
                            rows="3"
                            maxlength="5000"
                        >{{ old('notes', $order->notes) }}</textarea>
                    </label>
                </div>
            </section>

            <div class="payment-form-actions">
                <a
                    href="{{ route('orders.show', $order) }}"
                    class="btn btn-light"
                >
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle" aria-hidden="true"></i>
                    Update order
                </button>
            </div>
        </form>
    </main>
</div>
@endsection