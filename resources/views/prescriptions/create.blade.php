@extends('layouts.app')

@section(
    'title',
    isset($prescription)
        ? 'Edit Prescription — Salman Dawa Khana'
        : 'Create Prescription — Salman Dawa Khana'
)

@section('content')
@php
    $editing = isset($prescription) && $prescription->exists;

    $defaultItems = $editing
        ? $prescription->items->map(
            fn ($item) => $item->only(\App\Models\NuskhaItem::ITEM_FIELDS)
        )->all()
        : [[]];

    $items = old('items', $defaultItems);

    $items = is_array($items)
        ? array_values(array_filter($items, 'is_array'))
        : [];

    if (!$items) {
        $items = [[]];
    }

    $selectedVisit = old(
        'visit_id',
        $prescription->visit_id ?? request('visit_id')
    );

    $requestedVisit = $visits->firstWhere('visit_id', $selectedVisit);

    $selectedPatient = old(
        'patient_id',
        $prescription->patient_id
            ?? request('patient_id', $requestedVisit?->patient_id)
    );

    $selectedTemplate = old('template_id', request('template_id'));

    $mode = old(
        'prescription_type',
        !$editing && request()->filled('template_id') ? 'template' : 'custom'
    );

    $dateValue = old(
        'prescribed_at',
        $editing
            ? $prescription->prescribed_at->format('Y-m-d\TH:i')
            : now('Asia/Dubai')->format('Y-m-d\TH:i')
    );
@endphp

<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <div class="form-page-header">
            <a href="{{ route('prescriptions.index') }}" class="back-link">
                ← Back to prescriptions
            </a>
            <h1>{{ $editing ? 'Edit prescription' : 'Create prescription' }}</h1>
            <p>Prepare a nuskha for a patient visit.</p>
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

        <form
            action="{{ $editing
                ? route('prescriptions.update', $prescription)
                : route('prescriptions.store') }}"
            method="POST"
            data-prescription-form
        >
            @csrf

            @if ($editing)
                @method('PUT')
            @endif

            {{-- PRESCRIPTION — Patient and Visit --}}
            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">01</div>
                    <div>
                        <h2>Patient and visit</h2>
                        <p>Select the patient and related visit.</p>
                    </div>
                </div>

                <div class="template-form-grid">
                    <div class="form-field">
                        <label for="patient" class="form-label">Patient *</label>

                        @if ($editing)
                            <input
                                type="hidden"
                                name="patient_id"
                                value="{{ $prescription->patient_id }}"
                            >
                        @endif

                        <select
                            id="patient"
                            name="patient_id"
                            class="form-select"
                            data-rx-patient
                            required
                            @disabled($editing)
                        >
                            <option value="">Select patient</option>
                            @foreach ($patients as $patient)
                                <option
                                    value="{{ $patient->patient_id }}"
                                    @selected(
                                        (string) ($editing ? $prescription->patient_id : $selectedPatient)
                                        === (string) $patient->patient_id
                                    )
                                >
                                    {{ $patient->full_name }} — {{ $patient->patient_code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="visit" class="form-label">Visit</label>
                        <select
                            id="visit"
                            name="visit_id"
                            class="form-select"
                            data-rx-visit
                        >
                            <option value="">No linked visit</option>
                            @foreach ($visits as $visit)
                                <option
                                    value="{{ $visit->visit_id }}"
                                    data-patient-id="{{ $visit->patient_id }}"
                                    @selected((string) $selectedVisit === (string) $visit->visit_id)
                                >
                                    #{{ $visit->visit_id }}
                                    — {{ $visit->visit_date->format('d M Y, h:i A') }}
                                    — {{ $visit->visit_reason }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-help">
                            Only the selected patient's visits are available.
                        </small>
                    </div>

                    <div class="form-field">
                        <label for="prescribed_at" class="form-label">
                            Prescription Date &amp; Time — UAE *
                        </label>
                        <input
                            type="datetime-local"
                            id="prescribed_at"
                            name="prescribed_at"
                            class="form-control"
                            value="{{ $dateValue }}"
                            min="1000-01-01T00:00"
                            max="9999-12-31T23:59"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label for="prescription_status" class="form-label">
                            Status *
                        </label>
                        <select
                            id="prescription_status"
                            name="status"
                            class="form-select"
                            required
                        >
                            @foreach ([
                                'issued' => 'Issued',
                                'draft' => 'Draft',
                                'cancelled' => 'Cancelled',
                            ] as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(old('status', $prescription->status ?? 'issued') === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            {{-- PRESCRIPTION — Choose Template --}}
            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">02</div>
                    <div>
                        <h2>Choose a nuskha</h2>
                        <p>Load a saved template or enter ingredients manually.</p>
                    </div>
                </div>

                <div class="prescription-choice-grid">
                    @foreach ([
                        'template' => ['Use saved template', 'Select from your nuskha library'],
                        'custom' => ['Create custom nuskha', 'Add items manually'],
                    ] as $value => [$heading, $description])
                        <label class="prescription-choice {{ $mode === $value ? 'active' : '' }}">
                            <input
                                type="radio"
                                name="prescription_type"
                                value="{{ $value }}"
                                @checked($mode === $value)
                            >
                            <span class="choice-content">
                                <strong>{{ $heading }}</strong>
                                <small>{{ $description }}</small>
                            </span>
                        </label>
                    @endforeach
                </div>

                <div
                    class="form-field template-selection-field {{ $mode === 'template' ? '' : 'd-none' }}"
                    data-rx-template-section
                >
                    <label for="template" class="form-label">
                        Saved nuskha template
                    </label>
                    <select
                        id="template"
                        name="template_id"
                        class="form-select"
                        data-rx-template
                        @required($mode === 'template')
                        @disabled($mode !== 'template')
                    >
                        <option value="">Select a saved template</option>
                        @foreach ($templates as $template)
                            <option
                                value="{{ $template->nuskha_template_id }}"
                                @selected((string) $selectedTemplate === (string) $template->nuskha_template_id)
                            >
                                {{ $template->name }} — {{ $template->items->count() }} items
                            </option>
                        @endforeach
                    </select>

                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <button
                            type="button"
                            class="btn btn-outline-primary"
                            data-rx-apply-template
                        >
                            Load template items
                        </button>
                        <a
                            href="{{ route('prescriptions.templates.index') }}"
                            class="btn btn-light"
                        >
                            Manage templates
                        </a>
                    </div>

                    <small class="form-help">
                        Loading replaces the current items and instructions.
                        Review them before saving.
                    </small>
                    <p
                        class="small mt-2 mb-0"
                        data-rx-template-message
                        role="status"
                    ></p>
                </div>
            </section>

            {{-- PRESCRIPTION — Ingredients --}}
            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">03</div>
                    <div>
                        <h2>Prescription items</h2>
                        <p>Review ingredient quantities and patient-specific dosage.</p>
                    </div>
                </div>

                <div class="prescription-item-list" data-prescription-item-list>
                    @foreach ($items as $index => $item)
                        <div class="prescription-item-row">
                            <div class="item-number">{{ $index + 1 }}</div>

                            @include('prescriptions._item-fields', [
                                'item' => $item,
                                'index' => $index,
                                'quantityRequired' => false,
                            ])

                            <button
                                type="button"
                                class="remove-item-button"
                                data-remove-prescription-item
                                aria-label="Remove item"
                            >
                                ×
                            </button>
                        </div>
                    @endforeach
                </div>

                <button
                    type="button"
                    class="btn btn-outline-primary add-item-button"
                    data-add-prescription-item
                >
                    + Add custom item
                </button>

                <p class="form-help mt-2 mb-0">
                    If you enter a quantity, select its unit too.
                </p>
            </section>

            {{-- PRESCRIPTION — General Instructions --}}
            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">04</div>
                    <div>
                        <h2>Instructions and notes</h2>
                        <p>Add patient-specific instructions.</p>
                    </div>
                </div>

                <div class="template-form-grid">
                    <div class="form-field">
                        <label for="prepared_by" class="form-label">
                            Prepared by
                        </label>
                        <input
                            type="text"
                            id="prepared_by"
                            class="form-control"
                            value="{{ $editing
                                ? ($prescription->createdBy?->name ?? '—')
                                : auth()->user()->name }}"
                            readonly
                        >
                    </div>

                    <div class="form-field template-field-wide">
                        <label for="notes" class="form-label">
                            Patient instructions
                        </label>
                        <textarea
                            id="notes"
                            name="general_instructions"
                            class="form-control"
                            rows="4"
                            maxlength="10000"
                            data-rx-instructions
                        >{{ old('general_instructions', $prescription->general_instructions ?? '') }}</textarea>
                    </div>
                </div>
            </section>

            <div class="template-form-actions">
                <a href="{{ route('prescriptions.index') }}" class="btn btn-light">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ $editing ? 'Update prescription' : 'Save prescription' }}
                </button>
            </div>

            <script type="application/json" data-rx-template-data>
                @json($templateData)
            </script>
        </form>
    </main>
</div>
@endsection