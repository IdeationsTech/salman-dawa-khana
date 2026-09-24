@extends('layouts.app')

@section(
    'title',
    isset($visit)
        ? 'Edit Visit — Salman Dawa Khana'
        : 'Create Visit — Salman Dawa Khana'
)

@section('content')
@php
    $editing = isset($visit) && $visit->exists;
    $currentTime = now('Asia/Dubai');

    $defaultDate = $editing
        ? $visit->visit_date->format('Y-m-d')
        : $currentTime->format('Y-m-d');

    $defaultTime = $editing
        ? $visit->visit_date->format('H:i')
        : $currentTime->format('H:i');

    $selectedPatient = old(
        'patient_id',
        $visit->patient_id ?? request('patient_id')
    );

    $visitReasons = [
        'General visit',
        'Follow-up',
        'New consultation',
        'Prescription refill',
    ];

    $statusOptions = [
        'in_progress' => 'In progress',
        'completed' => 'Completed',
        'follow_up_required' => 'Follow-up required',
        'cancelled' => 'Cancelled',
    ];
@endphp

<div class="app-shell">

    {{-- VISITS — Sidebar --}}
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
            <a href="{{ url('/visits') }}" class="sidebar-link active">
                <span>▣</span><span>Visits</span>
            </a>
            <a href="{{ url('/prescriptions') }}" class="sidebar-link">
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

    {{-- VISITS — Main Content --}}
    <main class="dashboard-main">

        <div class="form-page-header">
            <a href="{{ route('visits.index') }}" class="back-link">
                ← Back to visits
            </a>

            <h1>{{ $editing ? 'Edit visit' : 'Create new visit' }}</h1>
            <p>Record the visit date, time, status and diagnosis.</p>
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
                ? route('visits.update', $visit)
                : route('visits.store') }}"
            method="POST"
            data-visit-calendar-form
        >
            @csrf

            @if ($editing)
                @method('PUT')
            @endif

            {{-- VISITS — Patient --}}
            <section class="visit-form-panel">
                <div class="visit-form-heading">
                    <div class="form-section-number">01</div>
                    <div>
                        <h2>Patient information</h2>
                        <p>Select the patient for this visit.</p>
                    </div>
                </div>

                <div class="visit-form-grid">
                    <div class="form-field visit-field-wide">
                        <label for="patient" class="form-label">
                            Patient <span>*</span>
                        </label>

                        @if ($editing)
                            <input
                                type="hidden"
                                name="patient_id"
                                value="{{ $visit->patient_id }}"
                            >
                        @endif

                        <select
                            id="patient"
                            name="patient_id"
                            class="form-select @error('patient_id') is-invalid @enderror"
                            required
                            @disabled($editing)
                        >
                            <option value="">Select patient</option>

                            @foreach ($patients as $patient)
                                <option
                                    value="{{ $patient->patient_id }}"
                                    @selected(
                                        (string) ($editing ? $visit->patient_id : $selectedPatient)
                                        === (string) $patient->patient_id
                                    )
                                >
                                    {{ $patient->full_name }} — {{ $patient->patient_code }}
                                </option>
                            @endforeach
                        </select>

                        @error('patient_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        @if (!$editing)
                            <small class="form-help">
                                Patient not registered?
                                <a href="{{ route('patients.create') }}">
                                    Add new patient
                                </a>
                            </small>
                        @endif
                    </div>
                </div>
            </section>

            {{-- VISITS — Date and Time --}}
            <section class="visit-form-panel">
                <div class="visit-form-heading">
                    <div class="form-section-number">02</div>
                    <div>
                        <h2>Visit details</h2>
                        <p>Day and Hijri date update automatically.</p>
                    </div>
                </div>

                <div class="visit-form-grid">
                    <div class="form-field">
                        <label for="visit_date" class="form-label">
                            Gregorian Date <span>*</span>
                        </label>

                        <input
                            type="date"
                            id="visit_date"
                            name="visit_date"
                            class="form-control @error('visit_date') is-invalid @enderror"
                            value="{{ old('visit_date', $defaultDate) }}"
                            min="1000-01-01"
                            max="9999-12-31"
                            data-visit-date
                            required
                        >

                        @error('visit_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="visit_day" class="form-label">Day</label>

                        <input
                            type="text"
                            id="visit_day"
                            class="form-control"
                            placeholder="Calculated from date"
                            data-visit-weekday
                            readonly
                        >
                    </div>

                    <div class="form-field">
                        <label for="visit_hijri_date" class="form-label">
                            Hijri Date — Umm al-Qura
                        </label>

                        <input
                            type="text"
                            id="visit_hijri_date"
                            class="form-control"
                            placeholder="Calculated from date"
                            data-visit-hijri
                            readonly
                        >

                        <small
                            class="form-help"
                            data-visit-calendar-message
                            aria-live="polite"
                        >
                            Calculated from the selected Gregorian date.
                        </small>
                    </div>

                    <div class="form-field">
                        <label for="visit_time" class="form-label">
                            Visit Time — UAE <span>*</span>
                        </label>

                        <input
                            type="time"
                            id="visit_time"
                            name="visit_time"
                            class="form-control @error('visit_time') is-invalid @enderror"
                            value="{{ old('visit_time', $defaultTime) }}"
                            required
                        >

                        @error('visit_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="visit_type" class="form-label">
                            Visit Type <span>*</span>
                        </label>

                        <select
                            id="visit_type"
                            name="visit_reason"
                            class="form-select @error('visit_reason') is-invalid @enderror"
                            required
                        >
                            <option value="">Select visit type</option>

                            @foreach ($visitReasons as $reason)
                                <option
                                    value="{{ $reason }}"
                                    @selected(
                                        old('visit_reason', $visit->visit_reason ?? 'General visit')
                                        === $reason
                                    )
                                >
                                    {{ $reason }}
                                </option>
                            @endforeach
                        </select>

                        @error('visit_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- VISITS — Status --}}
            <section class="visit-form-panel">
                <div class="visit-form-heading">
                    <div class="form-section-number">03</div>
                    <div>
                        <h2>Visit status</h2>
                        <p>Select the current state of this visit.</p>
                    </div>
                </div>

                <div class="visit-form-grid">
                    <div class="form-field">
                        <label for="visit_status" class="form-label">
                            Status <span>*</span>
                        </label>

                        <select
                            id="visit_status"
                            name="status"
                            class="form-select @error('status') is-invalid @enderror"
                            required
                        >
                            <option value="">Select status</option>

                            @foreach ($statusOptions as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(
                                        old('status', $editing ? $visit->status : 'in_progress')
                                        === $value
                                    )
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        @if ($editing && $visit->status === null)
                            <small class="form-help">
                                This older visit has no recorded status.
                                Select its actual status before saving.
                            </small>
                        @endif
                    </div>
                </div>
            </section>

            {{-- VISITS — Tashkhees and Notes --}}
            <section class="visit-form-panel">
                <div class="visit-form-heading">
                    <div class="form-section-number">04</div>
                    <div>
                        <h2>Tashkhees and notes</h2>
                        <p>Record the illness or complaint for this visit.</p>
                    </div>
                </div>

                <div class="visit-form-grid">
                    <div class="form-field visit-field-wide">
                        <label for="diagnosis_name" class="form-label">
                            Tashkhees / Illness Name
                        </label>

                        <input
                            type="text"
                            id="diagnosis_name"
                            name="diagnosis_name"
                            class="form-control @error('diagnosis_name') is-invalid @enderror"
                            value="{{ old('diagnosis_name', $visit->diagnosis_name ?? '') }}"
                            placeholder="e.g. Heart problem / Pait mein dard"
                            maxlength="255"
                        >

                        @error('diagnosis_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field visit-field-wide">
                        <label for="notes" class="form-label">
                            Visit Notes
                        </label>

                        <textarea
                            id="notes"
                            name="general_notes"
                            class="form-control @error('general_notes') is-invalid @enderror"
                            rows="5"
                            placeholder="Write visit notes here..."
                        >{{ old('general_notes', $visit->general_notes ?? '') }}</textarea>

                        @error('general_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            <div class="visit-form-actions">
                <a href="{{ route('visits.index') }}" class="btn btn-light">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    {{ $editing ? 'Update visit' : 'Save visit' }}
                </button>
            </div>
        </form>
    </main>
</div>
@endsection