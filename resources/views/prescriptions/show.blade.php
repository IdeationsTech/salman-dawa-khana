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
                {{ __(session('success')) }}
            </div>
        @endif

        <div class="clh-toolbar no-print">
            <a
                href="{{ route('prescriptions.index') }}"
                class="back-link"
            >
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                {{ __('Back to prescriptions') }}
            </a>

            <div class="clh-toolbar-actions">
                @clinicCan('prescriptions.edit')<a
                    href="{{ route('prescriptions.edit', $prescription) }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-pencil" aria-hidden="true"></i>
                    {{ __('Edit prescription') }}
                </a>@endclinicCan

                <button
                    type="button"
                    class="btn btn-primary"
                    data-print-page
                >
                    <i class="bi bi-printer" aria-hidden="true"></i>
                    {{ __('Print / Save PDF') }}
                </button>
            </div>
        </div>

        @if (!$prescription->doctor)
            <div class="alert alert-warning clh-screen-notice no-print">
                {{ __('No doctor is assigned to this prescription. Open Edit prescription to select the treating Doctor / Hakeem.') }}
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
                <h3 class="clh-section-title">{{ __('Tashkhees / Diagnosis') }}</h3>

                <p class="clh-diagnosis">
                    {{ $prescription->visit?->diagnosis_name ?: '—' }}
                </p>
            </div>

            <div class="clh-prescription-area">
                <span class="clh-rx" aria-label="{{ __('Prescription') }}">℞</span>

                <div class="clh-nuskha">
                    <h3 class="clh-section-title">{{ __('Prescribed nuskhay') }}</h3>

                    <table class="clh-nuskha-table">
                        <thead>
                            <tr>
                                <th>{{ __('Nuskha') }}</th>
                                <th>{{ __('Total quantity') }}</th>
                                <th>{{ __('Dosage / directions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($prescription->nuskhas as $nuskha)
                                <tr>
                                    <td>
                                        <strong>
                                            {{ $loop->iteration }}. {{ $nuskha->nuskha_name }}
                                        </strong>
                                    </td>
                                    <td class="clh-nuskha-quantity">
                                        {{ rtrim(rtrim((string) $nuskha->quantity, '0'), '.') }} {{ $nuskha->unit }}
                                    </td>
                                    <td>
                                        <div class="clh-nuskha-directions">
                                            @if ($nuskha->frequency)
                                                <span><b>{{ __('Frequency') }}:</b> {{ $nuskha->frequency }}</span>
                                            @endif
                                            @if ($nuskha->duration)
                                                <span><b>{{ __('Duration') }}:</b> {{ $nuskha->duration }}</span>
                                            @endif
                                            @if ($nuskha->timing)
                                                <span><b>{{ __('Timing') }}:</b> {{ $nuskha->timing }}</span>
                                            @endif
                                            @if ($nuskha->instructions)
                                                <span>{{ $nuskha->instructions }}</span>
                                            @endif
                                            @if (!$nuskha->frequency && !$nuskha->duration && !$nuskha->timing && !$nuskha->instructions)
                                                <span>—</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                {{-- Legacy ingredient rows must never appear in a patient copy. --}}
                                <tr>
                                    <td>
                                        <strong>{{ $prescription->nuskha_name ?: 'Nuskha name not recorded' }}</strong>
                                    </td>
                                    <td>—</td>
                                    <td>—</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <h3 class="clh-section-title">{{ __('Patient instructions') }}</h3>

            <p class="clh-instructions">{{ $prescription->general_instructions }}</p>

            <p class="clh-follow-up">
                Follow-up: ____________________
            </p>
        </x-clinic-letterhead>

        <section class="clh-screen-details no-print">
            <div class="visit-form-panel">
                <div class="visit-form-heading">
                    <div>
                        <h2>{{ __('Prescription information') }}</h2>
                        <p>{{ __('Practitioner and record details.') }}</p>
                    </div>
                </div>

                <div class="visit-form-grid">
                    <div class="form-field">
                        <span class="form-label d-block">{{ __('Doctor / Hakeem') }}</span>
                        <strong>
                            {{ $prescription->doctor?->name ?: 'Not assigned' }}
                        </strong>
                    </div>

                    <div class="form-field">
                        <span class="form-label d-block">{{ __('Recorded by') }}</span>
                        <strong>
                            {{ $prescription->createdBy?->name ?: '—' }}
                        </strong>
                    </div>

                    <div class="form-field">
                        <span class="form-label d-block">{{ __('Status') }}</span>
                        <strong>
                            {{ ucfirst($prescription->status ?? '—') }}
                        </strong>
                    </div>

                    @if ($prescription->visit)
                        <div class="form-field">
                            <span class="form-label d-block">{{ __('Linked visit') }}</span>

                            <a href="{{ route('visits.show', $prescription->visit) }}">
                                {{ __('Visit #') }}{{ $prescription->visit->visit_id }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </main>
</div>
@endsection
