@extends('layouts.app')

@section(
    'title',
    isset($template)
        ? 'Edit Nuskha Template — Salman Dawa Khana'
        : 'Create Nuskha Template — Salman Dawa Khana'
)

@section('content')
@php
    $editing = isset($template) && $template->exists;

    $defaultItems = $editing
        ? $template->items->map(
            fn ($item) => $item->only(
                \App\Models\NuskhaItem::ITEM_FIELDS
            )
        )->all()
        : [[]];

    $items = old('items', $defaultItems);

    $items = is_array($items)
        ? array_values(array_filter($items, 'is_array'))
        : [];

    if (empty($items)) {
        $items = [[]];
    }

    $selectedStatus = (string) old(
        'is_active',
        $editing ? (int) $template->is_active : 1
    );
@endphp

<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">

        {{-- NUSKHA TEMPLATE — Heading --}}
        <div class="form-page-header no-print">
            <a
                href="{{ route('prescriptions.templates.index') }}"
                class="back-link"
            >
                ← Back to templates
            </a>

            <h1>
                {{ $editing
                    ? 'Edit nuskha template'
                    : 'Create new nuskha template' }}
            </h1>

            <p>
                Save ingredients, quantities and instructions
                for future prescriptions.
            </p>
        </div>

        {{-- NUSKHA TEMPLATE — Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger no-print" role="alert">
                <strong>Please correct the following:</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- NUSKHA TEMPLATE — Create / Update --}}
        <form
            action="{{ $editing
                ? route('prescriptions.templates.update', $template)
                : route('prescriptions.templates.store') }}"
            method="POST"
            data-template-form
            class="no-print"
        >
            @csrf

            @if ($editing)
                @method('PUT')
            @endif

            {{-- NUSKHA TEMPLATE — Basic Information --}}
            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">01</div>

                    <div>
                        <h2>Basic information</h2>
                        <p>Give this reusable nuskha a clear name.</p>
                    </div>
                </div>

                <div class="template-form-grid">
                    <div class="form-field">
                        <label for="template_name" class="form-label">
                            Template name *
                        </label>

                        <input
                            type="text"
                            id="template_name"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $template->name ?? '') }}"
                            maxlength="160"
                            placeholder="Enter nuskha name"
                            required
                        >

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="template_status" class="form-label">
                            Status *
                        </label>

                        <select
                            id="template_status"
                            name="is_active"
                            class="form-select"
                            required
                        >
                            <option
                                value="1"
                                @selected($selectedStatus === '1')
                            >
                                Active
                            </option>

                            <option
                                value="0"
                                @selected($selectedStatus === '0')
                            >
                                Inactive
                            </option>
                        </select>

                        <small class="form-help">
                            Only active templates appear when creating a prescription.
                        </small>
                    </div>
                </div>
            </section>

            {{-- NUSKHA TEMPLATE — Ingredients --}}
            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">02</div>

                    <div>
                        <h2>Nuskha ingredients</h2>
                        <p>
                            Add each ingredient and its quantity/unit.
                            Dosage is kept separately for a patient's prescription.
                        </p>
                    </div>
                </div>

                <div class="nuskha-item-list" data-item-list>
                    @foreach ($items as $index => $item)
                        <div class="nuskha-item-row">
                            <div class="item-number">{{ $index + 1 }}</div>

                            @include('prescriptions._item-fields', [
                                'item' => $item,
                                'index' => $index,
                                'quantityRequired' => true,
                            ])

                            <button
                                type="button"
                                class="remove-item-button"
                                aria-label="Remove ingredient"
                            >
                                ×
                            </button>
                        </div>
                    @endforeach
                </div>

                <button
                    type="button"
                    class="btn btn-outline-primary add-item-button"
                    data-add-item
                >
                    + Add another item
                </button>

                <p class="form-help mt-2 mb-0">
                    Maximum 100 ingredients. Quantity supports up to three decimal places.
                </p>
            </section>

            {{-- NUSKHA TEMPLATE — Instructions --}}
            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">03</div>

                    <div>
                        <h2>General instructions</h2>
                        <p>
                            These notes are copied into a prescription
                            when this template is loaded.
                        </p>
                    </div>
                </div>

                <div class="form-field">
                    <label for="template_instructions" class="form-label">
                        Instructions and notes
                    </label>

                    <textarea
                        id="template_instructions"
                        name="instructions"
                        class="form-control"
                        rows="4"
                        maxlength="10000"
                        placeholder="Enter general instructions..."
                    >{{ old('instructions', $template->instructions ?? '') }}</textarea>
                </div>

                @if ($editing)
                    <p class="form-help mt-2 mb-0">
                        Updating this template does not change prescriptions already saved for patients.
                    </p>
                @endif
            </section>

            {{-- NUSKHA TEMPLATE — Actions --}}
            <div class="template-form-actions">
                @if ($editing)
                    <button
                        type="button"
                        class="btn btn-outline-primary"
                        data-print-nuskha
                    >
                        Print for Pansar
                    </button>
                @endif

                <a
                    href="{{ route('prescriptions.templates.index') }}"
                    class="btn btn-light"
                >
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    {{ $editing ? 'Update template' : 'Save template' }}
                </button>
            </div>
        </form>

        {{-- PANSAR PRINT — Ingredient List Only --}}
        @if ($editing)
            <section
                class="nuskha-print-sheet"
                data-nuskha-print-sheet
                aria-label="Nuskha ingredient list for pansar"
            >
                <header class="nuskha-print-header">
                    <div>
                        <p class="nuskha-print-kicker">
                            Salman Dawa Khana
                        </p>

                        <h1>{{ $template->name }}</h1>

                        <p class="nuskha-print-subtitle">
                            Nuskha preparation list
                        </p>
                    </div>

                    <div class="nuskha-print-date">
                        Prepared on<br>
                        <strong>
                            {{ now('Asia/Dubai')->format('d M Y') }}
                        </strong>
                    </div>
                </header>

                <table class="nuskha-print-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ingredient</th>
                            <th class="nuskha-print-quantity">Quantity</th>
                            <th>Unit</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($template->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->item_name }}</td>
                                <td class="nuskha-print-quantity">
                                    {{ rtrim(rtrim(number_format(
                                        (float) $item->quantity,
                                        3,
                                        '.',
                                        ''
                                    ), '0'), '.') }}
                                </td>
                                <td>
                                    {{ \App\Models\NuskhaItem::UNITS[$item->unit]
                                        ?? $item->unit }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <footer class="nuskha-print-footer">
                    <span>Pansar / prepared by: ____________________</span>
                    <span>Checked by: ____________________</span>
                </footer>
            </section>
        @endif

    </main>
</div>
@endsection