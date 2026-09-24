@extends('layouts.app')

@section('title', 'Visit Details — Salman Dawa Khana')

@section('content')
@php
    $statusLabels = [
        'in_progress' => 'In progress',
        'completed' => 'Completed',
        'follow_up_required' => 'Follow-up required',
        'cancelled' => 'Cancelled',
    ];
@endphp

<div class="app-shell">

    {{-- VISIT DETAILS — Sidebar --}}
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">+</div>
            <span>Salman Dawa Khana</span>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="sidebar-link">
                <span>▦</span><span>Dashboard</span>
            </a>
            <a href="{{ route('patients.index') }}" class="sidebar-link">
                <span>♙</span><span>Patients</span>
            </a>
            <a href="{{ route('visits.index') }}" class="sidebar-link active">
                <span>▣</span><span>Visits</span>
            </a>
            <a href="{{ route('prescriptions.index') }}" class="sidebar-link">
                <span>✎</span><span>Prescriptions</span>
            </a>
            <a href="{{ route('payments.index') }}" class="sidebar-link">
                <span>₨</span><span>Payments</span>
            </a>
            <a href="{{ route('expenses.index') }}" class="sidebar-link">
                <span>◈</span><span>Expenses</span>
            </a>

            <div class="sidebar-divider"></div>

            <a href="{{ route('reports.index') }}" class="sidebar-link">
                <span>◌</span><span>Reports</span>
            </a>
            <a href="{{ route('settings.index') }}" class="sidebar-link">
                <span>⚙</span><span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    <main class="dashboard-main">

        <div class="form-page-header">
            <a href="{{ route('visits.index') }}" class="back-link">
                ← Back to visits
            </a>
            <h1>Visit #{{ $visit->visit_id }}</h1>
            <p>Patient information and consultation details.</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        {{-- VISIT DETAILS — Patient --}}
        <section class="visit-form-panel">
            <div class="visit-form-heading">
                <div class="form-section-number">01</div>
                <div>
                    <h2>Patient information</h2>
                    <p>Patient linked to this visit.</p>
                </div>
            </div>

            <div class="visit-form-grid">
                <div class="form-field">
                    <span class="form-label d-block">Full Name</span>
                    <strong>
                        {{ $visit->patient?->full_name ?? 'Patient unavailable' }}
                    </strong>
                </div>

                <div class="form-field">
                    <span class="form-label d-block">Patient Code</span>
                    <strong>
                        {{ $visit->patient?->patient_code ?? '—' }}
                    </strong>
                </div>

                <div class="form-field">
                    <span class="form-label d-block">Phone Number</span>
                    <strong>{{ $visit->patient?->phone ?: '—' }}</strong>
                </div>

                <div class="form-field">
                    <span class="form-label d-block">Gender</span>
                    <strong>{{ $visit->patient?->gender ?: '—' }}</strong>
                </div>

                <div class="form-field">
                    <span class="form-label d-block">Emirates ID</span>
                    <strong>{{ $visit->patient?->emirates_id ?: '—' }}</strong>
                </div>

                <div class="form-field">
                    <span class="form-label d-block">Date of Birth</span>
                    <strong>
                        {{ $visit->patient?->date_of_birth?->format('d M Y') ?? '—' }}
                    </strong>
                </div>
            </div>

            @if ($visit->patient)
                <div class="mt-3">
                    <a
                        href="{{ route('patients.show', $visit->patient) }}"
                        class="btn btn-outline-primary"
                    >
                        Open patient profile
                    </a>
                </div>
            @endif
        </section>

        {{-- VISIT DETAILS — Date, Time and Status --}}
        <section class="visit-form-panel" data-visit-calendar-form>
            <div class="visit-form-heading">
                <div class="form-section-number">02</div>
                <div>
                    <h2>Visit details</h2>
                    <p>Date, time and recorded visit information.</p>
                </div>
            </div>

            <div class="visit-form-grid">
                <div class="form-field">
                    <label for="detail_visit_date" class="form-label">
                        Gregorian Date
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
                    <label for="detail_visit_day" class="form-label">Day</label>
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
                        Hijri Date — Umm al-Qura
                    </label>
                    <input
                        type="text"
                        id="detail_hijri_date"
                        class="form-control"
                        placeholder="Calculated from visit date"
                        data-visit-hijri
                        readonly
                    >
                    <small
                        class="form-help"
                        data-visit-calendar-message
                        aria-live="polite"
                    >
                        Calculated from the Gregorian date.
                    </small>
                </div>

                <div class="form-field">
                    <span class="form-label d-block">Visit Time — UAE</span>
                    <strong>{{ $visit->visit_date->format('h:i A') }}</strong>
                </div>

                <div class="form-field">
                    <span class="form-label d-block">Visit Type</span>
                    <strong>{{ $visit->visit_reason ?: '—' }}</strong>
                </div>

                <div class="form-field">
                    <span class="form-label d-block">Status</span>
                    <strong>
                        {{ $statusLabels[$visit->status] ?? 'Not recorded' }}
                    </strong>
                </div>

                <div class="form-field">
                    <span class="form-label d-block">Recorded By</span>
                    <strong>{{ $visit->recordedBy?->name ?? '—' }}</strong>
                </div>
            </div>
        </section>

        {{-- VISIT DETAILS — Tashkhees --}}
        <section class="visit-form-panel">
            <div class="visit-form-heading">
                <div class="form-section-number">03</div>
                <div>
                    <h2>Tashkhees and notes</h2>
                    <p>Information recorded for this visit.</p>
                </div>
            </div>

            <div class="visit-form-grid">
                <div class="form-field visit-field-wide">
                    <span class="form-label d-block">
                        Tashkhees / Illness Name
                    </span>
                    <strong>
                        {{ $visit->diagnosis_name ?: 'Not recorded' }}
                    </strong>
                </div>

                <div class="form-field visit-field-wide">
                    <label for="detail_notes" class="form-label">
                        Visit Notes
                    </label>
                    <textarea
                        id="detail_notes"
                        class="form-control"
                        rows="5"
                        readonly
                    >{{ $visit->general_notes ?: 'No notes recorded.' }}</textarea>
                </div>
            </div>
        </section>

        <div class="visit-form-actions">
            <a href="{{ route('visits.index') }}" class="btn btn-light">
                Back to visits
            </a>
            <a href="{{ route('visits.edit', $visit) }}" class="btn btn-primary">
                Edit visit
            </a>
        </div>
    </main>
</div>
@endsection