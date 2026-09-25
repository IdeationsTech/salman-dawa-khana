<div class="nuskha-fields-grid">
    <label class="form-label nuskha-field-wide">
        Item / ingredient name *
        <input
            type="text"
            name="items[{{ $index }}][item_name]"
            class="form-control"
            value="{{ $item['item_name'] ?? '' }}"
            maxlength="160"
            data-rx-field="item_name"
            required
        >
    </label>

    <label class="form-label">
        Quantity {{ $quantityRequired ? '*' : '' }}
        <input
            type="number"
            name="items[{{ $index }}][quantity]"
            class="form-control"
            value="{{ $item['quantity'] ?? '' }}"
            min="0.001"
            max="999999999.999"
            step="0.001"
            placeholder="e.g. 100"
            data-rx-field="quantity"
            @required($quantityRequired)
        >
    </label>

    <label class="form-label">
        Unit {{ $quantityRequired ? '*' : '' }}
        <select
            name="items[{{ $index }}][unit]"
            class="form-select"
            data-rx-field="unit"
            @required($quantityRequired)
        >
            <option value="">Select unit</option>

            @foreach (\App\Models\NuskhaItem::UNITS as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(($item['unit'] ?? '') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="form-label">
        Dosage
        <input
            type="text"
            name="items[{{ $index }}][dosage]"
            class="form-control"
            value="{{ $item['dosage'] ?? '' }}"
            maxlength="80"
            placeholder="Amount per dose"
            data-rx-field="dosage"
        >
    </label>

    <label class="form-label">
        Frequency
        <input
            type="text"
            name="items[{{ $index }}][frequency]"
            class="form-control"
            value="{{ $item['frequency'] ?? '' }}"
            maxlength="80"
            placeholder="As prescribed"
            data-rx-field="frequency"
        >
    </label>

    <label class="form-label">
        Duration
        <input
            type="text"
            name="items[{{ $index }}][duration]"
            class="form-control"
            value="{{ $item['duration'] ?? '' }}"
            maxlength="80"
            placeholder="Treatment duration"
            data-rx-field="duration"
        >
    </label>

    <label class="form-label">
        Timing
        <input
            type="text"
            name="items[{{ $index }}][timing]"
            class="form-control"
            value="{{ $item['timing'] ?? '' }}"
            maxlength="80"
            placeholder="When to take"
            data-rx-field="timing"
        >
    </label>

    <label class="form-label nuskha-field-wide">
        Item instructions
        <input
            type="text"
            name="items[{{ $index }}][instructions]"
            class="form-control"
            value="{{ $item['instructions'] ?? '' }}"
            maxlength="255"
            data-rx-field="instructions"
        >
    </label>
</div>