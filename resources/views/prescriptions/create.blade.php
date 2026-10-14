@extends('layouts.app')

@section('title', isset($prescription) ? 'Edit Prescription' : 'Create Prescription')

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

    $defaultDoctor = $editing
        ? $prescription->doctor_user_id
        : $requestedVisit?->doctor_user_id;

    if (!$editing && !$doctors->contains('user_id', $defaultDoctor)) {
        $defaultDoctor = $doctors->contains('user_id', auth()->id())
            ? auth()->id()
            : ($doctors->count() === 1 ? $doctors->first()->user_id : null);
    }

    $selectedDoctor = old('doctor_user_id', $defaultDoctor);

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
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Back to prescriptions
            </a>
            <h1>{{ $editing ? 'Edit prescription' : 'Create prescription' }}</h1>
            <p>Prepare a nuskha for a patient.</p>
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

        @if ($doctors->isEmpty())
            <div class="alert alert-warning" role="status">
                No active Owner/Doctor is available.
                The clinic owner must activate or add a practitioner.
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

            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">01</div>
                    <div>
                        <h2>Patient, visit and practitioner</h2>
                        <p>Select the patient and treating Doctor/Hakeem.</p>
                    </div>
                </div>

                <div class="template-form-grid">
                    <div class="form-field">
                        <label for="patient" class="form-label">Patient *</label>
                        @if ($editing)
                            <input type="hidden" name="patient_id" value="{{ $prescription->patient_id }}">
                        @endif

                        <select
                            id="patient"
                            name="{{ $editing ? 'patient_display' : 'patient_id' }}"
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
                        <select id="visit" name="visit_id" class="form-select" data-rx-visit>
                            <option value="">No linked visit</option>
                            @foreach ($visits as $visit)
                                <option
                                    value="{{ $visit->visit_id }}"
                                    data-patient-id="{{ $visit->patient_id }}"
                                    data-doctor-id="{{ $visit->doctor_user_id }}"
                                    @selected((string) $selectedVisit === (string) $visit->visit_id)
                                >
                                    #{{ $visit->visit_id }}
                                    — {{ $visit->visit_date->format('d M Y, h:i A') }}
                                    — {{ $visit->visit_reason }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-help">
                            Only this patient's visits are available.
                        </small>
                    </div>

                    <div class="form-field">
                        <label for="doctor_user_id" class="form-label">
                            Treating Doctor / Hakeem *
                        </label>
                        <select
                            id="doctor_user_id"
                            name="doctor_user_id"
                            class="form-select"
                            data-rx-doctor
                            required
                        >
                            <option value="">Select Doctor / Hakeem</option>
                            @foreach ($doctors as $doctor)
                                <option
                                    value="{{ $doctor->user_id }}"
                                    @selected((string) $selectedDoctor === (string) $doctor->user_id)
                                >
                                    {{ $doctor->name }}
                                    @if (!$doctor->is_active)
                                        — Inactive, previously assigned
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="form-help" data-rx-doctor-message>
                            Select the practitioner issuing this prescription.
                        </small>
                    </div>

                    <div class="form-field">
                        <label for="prescribed_at" class="form-label">
                            Prescription date &amp; time — UAE *
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
                        <label for="prescription_status" class="form-label">Status *</label>
                        <select id="prescription_status" name="status" class="form-select" required>
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

            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">02</div>
                    <div>
                        <h2>Choose a nuskha</h2>
                        <p>Load a template or enter ingredients manually.</p>
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
                    <label for="template" class="form-label">Saved nuskha template</label>
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
                        <button type="button" class="btn btn-outline-primary" data-rx-apply-template>
                            Load template items
                        </button>
                        <a href="{{ route('prescriptions.templates.index') }}" class="btn btn-light">
                            Manage templates
                        </a>
                    </div>

                    <small class="form-help">
                        Loading replaces current items and instructions.
                    </small>
                    <p class="small mt-2 mb-0" data-rx-template-message role="status"></p>
                </div>

                <div class="form-field mt-3">
                    <label for="nuskha_name" class="form-label">
                        Custom nuskha name — patient copy
                    </label>
                    <input
                        type="text"
                        id="nuskha_name"
                        name="nuskha_name"
                        class="form-control"
                        value="{{ old('nuskha_name', $prescription->nuskha_name ?? '') }}"
                        maxlength="160"
                    >
                    <small class="form-help">
                        Saved-template mode uses the selected template's name.
                        Patient copies show the nuskha name, not ingredients.
                    </small>
                </div>
            </section>

            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">03</div>
                    <div>
                        <h2>Prescription items</h2>
                        <p>Review ingredient quantities and dosage.</p>
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
                                <i class="bi bi-x-lg" aria-hidden="true"></i>
                            </button>
                        </div>
                    @endforeach
                </div>

                <button
                    type="button"
                    class="btn btn-outline-primary add-item-button"
                    data-add-prescription-item
                >
                    <i class="bi bi-plus-circle" aria-hidden="true"></i>
                    Add custom item
                </button>
            </section>

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
                        <label for="prepared_by" class="form-label">Recorded by</label>
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
                        <label for="notes" class="form-label">Patient instructions</label>
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
                <a href="{{ route('prescriptions.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary" @disabled($doctors->isEmpty())>
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