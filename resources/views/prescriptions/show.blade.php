@extends('layouts.app')

@section('title', 'Prescription Details')

@section('content')
@php
    $prescription->loadMissing([
        'clinic',
        'patient',
        'visit',
        'doctor',
        'createdBy',
        'nuskhas',
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

        @if (!$prescription->doctor)
            <div class="alert alert-warning clh-screen-notice no-print">
                No doctor is assigned to this prescription.
                Open Edit prescription to select the treating Doctor / Hakeem.
            </div>
        @endif

        <x-clinic-letterhead
            :clinic="$prescription->clinic"
            :patient="$prescription->patient"
            :practitioner="$prescription->doctor"
            :date="$prescription->prescribed_at"
            document-title="Prescription"
            :reference="$prescription->prescription_no"
            :visit-type="$prescription->visit?->visit_reason"
        >
            <div class="clh-diagnosis-block">
                <h3 class="clh-section-title">Tashkhees / Diagnosis</h3>

                <p class="clh-diagnosis">
                    {{ $prescription->visit?->diagnosis_name ?: '—' }}
                </p>
            </div>

            <div class="clh-prescription-area">
                <span class="clh-rx" aria-label="Prescription">℞</span>

                <div class="clh-nuskha">
                    <h3 class="clh-section-title">Prescribed nuskhay</h3>

                    @forelse ($prescription->nuskhas as $nuskha)
                        <div class="rx-patient-nuskha">
                            <h3 class="clh-nuskha-name">
                                {{ $loop->iteration }}. {{ $nuskha->nuskha_name }}
                            </h3>
                            <p class="rx-patient-quantity">
                                Total quantity:
                                <strong>{{ rtrim(rtrim((string) $nuskha->quantity, '0'), '.') }} {{ $nuskha->unit }}</strong>
                            </p>
                            @if ($nuskha->frequency || $nuskha->duration || $nuskha->timing)
                                <p class="rx-patient-directions">
                                    @if ($nuskha->frequency)<span>Frequency: {{ $nuskha->frequency }}</span>@endif
                                    @if ($nuskha->duration)<span>Duration: {{ $nuskha->duration }}</span>@endif
                                    @if ($nuskha->timing)<span>Timing: {{ $nuskha->timing }}</span>@endif
                                </p>
                            @endif
                            @if ($nuskha->instructions)
                                <p class="rx-patient-directions">{{ $nuskha->instructions }}</p>
                            @endif
                        </div>
                    @empty
                        {{-- Legacy ingredient rows must never appear in a patient copy. --}}
                        <h3 class="clh-nuskha-name">
                            {{ $prescription->nuskha_name ?: 'Nuskha name not recorded' }}
                        </h3>
                    @endforelse
                </div>
            </div>

            <h3 class="clh-section-title">Patient instructions</h3>

            <p class="clh-instructions">{{ $prescription->general_instructions }}</p>

            <p class="clh-follow-up">
                Follow-up: ____________________
            </p>
        </x-clinic-letterhead>

        <section class="clh-screen-details no-print">
            <div class="visit-form-panel">
                <div class="visit-form-heading">
                    <div>
                        <h2>Prescription information</h2>
                        <p>Practitioner and record details.</p>
                    </div>
                </div>

                <div class="visit-form-grid">
                    <div class="form-field">
                        <span class="form-label d-block">Doctor / Hakeem</span>
                        <strong>
                            {{ $prescription->doctor?->name ?: 'Not assigned' }}
                        </strong>
                    </div>

                    <div class="form-field">
                        <span class="form-label d-block">Recorded by</span>
                        <strong>
                            {{ $prescription->createdBy?->name ?: '—' }}
                        </strong>
                    </div>

                    <div class="form-field">
                        <span class="form-label d-block">Status</span>
                        <strong>
                            {{ ucfirst($prescription->status ?? '—') }}
                        </strong>
                    </div>

                    @if ($prescription->visit)
                        <div class="form-field">
                            <span class="form-label d-block">Linked visit</span>

                            <a href="{{ route('visits.show', $prescription->visit) }}">
                                Visit #{{ $prescription->visit->visit_id }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </main>
</div>
@endsection
