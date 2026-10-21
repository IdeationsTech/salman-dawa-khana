@extends('layouts.app')

@section('title', isset($prescription) ? 'Edit Prescription' : 'Create Prescription')

@section('content')
@php
    $editing = isset($prescription) && $prescription->exists;

    $legacyPrescription = $editing && $prescription->nuskhas->isEmpty();

    $defaultNuskhas = $editing && !$legacyPrescription
        ? $prescription->nuskhas->map(fn ($nuskha) => [
            'assignment_id' => $nuskha->prescription_nuskha_id,
            'source' => $nuskha->nuskha_template_id ? 'template' : 'custom',
            ...$nuskha->only([
                'nuskha_template_id', 'nuskha_name', 'quantity', 'unit',
                'frequency', 'duration', 'timing', 'instructions',
            ]),
        ])->all()
        : [[
            'source' => $legacyPrescription ? 'custom' : 'template',
            'nuskha_name' => $legacyPrescription ? $prescription->nuskha_name : '',
            'nuskha_template_id' => $editing ? null : request('template_id'),
        ]];

    $nuskhas = old('nuskhas', $defaultNuskhas);
    $nuskhas = is_array($nuskhas)
        ? array_values(array_filter($nuskhas, 'is_array'))
        : [];

    if (!$nuskhas) {
        $nuskhas = [['source' => 'template']];
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

    $dateValue = old(
        'prescribed_at',
        $editing
            ? $prescription->prescribed_at->format('Y-m-d\TH:i')
            : \App\Support\ClinicClock::now()->format('Y-m-d\TH:i')
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
            <p>Assign one or more nuskhay with their total quantities and patient instructions.</p>
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
            data-multiple-nuskhas
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
                    @php
                        $currentPatient = $patients->firstWhere('patient_id', (int) $selectedPatient);
                    @endphp
                    <div
                        class="form-field rx-patient-search"
                        data-rx-patient-search
                        data-search-url="{{ route('prescriptions.patients.search') }}"
                        data-visits-url="{{ route('prescriptions.patients.visits', ['patient' => '__PATIENT__']) }}"
                        data-selected-name="{{ $currentPatient?->full_name ?? '' }}"
                        data-locked="{{ $editing ? '1' : '0' }}"
                    >
                        <label for="rx-patient-search" class="form-label">Patient *</label>
                        <input
                            type="hidden"
                            name="patient_id"
                            value="{{ $currentPatient?->patient_id ?? '' }}"
                            data-rx-patient
                        >
                        <input
                            type="text"
                            id="rx-patient-search"
                            name="patient_search"
                            class="form-control"
                            value="{{ $currentPatient?->full_name ?? old('patient_search', '') }}"
                            placeholder="Start typing a patient name..."
                            maxlength="160"
                            autocomplete="off"
                            role="combobox"
                            aria-autocomplete="list"
                            aria-expanded="false"
                            aria-controls="rx-patient-results"
                            aria-describedby="rx-patient-search-help"
                            data-rx-patient-query
                            @readonly($editing)
                            required
                        >
                        <div class="rx-patient-results" id="rx-patient-results" role="listbox" aria-label="Matching patients" data-rx-patient-results hidden></div>
                        <small id="rx-patient-search-help" class="form-help" data-rx-patient-feedback role="status" aria-live="polite">
                            @if ($currentPatient)
                                Selected: {{ $currentPatient->patient_code }}{{ $currentPatient->phone ? ' · '.$currentPatient->phone : '' }}
                                {{ $editing ? ' · Patient cannot be changed on an existing prescription.' : '' }}
                            @else
                                Type a name, then select the matching patient.
                            @endif
                        </small>
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
                        <h2>Prescribed nuskhay</h2>
                        <p>Select a template or enter a custom name for each nuskha.</p>
                    </div>
                </div>

                @if ($legacyPrescription)
                    <div class="alert alert-info" role="status">
                        This older prescription has no recorded finished-nuskha quantity.
                        Confirm its name, total quantity and unit before saving.
                        Historical ingredient quantities are not used as the nuskha's weight.
                    </div>
                @endif

                <div class="rx-nuskha-list" data-rx-nuskha-list>
                    @foreach ($nuskhas as $index => $row)
                        @include('prescriptions._nuskha-fields', compact('row', 'index'))
                    @endforeach
                </div>

                <template data-rx-nuskha-prototype>
                    @include('prescriptions._nuskha-fields', [
                        'row' => ['source' => 'template'],
                        'index' => '__INDEX__',
                    ])
                </template>

                <button type="button" class="btn btn-outline-primary add-item-button" data-rx-add-nuskha>
                    <i class="bi bi-plus-circle" aria-hidden="true"></i>
                    Add another nuskha
                </button>
                <p class="form-help mt-2" data-rx-nuskha-count role="status"></p>
            </section>

            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">03</div>
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


        </form>
    </main>
</div>
@endsection
