@extends('layouts.app')

@section('title', 'Prescription Details — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- PRESCRIPTION DETAIL — Sidebar --}}
    <aside class="app-sidebar no-print">
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
            <a href="{{ url('/visits') }}" class="sidebar-link">
                <span>▣</span><span>Visits</span>
            </a>
            <a href="{{ url('/prescriptions') }}" class="sidebar-link active">
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

                <button
                    type="button"
                    class="btn btn-primary"
                    data-print-page
                >
                    Print / Save PDF
                </button>
            </div>
        </div>

        <article class="prescription-document">

            {{-- PRESCRIPTION DETAIL — Header --}}
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
                    <small>
                        Status: {{ ucfirst($prescription->status) }}
                    </small>
                </div>
            </header>

            <div class="prescription-document-divider"></div>

            {{-- PRESCRIPTION DETAIL — Patient --}}
            <section class="prescription-patient-info">
                <div>
                    <span>Patient name</span>
                    <strong>
                        {{ $prescription->patient?->full_name ?? '—' }}
                    </strong>
                </div>

                <div>
                    <span>Patient ID</span>
                    <strong>
                        {{ $prescription->patient?->patient_code ?? '—' }}
                    </strong>
                </div>

                <div>
                    <span>Phone</span>
                    <strong>
                        {{ $prescription->patient?->phone ?: '—' }}
                    </strong>
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

            {{-- PRESCRIPTION DETAIL — Items --}}
            <section class="prescription-document-section">
                <div class="document-section-heading">
                    <span class="document-section-number">Rx</span>
                    <h2>Prescribed nuskha</h2>
                </div>

                <div class="prescription-items-table">
                    <div class="prescription-table-row prescription-table-header">
                        <span>#</span>
                        <span>Item / ingredient</span>
                        <span>Dosage</span>
                        <span>Schedule and instructions</span>
                    </div>

                    @forelse ($prescription->items as $item)
                        <div class="prescription-table-row">
                            <span>{{ $loop->iteration }}</span>
                            <strong>{{ $item->item_name }}</strong>
                            <span>{{ $item->dosage ?: '—' }}</span>

                            <div>
                                @if ($item->frequency)
                                    <div>Frequency: {{ $item->frequency }}</div>
                                @endif

                                @if ($item->duration)
                                    <div>Duration: {{ $item->duration }}</div>
                                @endif

                                @if ($item->timing)
                                    <div>Timing: {{ $item->timing }}</div>
                                @endif

                                @if ($item->instructions)
                                    <div>{{ $item->instructions }}</div>
                                @endif

                                @if (
                                    !$item->frequency &&
                                    !$item->duration &&
                                    !$item->timing &&
                                    !$item->instructions
                                )
                                    —
                                @endif
                            </div>
                        </div>
                    @empty
                        <p>No prescription items recorded.</p>
                    @endforelse
                </div>
            </section>

            {{-- PRESCRIPTION DETAIL — Instructions --}}
            <section class="prescription-instructions">
                <h2>Patient instructions</h2>
                <p>{!! nl2br(e($prescription->general_instructions ?: 'No additional instructions.')) !!}</p>
            </section>

            <footer class="prescription-document-footer">
                <div>
                    <span>Prepared by</span>
                    <strong>
                        {{ $prescription->createdBy?->name ?? '—' }}
                    </strong>
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