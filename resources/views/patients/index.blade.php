@extends('layouts.app')

@section('title', 'Patients — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">+</div>
            <span>Salman Dawa Khana</span>
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
                <span>₨</span>
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

        <header class="page-header">
            <div>
                <p class="dashboard-date">Clinic management</p>

                <h1>Patients</h1>

                <p class="page-subtitle">
                    Manage your patient records and visit history.
                </p>
            </div>

            <a href="{{ route('patients.create') }}" class="btn btn-primary">
                + Add Patient
            </a>
        </header>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <section class="patient-stats-grid">
            <article class="patient-stat-card">
                <span class="patient-stat-label">Total Patients</span>
                <strong>{{ number_format($patients->total()) }}</strong>
                <small>All registered patients</small>
            </article>

            <article class="patient-stat-card">
                <span class="patient-stat-label">Active Patients</span>
                <strong>{{ number_format($activePatients ?? 0) }}</strong>
                <small class="stat-success">Currently active records</small>
            </article>

            <article class="patient-stat-card">
                <span class="patient-stat-label">New This Month</span>
                <strong>{{ number_format($newPatientsThisMonth ?? 0) }}</strong>
                <small class="stat-success">Recently registered</small>
            </article>

            <article class="patient-stat-card">
                <span class="patient-stat-label">Outstanding Balance</span>
                <strong>
                    PKR {{ number_format($outstandingBalance ?? 0, 2) }}
                </strong>
                <small class="stat-warning">Patient account balance</small>
            </article>
        </section>

        <section class="patients-panel">

            <div class="patients-toolbar">
                <div>
                    <h2>All patients</h2>
                    <p>Search and manage patient records.</p>
                </div>

                <div class="patients-toolbar-actions">
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-export-table="patients-table"
                    >
                        Export
                    </button>

                    <a href="{{ route('patients.create') }}" class="btn btn-primary">
                        + New Patient
                    </a>
                </div>
            </div>

            <form
                method="GET"
                action="{{ route('patients.index') }}"
                class="patient-filters"
            >
                <div class="patient-search">
                    <span>⌕</span>

                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by name, phone or patient ID"
                    >
                </div>

                <select name="status" class="form-select">
                    <option
                        value="all"
                        {{ request('status', 'all') === 'all' ? 'selected' : '' }}
                    >
                        All statuses
                    </option>

                    <option
                        value="active"
                        {{ request('status') === 'active' ? 'selected' : '' }}
                    >
                        Active
                    </option>

                    <option
                        value="inactive"
                        {{ request('status') === 'inactive' ? 'selected' : '' }}
                    >
                        Inactive
                    </option>
                </select>

                <button
                    type="button"
                    class="btn btn-light filter-clear"
                    data-clear-patient-filters
                >
                    Clear
                </button>
            </form>

            <div class="table-responsive">
                <table class="table patients-table align-middle" id="patients-table">
                    <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>Patient Name</th>
                            <th>Phone Number</th>
                            <th>Last Visit</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($patients as $patient)
                            @php
                                $balance = data_get($patient, 'balance_due', 0);
                            @endphp

                            <tr>
                                <td>
                                    {{ $patient->patient_id }}
                                </td>

                                <td>
                                    <strong>{{ $patient->full_name }}</strong>

                                    <small>
                                        {{ $patient->gender ?? '—' }}
                                    </small>
                                </td>

                                <td>
                                    {{ $patient->phone ?? '—' }}
                                </td>

                                <td>
                                    {{ optional($patient->updated_at)->format('d M Y') ?? '—' }}
                                </td>

                                <td class="{{ $balance > 0 ? 'balance-due' : '' }}">
                                    PKR {{ number_format($balance, 2) }}
                                </td>

                                <td>
                                    @if($patient->status === 'active')
                                        <span class="status-badge active-badge">
                                            Active
                                        </span>
                                    @else
                                        <span class="status-badge due-badge">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('patients.show', $patient) }}"
                                        class="table-action"
                                    >
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    No patients found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="patients-pagination">
                <span>
                    Showing {{ $patients->firstItem() ?? 0 }}
                    to {{ $patients->lastItem() ?? 0 }}
                    of {{ $patients->total() }} patients
                </span>

                <div>
                    {{ $patients->links() }}
                </div>
            </div>

        </section>

    </main>
</div>
@endsection