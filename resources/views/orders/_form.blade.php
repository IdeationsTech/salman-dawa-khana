@php
    $editing = isset($order);

    $savedItems = $editing
        ? $order->items->map(fn ($item) => [
            'item_id' => $item->nuskha_order_item_id,
            'nuskha_template_id' => $item->nuskha_template_id,
            'nuskha_name' => $item->nuskha_name,
            'quantity' => $item->quantity,
            'unit' => $item->unit,
            'unit_price' => $item->unit_price,
            'instructions' => $item->instructions,
        ])->values()->all()
        : [[
            'item_id' => '',
            'nuskha_template_id' => '',
            'nuskha_name' => '',
            'quantity' => '1',
            'unit' => 'pack',
            'unit_price' => '',
            'instructions' => '',
        ]];

    $formItems = old('items', $savedItems);
    $currency = $editing ? $order->currency : $clinic->currency;

    $methodLabels = [
        'cash' => 'Cash',
        'bank' => 'Bank transfer',
        'card' => 'Card',
    ];
@endphp

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

<form
    method="POST"
    action="{{ $editing ? route('orders.update', $order) : route('orders.store') }}"
    data-order-editor
    data-currency="{{ $currency }}"
    data-existing-paid="{{ $paidAmount }}"
>
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <section class="payment-form-panel">
        <div class="payment-form-heading">
            <div class="form-section-number">01</div>
            <div>
                <h2>Customer details</h2>
                <p>Order currency: {{ $currency }}</p>
            </div>
        </div>

        <div class="payment-form-grid">
            <label class="form-field">
                <span class="form-label">Customer name *</span>
                <input
                    type="text"
                    name="customer_name"
                    class="form-control"
                    value="{{ old('customer_name', $order->customer_name ?? '') }}"
                    maxlength="160"
                    required
                >
            </label>

            <div class="form-field">
                <label for="customer_phone" class="form-label">Customer phone *</label>
                <x-phone-input
                    name="customer_phone"
                    id="customer_phone"
                    :value="$order->customer_phone ?? ''"
                    :required="true"
                />
            </div>

            <label class="form-field">
                <span class="form-label">Order date/time *</span>
                <input
                    type="datetime-local"
                    name="ordered_at"
                    class="form-control"
                    value="{{ old('ordered_at', isset($order) ? $order->ordered_at->format('Y-m-d\TH:i') : now('Asia/Dubai')->format('Y-m-d\TH:i')) }}"
                    min="1000-01-01T00:00"
                    max="9999-12-31T23:59"
                    required
                >
            </label>

            <label class="form-field">
                <span class="form-label">Order source *</span>
                <select name="order_source" class="form-select" required>
                    <option value="walk_in" @selected(old('order_source', $order->order_source ?? 'walk_in') === 'walk_in')>
                        Walk-in
                    </option>
                    <option value="phone" @selected(old('order_source', $order->order_source ?? '') === 'phone')>
                        Phone order
                    </option>
                </select>
            </label>

            <label class="form-field">
                <span class="form-label">Fulfillment *</span>
                <select name="fulfillment_method" class="form-select" data-oe-fulfillment required>
                    <option value="pickup" @selected(old('fulfillment_method', $order->fulfillment_method ?? 'pickup') === 'pickup')>
                        Pickup
                    </option>
                    <option value="delivery" @selected(old('fulfillment_method', $order->fulfillment_method ?? '') === 'delivery')>
                        Delivery
                    </option>
                </select>
            </label>

            <label class="form-field payment-field-wide" data-oe-address>
                <span class="form-label">Delivery address *</span>
                <textarea
                    name="delivery_address"
                    class="form-control"
                    rows="2"
                    maxlength="500"
                >{{ old('delivery_address', $order->delivery_address ?? '') }}</textarea>
            </label>
        </div>
    </section>

    <section class="payment-form-panel">
        <div class="payment-form-heading">
            <div class="form-section-number">02</div>
            <div>
                <h2>Ordered nuskhas</h2>
                <p>Add, remove or replace nuskhas.</p>
            </div>
        </div>

        <div data-oe-items>
            @foreach ($formItems as $index => $item)
                <div class="order-item-row" data-oe-item>
                    <input
                        type="hidden"
                        name="items[{{ $index }}][item_id]"
                        value="{{ $item['item_id'] ?? '' }}"
                        data-oe-field="item_id"
                    >

                    <div class="order-item-heading">
                        <strong>Nuskha <span data-oe-number>{{ $index + 1 }}</span></strong>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-oe-remove>
                            <i class="bi bi-trash" aria-hidden="true"></i>
                            Remove
                        </button>
                    </div>

                    <div class="payment-form-grid">
                        <label class="form-field">
                            <span class="form-label">Saved template</span>
                            <select
                                name="items[{{ $index }}][nuskha_template_id]"
                                class="form-select"
                                data-oe-field="nuskha_template_id"
                            >
                                <option value="">Manual nuskha</option>
                                @foreach ($templates as $template)
                                    <option
                                        value="{{ $template->nuskha_template_id }}"
                                        @selected((string) ($item['nuskha_template_id'] ?? '') === (string) $template->nuskha_template_id)
                                    >
                                        {{ $template->name }}
                                        {{ !$template->is_active ? ' — inactive; existing item only' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="form-field">
                            <span class="form-label">Nuskha name</span>
                            <input
                                type="text"
                                name="items[{{ $index }}][nuskha_name]"
                                class="form-control"
                                value="{{ $item['nuskha_name'] ?? '' }}"
                                maxlength="160"
                                data-oe-field="nuskha_name"
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
                                data-oe-field="quantity"
                                required
                            >
                        </label>

                        <label class="form-field">
                            <span class="form-label">Unit *</span>
                            <select
                                name="items[{{ $index }}][unit]"
                                class="form-select"
                                data-oe-field="unit"
                                required
                            >
                                @foreach ($units as $unit)
                                    <option value="{{ $unit }}" @selected(($item['unit'] ?? 'pack') === $unit)>
                                        {{ $unit }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="form-field">
                            <span class="form-label">Unit price ({{ $currency }}) *</span>
                            <input
                                type="number"
                                name="items[{{ $index }}][unit_price]"
                                class="form-control"
                                value="{{ $item['unit_price'] ?? '' }}"
                                min="0"
                                max="9999999999.99"
                                step="0.01"
                                data-oe-field="unit_price"
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
                                data-oe-field="instructions"
                            >
                        </label>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button" class="btn btn-outline-secondary" data-oe-add>
            <i class="bi bi-plus-circle" aria-hidden="true"></i>
            Add another nuskha
        </button>
    </section>

    <section class="payment-form-panel">
        <div class="payment-form-heading">
            <div class="form-section-number">03</div>
            <div>
                <h2>Amount and payment</h2>
                <p>
                    {{ $editing
                        ? 'Existing payments and refunds remain recorded.'
                        : 'Enter zero received to save an unpaid order.' }}
                </p>
            </div>
        </div>

        <div class="payment-form-grid">
            <label class="form-field">
                <span class="form-label">Discount *</span>
                <input
                    type="number"
                    name="discount"
                    class="form-control"
                    value="{{ old('discount', $order->discount ?? '0') }}"
                    min="0"
                    max="9999999999.99"
                    step="0.01"
                    required
                >
            </label>

            <label class="form-field" data-oe-fee>
                <span class="form-label">Delivery fee *</span>
                <input
                    type="number"
                    name="delivery_fee"
                    class="form-control"
                    value="{{ old('delivery_fee', $order->delivery_fee ?? '0') }}"
                    min="0"
                    max="9999999999.99"
                    step="0.01"
                    required
                >
            </label>

            @if (!$editing)
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
                        @foreach ($enabledMethods as $method)
                            <option value="{{ $method }}" @selected(old('payment_method') === $method)>
                                {{ $methodLabels[$method] }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="form-field payment-field-wide">
                    <span class="form-label">Bank/card reference</span>
                    <input
                        type="text"
                        name="reference_no"
                        class="form-control"
                        value="{{ old('reference_no') }}"
                        maxlength="100"
                    >
                </label>
            @endif

            <label class="form-field payment-field-wide">
                <span class="form-label">Internal notes</span>
                <textarea
                    name="notes"
                    class="form-control"
                    rows="2"
                    maxlength="5000"
                >{{ old('notes', $order->notes ?? '') }}</textarea>
            </label>
        </div>

        <div class="balance-summary-grid mt-4">
            <div><span>Subtotal</span><strong data-oe-subtotal>—</strong></div>
            <div><span>Order total</span><strong data-oe-total>—</strong></div>
            <div><span>Remaining balance</span><strong data-oe-due>—</strong></div>
        </div>

        <small class="text-danger d-block mt-2" data-oe-error></small>
    </section>

    <div class="payment-form-actions">
        <a href="{{ $editing ? route('orders.show', $order) : route('orders.index') }}" class="btn btn-light">
            Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            {{ $editing ? 'Update order' : 'Save order' }}
        </button>
    </div>
</form>