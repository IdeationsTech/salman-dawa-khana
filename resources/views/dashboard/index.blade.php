@extends('layouts.app')

@section('title', 'Dashboard — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Dashboard Sidebar --}}
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">+</div>
            <span>Salman Dawa Khana</span>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="sidebar-link active">
                <span>▦</span><span>Dashboard</span>
            </a>

            <a href="{{ route('patients.index') }}" class="sidebar-link">
                <span>♙</span><span>Patients</span>
            </a>

            <a href="{{ route('visits.index') }}" class="sidebar-link">
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

    {{-- Dashboard Main Area --}}
    <main class="dashboard-main">

        {{-- Topbar --}}
        <header class="dashboard-topbar">
            <div>
                <p class="dashboard-date">
                    {{ now()->format('l, d F Y') }}
                </p>

                <h1>Good morning, Dr. Ahmed</h1>
            </div>

            <div class="topbar-user">
                <div class="user-avatar">AK</div>

                <div>
                    <strong>Dr. Ahmed Khan</strong>
                    <small>Clinic Owner</small>
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

                <a
                    href="{{ route('patients.create') }}"
                    class="btn btn-primary"
                >
                    + Add Patient
                </a>
            </div>

            <div class="summary-grid">

                <article class="summary-card">
                    <div class="summary-icon teal-icon">♙</div>

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
                    <div class="summary-icon blue-icon">▣</div>

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
                    <div class="summary-icon orange-icon">₨</div>

                    <div>
                        <p>Today's Income</p>

                        <h3>
                            PKR {{ number_format($todaysIncome ?? 0) }}
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
                            PKR
                            {{ number_format($outstandingBalance ?? 0) }}
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
                    <a
                        href="{{ route('patients.create') }}"
                        class="quick-action"
                    >
                        <span class="quick-action-icon">♙</span>

                        <span>
                            <strong>Add patient</strong>
                            <small>Create a new patient record</small>
                        </span>

                        <span>›</span>
                    </a>

                    <a
                        href="{{ route('visits.create') }}"
                        class="quick-action"
                    >
                        <span class="quick-action-icon">▣</span>

                        <span>
                            <strong>Create visit</strong>
                            <small>Record a patient visit</small>
                        </span>

                        <span>›</span>
                    </a>

                    <a
                        href="{{ route('prescriptions.create') }}"
                        class="quick-action"
                    >
                        <span class="quick-action-icon">✎</span>

                        <span>
                            <strong>Create prescription</strong>
                            <small>Prepare a patient nuskha</small>
                        </span>

                        <span>›</span>
                    </a>

                    <a
                        href="{{ route('payments.create') }}"
                        class="quick-action"
                    >
                        <span class="quick-action-icon">₨</span>

                        <span>
                            <strong>Record payment</strong>
                            <small>Add cash or bank payment</small>
                        </span>

                        <span>›</span>
                    </a>
                </div>
            </div>

        </section>

    </main>
</div>
@endsection