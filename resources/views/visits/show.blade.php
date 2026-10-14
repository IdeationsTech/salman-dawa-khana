@extends('layouts.app')

@section('title', 'Visit Details')

@section('content')
@php
    $visit->loadMissing([
        'clinic',
        'patient',
        'recordedBy.role',
    ]);

    $statusLabels = [
        'in_progress' => 'In progress',
        'completed' => 'Completed',
        'follow_up_required' => 'Follow-up required',
        'cancelled' => 'Cancelled',
    ];
@endphp

<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        @if (session('success'))
            <div class="alert alert-success no-print" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="clh-toolbar no-print">
            <a href="{{ route('visits.index') }}" class="back-link">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Back to visits
            </a>

            <div class="clh-toolbar-actions">
                @if ($visit->patient)
                    <a
                        href="{{ route('patients.show', $visit->patient) }}"
                        class="btn btn-outline-secondary"
                    >
                        Patient profile
                    </a>
                @endif

                <a
                    href="{{ route('visits.edit', $visit) }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-pencil" aria-hidden="true"></i>
                    Edit visit
                </a>

                <button
                    type="button"
                    class="btn btn-primary"
                    data-print-page
                >
                    <i class="bi bi-printer" aria-hidden="true"></i>
                    Print patient slip
                </button>
            </div>
        </div>

        <x-clinic-letterhead
            :clinic="$visit->clinic"
            :patient="$visit->patient"
            :practitioner="$visit->recordedBy"
            :date="$visit->visit_date"
            document-title="Patient / visit slip"
            :reference="'Visit #' . $visit->visit_id"
            :visit-type="$visit->visit_reason"
        >
            <h3 class="clh-section-title">Tashkhees / Diagnosis</h3>

            <p class="clh-diagnosis">
                {{ $visit->diagnosis_name ?: '—' }}
            </p>

            <h3 class="clh-section-title">Clinical notes</h3>

            <p class="clh-notes">{{ $visit->general_notes }}</p>

            <h3 class="clh-section-title">Prescription</h3>

            <div class="clh-prescription-area">
                <span class="clh-rx" aria-label="Prescription">℞</span>
            </div>

            <p class="clh-follow-up">
                Next visit / follow-up: ____________________
            </p>
        </x-clinic-letterhead>

        <section
            class="visit-form-panel clh-screen-details no-print"
            data-visit-calendar-form
        >
            <div class="visit-form-heading">
                <div>
                    <h2>Visit information</h2>
                    <p>Calendar and record status.</p>
                </div>
            </div>

            <div class="visit-form-grid">
                <div class="form-field">
                    <span class="form-label d-block">Status</span>
                    <strong>
                        {{ $statusLabels[$visit->status] ?? 'Not recorded' }}
                    </strong>
                </div>

                <div class="form-field">
                    <label for="detail_visit_date" class="form-label">
                        Gregorian date
                    </label>

                    <input
                        type="date"
                        id="detail_visit_date"
                        class="form-control"
                        value="{{ $visit->visit_date->format('Y-m-d') }}"
                        data-visit-date
                        readonly
                    >
                </div>

                <div class="form-field">
                    <label for="detail_visit_day" class="form-label">
                        Day
                    </label>

                    <input
                        type="text"
                        id="detail_visit_day"
                        class="form-control"
                        value="{{ $visit->visit_date->format('l') }}"
                        data-visit-weekday
                        readonly
                    >
                </div>

                <div class="form-field">
                    <label for="detail_hijri_date" class="form-label">
                        Hijri date — Umm al-Qura
                    </label>

                    <input
                        type="text"
                        id="detail_hijri_date"
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
            </div>
        </section>
    </main>
</div>
@endsection