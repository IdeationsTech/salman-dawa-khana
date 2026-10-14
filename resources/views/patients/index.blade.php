@extends('layouts.app')

@section('title', 'Patients')

@section('content')
<div class="app-shell">
    @include('layouts._sidebar')

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
                <i class="bi bi-plus-circle" aria-hidden="true"></i>
                Add patient
            </a>
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="patient-stats-grid">
            <article class="patient-stat-card">
                <span class="patient-stat-label">Total patients</span>
                <strong>{{ number_format($totalPatients) }}</strong>
                <small>All registered patients</small>
            </article>

            <article class="patient-stat-card">
                <span class="patient-stat-label">Active patients</span>
                <strong>{{ number_format($activePatients) }}</strong>
                <small class="stat-success">Currently active records</small>
            </article>

            <article class="patient-stat-card">
                <span class="patient-stat-label">New this month</span>
                <strong>{{ number_format($newPatientsThisMonth) }}</strong>
                <small class="stat-success">Recently registered</small>
            </article>

            <article class="patient-stat-card">
                <span class="patient-stat-label">Outstanding balance</span>
                <strong>
                    {{ $currency }}
                    {{ number_format($outstandingBalance, $moneyDecimals) }}
                </strong>
                <small class="stat-warning">
                    Outstanding {{ $currency }} patient bills
                </small>
            </article>
        </section>

        <section class="patients-panel">
            <div class="patients-toolbar">
                <div>
                    <h2>All patients</h2>
                    <p>
                        Balances and CSV amounts show {{ $currency }} bills only.
                        Other currencies are not added to these balances.
                    </p>
                </div>

                <div class="patients-toolbar-actions">
                    <button
                        type="submit"
                        form="patient-filter-form"
                        name="export"
                        value="csv"
                        class="btn btn-outline-secondary"
                    >
                        <i class="bi bi-download" aria-hidden="true"></i>
                        Export CSV
                    </button>

                    <a
                        href="{{ route('patients.create') }}"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-plus-circle" aria-hidden="true"></i>
                        New patient
                    </a>
                </div>
            </div>

            <form
                id="patient-filter-form"
                method="GET"
                action="{{ route('patients.index') }}"
                class="patient-filters"
                data-server-patient-filters
            >
                <div class="patient-search">
                    <span>
                        <i class="bi bi-search" aria-hidden="true"></i>
                    </span>

                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Name, phone, patient code, CNIC or Emirates ID"
                        aria-label="Search patients"
                        maxlength="160"
                    >
                </div>

                <select
                    name="status"
                    class="form-select"
                    aria-label="Patient status"
                >
                    <option value="all" @selected(request('status', 'all') === 'all')>
                        All statuses
                    </option>

                    <option value="active" @selected(request('status') === 'active')>
                        Active
                    </option>

                    <option value="inactive" @selected(request('status') === 'inactive')>
                        Inactive
                    </option>
                </select>

                <button type="submit" class="btn btn-primary">Search</button>

                <a href="{{ route('patients.index') }}" class="btn btn-light">
                    Clear
                </a>
            </form>

            <div class="table-responsive">
                <table class="table patients-table align-middle" id="patients-table">
                    <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>Patient name</th>
                            <th>Phone number</th>
                            <th>Last visit</th>
                            <th>Balance ({{ $currency }})</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($patients as $patient)
                            <tr>
                                <td>{{ $patient->patient_id }}</td>

                                <td>
                                    <strong>{{ $patient->full_name }}</strong>
                                    <small>{{ $patient->gender ?: '—' }}</small>
                                </td>

                                <td>{{ $patient->phone ?: '—' }}</td>

                                <td>
                                    {{ $patient->last_visit_at?->format('d M Y') ?? '—' }}
                                </td>

                                <td class="{{ $patient->balance_due > 0 ? 'balance-due' : '' }}">
                                    {{ $currency }}
                                    {{ number_format($patient->balance_due, $moneyDecimals) }}
                                </td>

                                <td>
                                    @if ($patient->status === 'active')
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
                                    No patients match these filters.
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
                <div>{{ $patients->links() }}</div>
            </div>
        </section>
    </main>
</div>
@endsection