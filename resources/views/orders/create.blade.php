@extends('layouts.app')

@section('title', 'New Nuskha Order — ' . $clinic->name)

@section('content')
@php
    $orderItems = old('items', [[
        'nuskha_template_id' => '',
        'nuskha_name' => '',
        'quantity' => '1',
        'unit' => 'pack',
        'unit_price' => '',
        'instructions' => '',
    ]]);

    $methodLabels = [
        'cash' => 'Cash',
        'bank' => 'Bank transfer',
        'card' => 'Card',
    ];
@endphp

<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <div class="form-page-header">
            <a href="{{ route('orders.index') }}" class="back-link">
                <i class="bi bi-arrow-left app-icon" aria-hidden="true"></i>
                Back to orders
            </a>
            <h1>New nuskha order</h1>
            <p>Record a customer order without creating a patient or visit.</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Please correct the following:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('orders.store') }}"
            data-nuskha-order-form
            data-currency="{{ $clinic->currency }}"
        >
            @csrf

            {{-- New Order — Customer --}}
            <section class="payment-form-panel">
                <div class="payment-form-heading">
                    <div class="form-section-number">01</div>
                    <div>
                        <h2>Customer details</h2>
                        <p>Enter the customer’s contact information.</p>
                    </div>
                </div>

                <div class="payment-form-grid">
                    <div class="form-field">
                        <label for="customer_name" class="form-label">Customer name *</label>
                        <input
                            type="text"
                            id="customer_name"
                            name="customer_name"
                            class="form-control"
                            value="{{ old('customer_name') }}"
                            maxlength="160"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label for="customer_phone" class="form-label">Customer phone *</label>
                        <x-phone-input
                            name="customer_phone"
                            id="customer_phone"
                            :required="true"
                        />
                    </div>

                    <div class="form-field">
                        <label for="ordered_at" class="form-label">Order date/time *</label>
                        <input
                            type="datetime-local"
                            id="ordered_at"
                            name="ordered_at"
                            class="form-control"
                            value="{{ old('ordered_at', now('Asia/Dubai')->format('Y-m-d\TH:i')) }}"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label for="order_source" class="form-label">Order source *</label>
                        <select id="order_source" name="order_source" class="form-select" required>
                            <option value="walk_in" @selected(old('order_source', 'walk_in') === 'walk_in')>
                                Walk-in
                            </option>
                            <option value="phone" @selected(old('order_source') === 'phone')>
                                Phone order
                            </option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="fulfillment_method" class="form-label">Fulfillment *</label>
                        <select
                            id="fulfillment_method"
                            name="fulfillment_method"
                            class="form-select"
                            data-order-fulfillment
                            required
                        >
                            <option value="pickup" @selected(old('fulfillment_method', 'pickup') === 'pickup')>
                                Pickup
                            </option>
                            <option value="delivery" @selected(old('fulfillment_method') === 'delivery')>
                                Delivery
                            </option>
                        </select>
                    </div>

                    <div class="form-field payment-field-wide" data-order-address>
                        <label for="delivery_address" class="form-label">Delivery address *</label>
                        <textarea
                            id="delivery_address"
                            name="delivery_address"
                            class="form-control"
                            rows="2"
                            maxlength="500"
                        >{{ old('delivery_address') }}</textarea>
                    </div>
                </div>
            </section>

            {{-- New Order — Nuskhas --}}
            <section class="payment-form-panel">
                <div class="payment-form-heading">
                    <div class="form-section-number">02</div>
                    <div>
                        <h2>Ordered nuskhas</h2>
                        <p>Select a saved template or enter a nuskha name manually.</p>
                    </div>
                </div>

                <div data-order-items>
                    @foreach($orderItems as $index => $item)
                        <div class="order-item-row" data-order-item>
                            <div class="order-item-heading">
                                <strong>
                                    Nuskha <span data-order-item-number>{{ $index + 1 }}</span>
                                </strong>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    data-order-remove
                                >
                                    Remove
                                </button>
                            </div>

                            <div class="payment-form-grid">
                                <label class="form-field">
                                    <span class="form-label">Saved template</span>
                                    <select
                                        name="items[{{ $index }}][nuskha_template_id]"
                                        class="form-select"
                                        data-order-field="nuskha_template_id"
                                    >
                                        <option value="">Manual nuskha</option>
                                        @foreach($templates as $template)
                                            <option
                                                value="{{ $template->nuskha_template_id }}"
                                                @selected(
                                                    (string) ($item['nuskha_template_id'] ?? '')
                                                        === (string) $template->nuskha_template_id
                                                )
                                            >
                                                {{ $template->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>

                                <label class="form-field">
                                    <span class="form-label">Manual nuskha name</span>
                                    <input
                                        type="text"
                                        name="items[{{ $index }}][nuskha_name]"
                                        class="form-control"
                                        value="{{ $item['nuskha_name'] ?? '' }}"
                                        maxlength="160"
                                        data-order-field="nuskha_name"
                                    >
                                </label>

                                <label class="form-field">
                                    <span class="form-label">Quantity *</span>
                                    <input
                                        type="number"
                                        name="items[{{ $index }}][quantity]"
                                        class="form-control"
                                        value="{{ $item['quantity'] ?? '1' }}"
                                        min="0.001"
                                        max="1000"
                                        step="0.001"
                                        data-order-field="quantity"
                                        required
                                    >
                                </label>

                                <label class="form-field">
                                    <span class="form-label">Unit *</span>
                                    <select
                                        name="items[{{ $index }}][unit]"
                                        class="form-select"
                                        data-order-field="unit"
                                        required
                                    >
                                        @foreach($units as $unit)
                                            <option
                                                value="{{ $unit }}"
                                                @selected(($item['unit'] ?? 'pack') === $unit)
                                            >
                                                {{ $unit }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>

                                <label class="form-field">
                                    <span class="form-label">
                                        Price per selected unit ({{ $clinic->currency }}) *
                                    </span>
                                    <input
                                        type="number"
                                        name="items[{{ $index }}][unit_price]"
                                        class="form-control"
                                        value="{{ $item['unit_price'] ?? '' }}"
                                        min="0"
                                        max="9999999999.99"
                                        step="0.01"
                                        data-order-field="unit_price"
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
                                        data-order-field="instructions"
                                    >
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-outline-secondary" data-order-add>
                    <i class="bi bi-plus-circle app-icon" aria-hidden="true"></i>
                    Add another nuskha
                </button>
            </section>

            {{-- New Order — Totals and Payment --}}
            <section class="payment-form-panel">
                <div class="payment-form-heading">
                    <div class="form-section-number">03</div>
                    <div>
                        <h2>Amount and payment</h2>
                        <p>Enter zero received to save an unpaid order.</p>
                    </div>
                </div>

                <div class="payment-form-grid">
                    <label class="form-field">
                        <span class="form-label">Discount</span>
                        <input
                            type="number"
                            name="discount"
                            class="form-control"
                            value="{{ old('discount', '0') }}"
                            min="0"
                            max="9999999999.99"
                            step="0.01"
                            data-order-discount
                            required
                        >
                    </label>

                    <label class="form-field" data-order-delivery-fee>
                        <span class="form-label">Delivery fee</span>
                        <input
                            type="number"
                            name="delivery_fee"
                            class="form-control"
                            value="{{ old('delivery_fee', '0') }}"
                            min="0"
                            max="9999999999.99"
                            step="0.01"
                            required
                        >
                    </label>

                    <label class="form-field">
                        <span class="form-label">Amount received *</span>
                        <input
                            type="number"
                            name="payment_amount"
                            class="form-control"
                            value="{{ old('payment_amount', '0') }}"
                            min="0"
                            max="9999999999.99"
                            step="0.01"
                            required
                        >
                    </label>

                    <label class="form-field">
                        <span class="form-label">Payment method</span>
                        <select name="payment_method" class="form-select">
                            <option value="">Select method</option>
                            @foreach($enabledMethods as $method)
                                <option value="{{ $method }}" @selected(old('payment_method') === $method)>
                                    {{ $methodLabels[$method] }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="form-field payment-field-wide">
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
                        <span class="form-label">Order notes</span>
                        <textarea name="notes" class="form-control" rows="2" maxlength="5000">{{ old('notes') }}</textarea>
                    </label>
                </div>

                <div class="balance-summary-grid mt-4">
                    <div>
                        <span>Subtotal</span>
                        <strong data-order-subtotal>—</strong>
                    </div>
                    <div>
                        <span>Order total</span>
                        <strong data-order-total>—</strong>
                    </div>
                    <div>
                        <span>Remaining after payment</span>
                        <strong data-order-due>—</strong>
                    </div>
                </div>

                <small class="text-muted d-block mt-2" data-order-total-error></small>
            </section>

            <div class="payment-form-actions">
                <a href="{{ route('orders.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    Save order
                </button>
            </div>
        </form>
    </main>
</div>
@endsection