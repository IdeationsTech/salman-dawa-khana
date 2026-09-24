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

    $blankItem = [
        'item_name' => '',
        'dosage' => '',
        'frequency' => '',
        'duration' => '',
        'timing' => '',
        'instructions' => '',
    ];

    $defaultItems = $editing
        ? $prescription->items->map(fn ($item) => [
            'item_name' => $item->item_name,
            'dosage' => $item->dosage,
            'frequency' => $item->frequency,
            'duration' => $item->duration,
            'timing' => $item->timing,
            'instructions' => $item->instructions,
        ])->all()
        : [$blankItem];

    $items = old('items', $defaultItems);

    $items = is_array($items)
        ? array_values(array_filter($items, 'is_array'))
        : [];

    if (!$items) {
        $items = [$blankItem];
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

    $mode = old('prescription_type', 'custom');

    $dateValue = old(
        'prescribed_at',
        $editing
            ? $prescription->prescribed_at->format('Y-m-d\TH:i')
            : now('Asia/Dubai')->format('Y-m-d\TH:i')
    );
@endphp

<div class="app-shell">

    {{-- PRESCRIPTION FORM — Sidebar --}}
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">+</div>
            <span>Salman Dawa Khana</span>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ url('/dashboard') }}" class="sidebar-link">
                <span>▦</span><span>Dashboard</span>
            </a>
            <a href="{{ url('/patients') }}" class="sidebar-link">
                <span>♙</span><span>Patients</span>
            </a>
            <a href="{{ url('/visits') }}" class="sidebar-link">
                <span>▣</span><span>Visits</span>
            </a>
            <a href="{{ url('/prescriptions') }}" class="sidebar-link active">
                <span>✎</span><span>Prescriptions</span>
            </a>
            <a href="{{ url('/payments') }}" class="sidebar-link">
                <span>₨</span><span>Payments</span>
            </a>
            <a href="{{ url('/expenses') }}" class="sidebar-link">
                <span>◈</span><span>Expenses</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

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

            {{-- PRESCRIPTION FORM — Patient and Visit --}}
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
                        <label for="patient" class="form-label">
                            Patient <span>*</span>
                        </label>

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
                        <label for="visit" class="form-label">
                            Visit
                        </label>

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
                            Prescription Date &amp; Time — UAE <span>*</span>
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
                            Status <span>*</span>
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

            {{-- PRESCRIPTION FORM — Template Selection --}}
            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">02</div>
                    <div>
                        <h2>Choose a nuskha</h2>
                        <p>Use a saved template or enter your own items.</p>
                    </div>
                </div>

                <div class="prescription-choice-grid">
                    <label class="prescription-choice {{ $mode === 'template' ? 'active' : '' }}">
                        <input
                            type="radio"
                            name="prescription_type"
                            value="template"
                            @checked($mode === 'template')
                        >
                        <span class="choice-content">
                            <strong>Use saved template</strong>
                            <small>Select from your nuskha library</small>
                        </span>
                    </label>

                    <label class="prescription-choice {{ $mode === 'custom' ? 'active' : '' }}">
                        <input
                            type="radio"
                            name="prescription_type"
                            value="custom"
                            @checked($mode === 'custom')
                        >
                        <span class="choice-content">
                            <strong>Create custom nuskha</strong>
                            <small>Add items manually</small>
                        </span>
                    </label>
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
                                @selected(
                                    (string) old('template_id')
                                    === (string) $template->nuskha_template_id
                                )
                            >
                                {{ $template->name }}
                                — {{ $template->items->count() }} items
                            </option>
                        @endforeach
                    </select>

                    <button
                        type="button"
                        class="btn btn-outline-primary mt-2"
                        data-rx-apply-template
                    >
                        Load template items
                    </button>

                    <small class="form-help">
                        Loading replaces the items and instructions below.
                        Review them before saving.
                    </small>

                    <p
                        class="small mt-2 mb-0"
                        data-rx-template-message
                        role="status"
                    ></p>
                </div>
            </section>

            {{-- PRESCRIPTION FORM — Items --}}
            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">03</div>
                    <div>
                        <h2>Prescription items</h2>
                        <p>Add, remove and review the prescribed items.</p>
                    </div>
                </div>

                <div class="prescription-item-list" data-prescription-item-list>
                    @foreach ($items as $index => $item)
                        <div class="prescription-item-row">
                            <div class="item-number">{{ $index + 1 }}</div>

                            <div class="form-field">
                                <label class="form-label">
                                    Item name *
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

                                <label class="form-label mt-2">
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

                            <div class="form-field">
                                <label class="form-label">
                                    Dosage
                                    <input
                                        type="text"
                                        name="items[{{ $index }}][dosage]"
                                        class="form-control"
                                        value="{{ $item['dosage'] ?? '' }}"
                                        maxlength="80"
                                        data-rx-field="dosage"
                                    >
                                </label>

                                <label class="form-label mt-2">
                                    Timing
                                    <input
                                        type="text"
                                        name="items[{{ $index }}][timing]"
                                        class="form-control"
                                        value="{{ $item['timing'] ?? '' }}"
                                        maxlength="80"
                                        data-rx-field="timing"
                                    >
                                </label>
                            </div>

                            <div class="form-field">
                                <label class="form-label">
                                    Frequency
                                    <input
                                        type="text"
                                        name="items[{{ $index }}][frequency]"
                                        class="form-control"
                                        value="{{ $item['frequency'] ?? '' }}"
                                        maxlength="80"
                                        data-rx-field="frequency"
                                    >
                                </label>

                                <label class="form-label mt-2">
                                    Duration
                                    <input
                                        type="text"
                                        name="items[{{ $index }}][duration]"
                                        class="form-control"
                                        value="{{ $item['duration'] ?? '' }}"
                                        maxlength="80"
                                        data-rx-field="duration"
                                    >
                                </label>
                            </div>

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
            </section>

            {{-- PRESCRIPTION FORM — Instructions --}}
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
                            rows="3"
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