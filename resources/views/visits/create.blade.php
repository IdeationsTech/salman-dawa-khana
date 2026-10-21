@extends('layouts.app')

@section('title', isset($visit) ? 'Edit Visit' : 'Create Visit')

@section('content')
@php
    $editing = isset($visit) && $visit->exists;
    $currentTime = \App\Support\ClinicClock::now();

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

    $defaultDoctor = $editing
        ? $visit->doctor_user_id
        : ($doctors->contains('user_id', auth()->id())
            ? auth()->id()
            : ($doctors->count() === 1 ? $doctors->first()->user_id : null));

    $selectedDoctor = old('doctor_user_id', $defaultDoctor);

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
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <div class="form-page-header">
            <a href="{{ route('visits.index') }}" class="back-link">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Back to visits
            </a>
            <h1>{{ $editing ? 'Edit visit' : 'Create new visit' }}</h1>
            <p>Record the patient, treating practitioner and consultation details.</p>
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
                The clinic owner must activate or add a practitioner first.
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

            <section class="visit-form-panel">
                <div class="visit-form-heading">
                    <div class="form-section-number">01</div>
                    <div>
                        <h2>Patient and practitioner</h2>
                        <p>Select who is receiving and providing the consultation.</p>
                    </div>
                </div>

                <div class="visit-form-grid">
                    <x-patient-search :patient="$patients->first()" :locked="$editing" />

                    <div class="form-field">
                        <label for="doctor_user_id" class="form-label">
                            Treating Doctor / Hakeem *
                        </label>

                        <select
                            id="doctor_user_id"
                            name="doctor_user_id"
                            class="form-select @error('doctor_user_id') is-invalid @enderror"
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

                        @error('doctor_user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <small class="form-help">
                            The person recording this visit is saved separately.
                        </small>
                    </div>
                </div>
            </section>

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
                        <label for="visit_date" class="form-label">Gregorian date *</label>
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
                            data-visit-weekday
                            readonly
                        >
                    </div>

                    <div class="form-field">
                        <label for="visit_hijri_date" class="form-label">
                            Hijri date — Umm al-Qura
                        </label>
                        <input
                            type="text"
                            id="visit_hijri_date"
                            class="form-control"
                            data-visit-hijri
                            readonly
                        >
                        <small
                            class="form-help"
                            data-visit-calendar-message
                            aria-live="polite"
                        ></small>
                    </div>

                    <div class="form-field">
                        <label for="visit_time" class="form-label">Visit time — UAE *</label>
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
                        <label for="visit_type" class="form-label">Visit type *</label>
                        <select
                            id="visit_type"
                            name="visit_reason"
                            class="form-select @error('visit_reason') is-invalid @enderror"
                            required
                        >
                            @foreach ($visitReasons as $reason)
                                <option
                                    value="{{ $reason }}"
                                    @selected(old('visit_reason', $visit->visit_reason ?? 'General visit') === $reason)
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
                        <label for="visit_status" class="form-label">Status *</label>
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
                                    @selected(old('status', $editing ? $visit->status : 'in_progress') === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

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
                            Tashkhees / Illness name
                        </label>
                        <input
                            type="text"
                            id="diagnosis_name"
                            name="diagnosis_name"
                            class="form-control @error('diagnosis_name') is-invalid @enderror"
                            value="{{ old('diagnosis_name', $visit->diagnosis_name ?? '') }}"
                            maxlength="255"
                        >
                        @error('diagnosis_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field visit-field-wide">
                        <label for="notes" class="form-label">Visit notes</label>
                        <textarea
                            id="notes"
                            name="general_notes"
                            class="form-control @error('general_notes') is-invalid @enderror"
                            rows="5"
                        >{{ old('general_notes', $visit->general_notes ?? '') }}</textarea>
                        @error('general_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            <div class="visit-form-actions">
                <a href="{{ route('visits.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary" @disabled($doctors->isEmpty())>
                    {{ $editing ? 'Update visit' : 'Save visit' }}
                </button>
            </div>
        </form>
    </main>
</div>
@endsection