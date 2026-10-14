@extends('layouts.app')

@section('title', 'Prescription Details')

@section('content')
@php
    $prescription->loadMissing([
        'clinic',
        'patient',
        'visit',
        'createdBy.role',
    ]);
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
            <a
                href="{{ route('prescriptions.index') }}"
                class="back-link"
            >
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Back to prescriptions
            </a>

            <div class="clh-toolbar-actions">
                <a
                    href="{{ route('prescriptions.edit', $prescription) }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-pencil" aria-hidden="true"></i>
                    Edit prescription
                </a>

                <button
                    type="button"
                    class="btn btn-primary"
                    data-print-page
                >
                    <i class="bi bi-printer" aria-hidden="true"></i>
                    Print / Save PDF
                </button>
            </div>
        </div>

        <x-clinic-letterhead
            :clinic="$prescription->clinic"
            :patient="$prescription->patient"
            :practitioner="$prescription->createdBy"
            :date="$prescription->prescribed_at"
            document-title="Prescription"
            :reference="$prescription->prescription_no"
            :visit-type="$prescription->visit?->visit_reason"
        >
            <h3 class="clh-section-title">Tashkhees / Diagnosis</h3>

            <p class="clh-diagnosis">
                {{ $prescription->visit?->diagnosis_name ?: '—' }}
            </p>

            <h3 class="clh-section-title">Prescribed nuskha</h3>

            <div class="clh-prescription-area">
                <span class="clh-rx" aria-label="Prescription">℞</span>

                <div class="clh-nuskha">
                    <h3>
                        {{ $prescription->nuskha_name ?: 'Custom nuskha' }}
                    </h3>
                </div>
            </div>

            <h3 class="clh-section-title">Patient instructions</h3>

            <p class="clh-instructions">{{ $prescription->general_instructions }}</p>

            <p class="clh-follow-up">
                Follow-up: ____________________
            </p>
        </x-clinic-letterhead>

        <div class="clh-screen-details no-print">
            <p class="text-muted mb-0">
                Prescription status:
                <strong>{{ ucfirst($prescription->status) }}</strong>

                @if ($prescription->visit)
                    · Linked visit:
                    <a
                        href="{{ route('visits.show', $prescription->visit) }}"
                    >
                        #{{ $prescription->visit->visit_id }}
                    </a>
                @endif
            </p>
        </div>
    </main>
</div>
@endsection