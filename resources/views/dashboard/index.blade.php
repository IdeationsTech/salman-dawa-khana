@extends('layouts.app')

@section('title', 'Dashboard — Salman Dawa Khana')

@section('content')
@php
    $currency = strtoupper(
        auth()->user()->clinic->currency ?: 'AED'
    );

    $decimalSetting = \App\Models\Setting::query()
        ->where('clinic_id', auth()->user()->clinic_id)
        ->where('setting_key', 'decimal_places')
        ->value('setting_value');

    $moneyDecimals = (string) $decimalSetting === '0' ? 0 : 2;
@endphp
<div class="app-shell">

    {{-- Dashboard Sidebar --}}
    @include('layouts._sidebar')

    {{-- Dashboard Main Area --}}
    <main class="dashboard-main">

{{-- Dashboard — Dynamic User Topbar --}}
@php
    $signedInUser = auth()->user();

    $initials = collect(
        preg_split('/\s+/u', trim($signedInUser->name))
    )
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

        <header class="dashboard-topbar">
            <div>
                <p class="dashboard-date">
                    {{ \App\Support\ClinicClock::now()->format('l, d F Y') }}
                </p>

                <h1>Welcome, {{ $signedInUser->name }}</h1>
            </div>

            <div class="topbar-user">
                <div class="user-avatar">
                    @if($signedInUser->profile_photo_path)
                        <img
                            src="{{ asset('storage/' . $signedInUser->profile_photo_path) }}"
                            alt="{{ $signedInUser->name }} profile photo"
                            class="topbar-profile-photo"
                        >
                    @else
                        {{ $initials }}
                    @endif
                </div>

                <div>
                    <strong>{{ $signedInUser->name }}</strong>
                    <small>{{ $signedInUser->role?->name ?? 'Staff' }}</small>
                </div>
            </div>
        </header>

        {{-- Summary Cards --}}
        <section class="dashboard-section">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">OVERVIEW</p>
                    <h2>Clinic summary</h2>
                </div>

                @clinicCan('patients.create')<a
                    href="{{ route('patients.create') }}"
                    class="btn btn-primary"
                >
                    + Add Patient
                </a>@endclinicCan
            </div>

            <div class="summary-grid">

                <article class="summary-card">
                    <div class="summary-icon teal-icon"><i class="bi bi-person-vcard app-icon" aria-hidden="true"></i></div>

                    <div>
                        <p>Total Patients</p>

                        <h3>
                            {{ number_format($totalPatients ?? 0) }}
                        </h3>

                        <span class="summary-positive">
                            Registered patients
                        </span>
                    </div>
                </article>

                <article class="summary-card">
                    <div class="summary-icon blue-icon"><i class="bi bi-calendar-check app-icon" aria-hidden="true"></i></div>

                    <div>
                        <p>Today's Visits</p>

                        <h3>
                            {{ number_format($todaysVisits ?? 0) }}
                        </h3>

                        <span class="summary-positive">
                            Current day
                        </span>
                    </div>
                </article>

                <article class="summary-card">
                    <div class="summary-icon orange-icon"><i class="bi bi-cash-coin app-icon" aria-hidden="true"></i></div>

                    <div>
                        <p>Today's Income</p>

                        <h3>
                            {{ $currency }} {{ number_format($todaysIncome ?? 0, $moneyDecimals) }}
                        </h3>

                        <span class="summary-positive">
                            Received payments
                        </span>
                    </div>
                </article>

                <article class="summary-card">
                    <div class="summary-icon purple-icon">◷</div>

                    <div>
                        <p>Outstanding</p>

                        <h3>
                            {{ $currency }}
                            {{ number_format($outstandingBalance ?? 0, $moneyDecimals) }}
                        </h3>

                        <span class="summary-warning">
                            Patient balances due
                        </span>
                    </div>
                </article>

            </div>
        </section>

        {{-- Dashboard Content --}}
        <section class="dashboard-content-grid">

            <div class="dashboard-panel">
                <div class="panel-heading">
                    <div>
                        <h2>Recent patients</h2>
                        <p>Latest patient records added to the clinic.</p>
                    </div>

                    <a
                        href="{{ route('patients.index') }}"
                        class="panel-link"
                    >
                        View all
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table dashboard-table align-middle">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Phone</th>
                                <th>Registered</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($recentPatients ?? [] as $patient)
                                <tr>
                                    <td>
                                        <strong>
                                            {{ $patient->full_name }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ $patient->phone ?? '—' }}
                                    </td>

                                    <td>
                                        {{ optional($patient->created_at)
                                            ->format('d M Y') }}
                                    </td>

                                    <td>
                                        <span class="status-badge active-badge">
                                            Active
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">
                                        No patients found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="panel-heading">
                    <div>
                        <h2>Quick actions</h2>
                        <p>Common clinic tasks.</p>
                    </div>
                </div>

                <div class="quick-actions">
                    @clinicCan('patients.create')<a
                        href="{{ route('patients.create') }}"
                        class="quick-action"
                    >
                        <span class="quick-action-icon"><i class="bi bi-person-vcard app-icon" aria-hidden="true"></i></span>

                        <span>
                            <strong>Add patient</strong>
                            <small>Create a new patient record</small>
                        </span>

                        <span>›</span>
                    </a>@endclinicCan

                    @clinicCan('visits.create')<a
                        href="{{ route('visits.create') }}"
                        class="quick-action"
                    >
                        <span class="quick-action-icon"><i class="bi bi-calendar-check app-icon" aria-hidden="true"></i></span>

                        <span>
                            <strong>Create visit</strong>
                            <small>Record a patient visit</small>
                        </span>

                        <span>›</span>
                    </a>@endclinicCan

                    @clinicCan('prescriptions.create')<a
                        href="{{ route('prescriptions.create') }}"
                        class="quick-action"
                    >
                        <span class="quick-action-icon"><i class="bi bi-prescription app-icon" aria-hidden="true"></i></span>

                        <span>
                            <strong>Create prescription</strong>
                            <small>Prepare a patient nuskha</small>
                        </span>

                        <span>›</span>
                    </a>@endclinicCan

                    @clinicCan('payments.create')<a
                        href="{{ route('payments.create') }}"
                        class="quick-action"
                    >
                        <span class="quick-action-icon"><i class="bi bi-cash-coin app-icon" aria-hidden="true"></i></span>

                        <span>
                            <strong>Record payment</strong>
                            <small>Add cash or bank payment</small>
                        </span>

                        <span>›</span>
                    </a>@endclinicCan
                </div>
            </div>

        </section>

    </main>
</div>
@endsection