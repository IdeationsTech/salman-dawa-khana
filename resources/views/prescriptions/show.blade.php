@extends('layouts.app')

@section('title', 'Prescription Details — Salman Dawa Khana')

@section('content')
<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        @if (session('success'))
            <div class="alert alert-success no-print" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="prescription-detail-toolbar no-print">
            <a href="{{ route('prescriptions.index') }}" class="back-link">
                ← Back to prescriptions
            </a>

            <div class="prescription-toolbar-actions">
                <a
                    href="{{ route('prescriptions.edit', $prescription) }}"
                    class="btn btn-outline-secondary"
                >
                    Edit prescription
                </a>

                <button type="button" class="btn btn-primary" data-print-page>
                    Print / Save PDF
                </button>
            </div>
        </div>

        <article class="prescription-document">
            <header class="prescription-document-header">
                <div class="prescription-brand">
                    <div class="prescription-brand-mark">+</div>

                    <div>
                        <h1>
                            {{ $prescription->clinic?->name ?? 'Salman Dawa Khana' }}
                        </h1>
                        <p>Patient Care &amp; Herbal Wellness</p>
                    </div>
                </div>

                <div class="prescription-document-meta">
                    <span>PRESCRIPTION</span>
                    <strong>{{ $prescription->prescription_no }}</strong>
                    <small>
                        {{ $prescription->prescribed_at->format('d F Y, h:i A') }}
                    </small>
                    <small>Status: {{ ucfirst($prescription->status) }}</small>
                </div>
            </header>

            <div class="prescription-document-divider"></div>

            <section class="prescription-patient-info">
                <div>
                    <span>Patient name</span>
                    <strong>{{ $prescription->patient?->full_name ?? '—' }}</strong>
                </div>

                <div>
                    <span>Patient ID</span>
                    <strong>{{ $prescription->patient?->patient_code ?? '—' }}</strong>
                </div>

                <div>
                    <span>Phone</span>
                    <strong>{{ $prescription->patient?->phone ?: '—' }}</strong>
                </div>

                <div>
                    <span>Visit</span>
                    <strong>
                        @if ($prescription->visit)
                            #{{ $prescription->visit->visit_id }}
                            — {{ $prescription->visit->visit_reason }}
                        @else
                            No linked visit
                        @endif
                    </strong>
                </div>
            </section>

            @if ($prescription->visit?->diagnosis_name)
                <section class="prescription-instructions">
                    <h2>Tashkhees</h2>
                    <p>{{ $prescription->visit->diagnosis_name }}</p>
                </section>
            @endif

            <section class="prescription-document-section">
                <div class="document-section-heading">
                    <span class="document-section-number">Rx</span>
                    <h2>Prescribed nuskha</h2>
                </div>

                <div class="prescription-items-table p-3">
                    <p class="mb-0 fw-semibold fs-5">
                        {{ $prescription->nuskha_name ?: 'Custom nuskha' }}
                    </p>
                </div>
            </section>

            <section class="prescription-instructions">
                <h2>Patient instructions</h2>
                <p>{!! nl2br(e(
                    $prescription->general_instructions
                        ?: 'No additional instructions.'
                )) !!}</p>
            </section>

            <footer class="prescription-document-footer">
                <div>
                    <span>Prepared by</span>
                    <strong>{{ $prescription->createdBy?->name ?? '—' }}</strong>
                </div>

                <div class="signature-area">
                    <span>Signature</span>
                    <div></div>
                </div>
            </footer>

            <div class="prescription-print-note">
                Prescription status: {{ ucfirst($prescription->status) }}.
            </div>
        </article>
    </main>
</div>
@endsection