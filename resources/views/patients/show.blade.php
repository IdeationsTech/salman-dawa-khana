@extends('layouts.app')

@section('title', $patient->full_name . ' — Patient Profile')

@section('content')
@php
    $nameParts = preg_split(
        '/\s+/u',
        trim($patient->full_name),
        -1,
        PREG_SPLIT_NO_EMPTY
    );

    $initials = collect($nameParts)
        ->take(2)
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->implode('');

    $initials = mb_strtoupper($initials);

    $age = $patient->date_of_birth
        ? (int) $patient->date_of_birth->diffInYears(now('Asia/Dubai'))
        : null;

    $status = strtolower((string) $patient->status);

    $statusClass = $status === 'active'
        ? 'active-badge'
        : 'inactive-badge';

    $maritalStatus = match ($patient->marital_status) {
        'married' => 'Married',
        'unmarried' => 'Unmarried',
        default => 'Not recorded',
    };

    $childrenLabel = 'Not recorded';

    if ($patient->marital_status === 'married') {
        if ($patient->has_children === true) {
            $childrenLabel = 'Yes — ' . $patient->children_count;
        } elseif ($patient->has_children === false) {
            $childrenLabel = 'No';
        }
    } elseif ($patient->marital_status === 'unmarried') {
        $childrenLabel = 'Not applicable';
    }
@endphp

<div class="app-shell">

    {{-- PATIENT PROFILE — Sidebar --}}
    <aside class="app-sidebar no-print">
        <div class="sidebar-brand">
            <div class="sidebar-logo">+</div>

            <span>
                {{ $patient->clinic?->name ?? 'Salman Dawa Khana' }}
            </span>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="sidebar-link">
                <span>▦</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('patients.index') }}" class="sidebar-link active">
                <span>♙</span>
                <span>Patients</span>
            </a>

            <a href="{{ route('visits.index') }}" class="sidebar-link">
                <span>▣</span>
                <span>Visits</span>
            </a>

            <a href="{{ route('prescriptions.index') }}" class="sidebar-link">
                <span>✎</span>
                <span>Prescriptions</span>
            </a>

            <a href="{{ route('payments.index') }}" class="sidebar-link">
                <span>▤</span>
                <span>Payments</span>
            </a>

            <a href="{{ route('expenses.index') }}" class="sidebar-link">
                <span>◈</span>
                <span>Expenses</span>
            </a>

            <div class="sidebar-divider"></div>

            <a href="{{ route('reports.index') }}" class="sidebar-link">
                <span>◌</span>
                <span>Reports</span>
            </a>

            <a href="{{ route('settings.index') }}" class="sidebar-link">
                <span>⚙</span>
                <span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    <main class="dashboard-main">

        <div class="form-page-header no-print">
            <a href="{{ route('patients.index') }}" class="back-link">
                ← Back to patients
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success no-print" role="alert">
                {{ session('success') }}
            </div>
        @endif

        {{-- PATIENT PROFILE — Identity --}}
        <section class="patient-profile-header">
            <div class="patient-profile-identity">
                <div class="large-patient-avatar">
                    {{ $initials ?: '?' }}
                </div>

                <div>
                    <div class="patient-id-label">
                        PATIENT CODE: {{ $patient->patient_code }}
                    </div>

                    <h1>{{ $patient->full_name }}</h1>

                    <p>
                        {{ $patient->gender ?: 'Gender not recorded' }}

                        @if($age !== null)
                            · {{ $age }} years
                        @endif

                        @if($patient->phone)
                            · {{ $patient->phone }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="patient-profile-actions no-print">
                <a
                    href="{{ route('patients.edit', $patient) }}"
                    class="btn btn-outline-secondary"
                >
                    Edit patient
                </a>

                <a
                    href="{{ route('visits.create', [
                        'patient_id' => $patient->patient_id,
                    ]) }}"
                    class="btn btn-primary"
                >
                    + New visit
                </a>
            </div>
        </section>

        <section class="patient-profile-grid">
            <div class="patient-profile-main">

                {{-- PATIENT PROFILE — Personal Information --}}
                <div class="patient-detail-panel">
                    <div class="profile-panel-heading">
                        <div>
                            <h2>Personal information</h2>
                            <p>Registered patient details</p>
                        </div>

                        <span class="status-badge {{ $statusClass }}">
                            {{ $status !== '' ? ucfirst($status) : 'Not recorded' }}
                        </span>
                    </div>

                    <div class="detail-grid">
                        <div>
                            <span class="detail-label">Full name</span>
                            <strong>{{ $patient->full_name }}</strong>
                        </div>

                        <div>
                            <span class="detail-label">Patient code</span>
                            <strong>{{ $patient->patient_code }}</strong>
                        </div>

                        <div>
                            <span class="detail-label">Father / husband name</span>

                            <strong>
                                {{ $patient->father_or_husband_name ?: 'Not recorded' }}
                            </strong>
                        </div>

                        <div>
                            <span class="detail-label">Phone number</span>
                            <strong>{{ $patient->phone ?: 'Not recorded' }}</strong>
                        </div>

                        <div>
                            <span class="detail-label">Emirates ID</span>

                            <strong>
                                {{ $patient->emirates_id ?: 'Not recorded' }}
                            </strong>
                        </div>

                        <div>
                            <span class="detail-label">Date of birth</span>

                            <strong>
                                {{ $patient->date_of_birth?->format('d F Y') ?? 'Not recorded' }}
                            </strong>
                        </div>

                        <div>
                            <span class="detail-label">Gender</span>
                            <strong>{{ $patient->gender ?: 'Not recorded' }}</strong>
                        </div>

                        <div>
                            <span class="detail-label">Marital status</span>
                            <strong>{{ $maritalStatus }}</strong>
                        </div>

                        <div>
                            <span class="detail-label">Children</span>
                            <strong>{{ $childrenLabel }}</strong>
                        </div>

                        <div>
                            <span class="detail-label">Registered on</span>

                            <strong>
                                {{ $patient->created_at?->format('d F Y') ?? 'Not recorded' }}
                            </strong>
                        </div>

                        <div>
                            <span class="detail-label">Address</span>

                            <strong>
                                {{ $patient->address ?: 'Not recorded' }}
                            </strong>
                        </div>
                    </div>
                </div>

                {{-- PATIENT PROFILE — Notes --}}
                @if($patient->notes)
                    <div class="patient-detail-panel">
                        <div class="profile-panel-heading">
                            <div>
                                <h2>Patient notes</h2>
                            </div>
                        </div>

                        <p class="mb-0">
                            {!! nl2br(e($patient->notes)) !!}
                        </p>
                    </div>
                @endif

                {{-- PATIENT PROFILE — Visit History --}}
                <div class="patient-detail-panel" id="patient-visits">
                    <div class="profile-panel-heading">
                        <div>
                            <h2>Visit history</h2>
                            <p>Latest five visits for this patient</p>
                        </div>
                    </div>

                    <div class="visit-history-list">
                        @forelse($recentVisits as $visit)
                            @php
                                $visitDate = $visit->visit_date
                                    ? \Carbon\Carbon::parse($visit->visit_date)
                                    : null;

                                $visitStatus = $visit->status
                                    ? ucfirst(str_replace('_', ' ', $visit->status))
                                    : 'Status not recorded';
                            @endphp

                            <div class="visit-history-item">
                                <div class="visit-date">
                                    <strong>
                                        {{ $visitDate?->format('d') ?? '—' }}
                                    </strong>

                                    <span>
                                        {{ $visitDate ? strtoupper($visitDate->format('M')) : '—' }}
                                    </span>
                                </div>

                                <div class="visit-info">
                                    <strong>
                                        {{ $visit->visit_reason ?: 'Visit' }}
                                    </strong>

                                    <span>
                                        {{ $visitDate?->format('d M Y, h:i A') ?? 'Date not recorded' }}
                                    </span>

                                    @if($visit->diagnosis_name)
                                        <span>
                                            Diagnosis: {{ $visit->diagnosis_name }}
                                        </span>
                                    @endif

                                    <span>{{ $visitStatus }}</span>
                                </div>

                                <a
                                    href="{{ route('visits.show', $visit) }}"
                                    class="panel-link no-print"
                                >
                                    View
                                </a>
                            </div>
                        @empty
                            <p class="text-muted mb-0">
                                No visits recorded for this patient.
                            </p>
                        @endforelse
                    </div>
                </div>

                {{-- PATIENT PROFILE — Prescriptions --}}
                <div class="patient-detail-panel" id="patient-prescriptions">
                    <div class="profile-panel-heading">
                        <div>
                            <h2>Prescriptions</h2>
                            <p>Latest five prescriptions for this patient</p>
                        </div>
                    </div>

                    <div class="visit-history-list">
                        @forelse($recentPrescriptions as $prescription)
                            @php
                                $prescriptionDate = $prescription->prescribed_at
                                    ? \Carbon\Carbon::parse($prescription->prescribed_at)
                                    : null;
                            @endphp

                            <div class="visit-history-item">
                                <div class="visit-date">
                                    <strong>
                                        {{ $prescriptionDate?->format('d') ?? '—' }}
                                    </strong>

                                    <span>
                                        {{ $prescriptionDate
                                            ? strtoupper($prescriptionDate->format('M'))
                                            : '—' }}
                                    </span>
                                </div>

                                <div class="visit-info">
                                    <strong>{{ $prescription->prescription_no }}</strong>

                                    <span>
                                        {{ $prescriptionDate?->format('d M Y') ?? 'Date not recorded' }}
                                    </span>

                                    <span>
                                        {{ ucfirst($prescription->status ?: 'Not recorded') }}
                                    </span>
                                </div>

                                <a
                                    href="{{ route('prescriptions.show', $prescription) }}"
                                    class="panel-link no-print"
                                >
                                    View
                                </a>
                            </div>
                        @empty
                            <p class="text-muted mb-0">
                                No prescriptions recorded for this patient.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- PATIENT PROFILE — Account Summary --}}
            <aside class="patient-profile-side">
                <div class="patient-detail-panel account-summary-panel">
                    <div class="profile-panel-heading">
                        <div>
                            <h2>Account summary</h2>
                            <p>This patient's current bill balances</p>
                        </div>
                    </div>

                    <div class="account-total">
                        <span>Outstanding bills</span>

                        <strong>
                            {{ $currency }}
                            {{ number_format($outstandingBalance, 2) }}
                        </strong>
                    </div>

                    <div class="account-row">
                        <span>Total billed</span>

                        <strong>
                            {{ $currency }}
                            {{ number_format($totalBilled, 2) }}
                        </strong>
                    </div>

                    <div class="account-row">
                        <span>Paid against bills</span>

                        <strong>
                            {{ $currency }}
                            {{ number_format($paidAgainstBills, 2) }}
                        </strong>
                    </div>

                    @if((float) $unallocatedPayments > 0)
                        <div class="account-row">
                            <span>Unallocated payments</span>

                            <strong>
                                {{ $currency }}
                                {{ number_format($unallocatedPayments, 2) }}
                            </strong>
                        </div>

                        <small class="text-muted d-block mt-2">
                            Unallocated payments have not been deducted
                            from outstanding bills.
                        </small>
                    @endif

                    <a
                        href="{{ route('payments.create', [
                            'mode' => 'new_bill',
                            'patient_id' => $patient->patient_id,
                        ]) }}"
                        class="btn btn-primary w-100 mt-3 no-print"
                    >
                        New bill + payment
                    </a>

                    @if($outstandingBalance > 0)
                        <a
                            href="{{ route('payments.create', [
                                'mode' => 'existing_bill',
                                'patient_id' => $patient->patient_id,
                            ]) }}"
                            class="btn btn-outline-secondary w-100 mt-2 no-print"
                        >
                            Receive outstanding payment
                        </a>
                    @endif
                </div>

                {{-- PATIENT PROFILE — Quick Actions --}}
                <div class="patient-detail-panel no-print">
                    <div class="profile-panel-heading">
                        <div>
                            <h2>Quick actions</h2>
                        </div>
                    </div>

                    <div class="profile-quick-actions">
                        <a href="#patient-prescriptions">
                            Recent prescriptions <span>›</span>
                        </a>

                        <a href="{{ route('visits.create', [
                            'patient_id' => $patient->patient_id,
                        ]) }}">
                            Create new visit <span>›</span>
                        </a>
                    </div>

                    <button
                        type="button"
                        class="btn btn-outline-secondary w-100 mt-3"
                        data-print-page
                    >
                        Print patient summary
                    </button>
                </div>
            </aside>
        </section>
    </main>
</div>
@endsection