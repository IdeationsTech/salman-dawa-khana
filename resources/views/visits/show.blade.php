@extends('layouts.app')

@section('title', 'Visit Details')

@section('content')
@php
    $visit->loadMissing([
        'clinic',
        'patient',
        'doctor',
        'recordedBy',
    ]);

    $statusLabels = [
        'in_progress' => __('In progress'),
        'completed' => __('Completed'),
        'follow_up_required' => __('Follow-up required'),
        'cancelled' => __('Cancelled'),
    ];
@endphp

<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        @if (session('success'))
            <div class="alert alert-success no-print" role="alert">
                {{ __(session('success')) }}
            </div>
        @endif

        <div class="clh-toolbar no-print">
            <a href="{{ route('visits.index') }}" class="back-link">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                {{ __('Back to visits') }}
            </a>

            <div class="clh-toolbar-actions">
                @if ($visit->patient)
                    <a
                        href="{{ route('patients.show', $visit->patient) }}"
                        class="btn btn-outline-secondary"
                    >
                        <i class="bi bi-person" aria-hidden="true"></i>
                        {{ __('Patient profile') }}
                    </a>
                @endif

                @clinicCan('visits.edit')<a
                    href="{{ route('visits.edit', $visit) }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-pencil" aria-hidden="true"></i>
                    {{ __('Edit visit') }}
                </a>@endclinicCan

                <button
                    type="button"
                    class="btn btn-primary"
                    data-print-page
                >
                    <i class="bi bi-printer" aria-hidden="true"></i>
                    {{ __('Print patient slip') }}
                </button>
            </div>
        </div>

        @if (!$visit->doctor)
            <div class="alert alert-warning clh-screen-notice no-print">
                {{ __('No doctor is assigned to this visit. Open Edit visit to select the treating Doctor / Hakeem.') }}
            </div>
        @endif

        <x-clinic-letterhead
            :clinic="$visit->clinic"
            :patient="$visit->patient"
            :practitioner="$visit->doctor"
            :date="$visit->visit_date"
            document-title="Patient / visit slip"
            :reference="'Visit #' . $visit->visit_id"
            :visit-type="$visit->visit_reason"
        >
            <div class="clh-diagnosis-block">
                <h3 class="clh-section-title">{{ __('Tashkhees / Diagnosis') }}</h3>
                <p class="clh-diagnosis">
                    {{ $visit->diagnosis_name ?: '—' }}
                </p>
            </div>

            <h3 class="clh-section-title">{{ __('Clinical notes') }}</h3>
            <p class="clh-notes">{{ $visit->general_notes }}</p>

            <div class="clh-prescription-area">
                <span class="clh-rx" aria-label="{{ __('Prescription') }}">℞</span>

                <div class="clh-nuskha">
                    <h3 class="clh-section-title">{{ __('Prescription') }}</h3>
                </div>
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
                    <h2>{{ __('Visit information') }}</h2>
                    <p>{{ __('Practitioner, calendar and record status.') }}</p>
                </div>
            </div>

            <div class="visit-form-grid">
                <div class="form-field">
                    <span class="form-label d-block">{{ __('Doctor / Hakeem') }}</span>
                    <strong>{{ $visit->doctor?->name ?: 'Not assigned' }}</strong>
                </div>

                <div class="form-field">
                    <span class="form-label d-block">{{ __('Recorded by') }}</span>
                    <strong>{{ $visit->recordedBy?->name ?: '—' }}</strong>
                </div>

                <div class="form-field">
                    <span class="form-label d-block">{{ __('Status') }}</span>
                    <strong>
                        {{ $statusLabels[$visit->status] ?? __('Not recorded') }}
                    </strong>
                </div>

                <div class="form-field">
                    <label for="detail_visit_date" class="form-label">
                        {{ __('Gregorian date') }}
                    </label>

                    <input
                        type="date"
                        id="detail_visit_date"
                        class="form-control"
                        value="{{ $visit->visit_date?->format('Y-m-d') }}"
                        data-visit-date
                        readonly
                    >
                </div>

                <div class="form-field">
                    <label for="detail_visit_day" class="form-label">
                        {{ __('Day') }}
                    </label>

                    <input
                        type="text"
                        id="detail_visit_day"
                        class="form-control"
                        value="{{ $visit->visit_date?->format('l') }}"
                        data-visit-weekday
                        readonly
                    >
                </div>

                <div class="form-field">
                    <label for="detail_hijri_date" class="form-label">
                        {{ __('Hijri date — Umm al-Qura') }}
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