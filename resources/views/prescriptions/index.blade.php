@extends('layouts.app')

@section('title', 'Prescriptions — Salman Dawa Khana')

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

            <a href="{{ route('patients.index') }}" class="sidebar-link">
                <span>♙</span>
                <span>Patients</span>
            </a>

            <a href="{{ route('visits.index') }}" class="sidebar-link">
                <span>▣</span>
                <span>Visits</span>
            </a>

            <a href="{{ route('prescriptions.index') }}" class="sidebar-link active">
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
                <p class="dashboard-date">Patient care</p>

                <h1>Prescriptions</h1>

                <p class="page-subtitle">
                    Manage patient nuskhas and prescription records.
                </p>
            </div>

            <a href="{{ route('prescriptions.create') }}" class="btn btn-primary">
                + Create Prescription
            </a>
        </header>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <section class="prescription-summary-grid">
            <article class="prescription-summary-card">
                <span>Total prescriptions</span>

                <strong>
                    {{ number_format($totalPrescriptions ?? $prescriptions->total()) }}
                </strong>

                <small>All clinic records</small>
            </article>

            <article class="prescription-summary-card">
                <span>Created this month</span>

                <strong>
                    {{ number_format($monthlyPrescriptions ?? 0) }}
                </strong>

                <small class="stat-success">
                    Current month
                </small>
            </article>

            <article class="prescription-summary-card">
                <span>Today's prescriptions</span>

                <strong>
                    {{ number_format($todayPrescriptions ?? 0) }}
                </strong>

                <small>
                    Today's records
                </small>
            </article>

            <article class="prescription-summary-card">
                <span>Saved templates</span>

                <strong>
                    {{ number_format($savedTemplates ?? 0) }}
                </strong>

                <small>
                    Reusable nuskha templates
                </small>
            </article>
        </section>

        <section class="prescriptions-panel">

            <div class="prescriptions-toolbar">
                <div>
                    <h2>Prescription records</h2>

                    <p>
                        View and manage patient prescription history.
                    </p>
                </div>

                <div class="prescriptions-toolbar-actions">

                    <select
                        name="date_range"
                        class="form-select"
                        form="prescription-filter-form"
                    >
                        <option value="all dates">
                            All dates
                        </option>

                        <option
                            value="today"
                            {{ request('date_range') === 'today' ? 'selected' : '' }}
                        >
                            Today
                        </option>

                        <option
                            value="this week"
                            {{ request('date_range') === 'this week' ? 'selected' : '' }}
                        >
                            This week
                        </option>

                        <option
                            value="this month"
                            {{ request('date_range') === 'this month' ? 'selected' : '' }}
                        >
                            This month
                        </option>
                    </select>

                    <select
                        name="status"
                        class="form-select"
                        form="prescription-filter-form"
                    >
                        <option value="all">
                            All statuses
                        </option>

                        <option
                            value="issued"
                            {{ request('status') === 'issued' ? 'selected' : '' }}
                        >
                            Issued
                        </option>

                        <option
                            value="draft"
                            {{ request('status') === 'draft' ? 'selected' : '' }}
                        >
                            Draft
                        </option>

                        <option
                            value="cancelled"
                            {{ request('status') === 'cancelled' ? 'selected' : '' }}
                        >
                            Cancelled
                        </option>
                    </select>
                </div>
            </div>

            <form
                method="GET"
                action="{{ route('prescriptions.index') }}"
                id="prescription-filter-form"
            >
                <div class="prescription-search">
                    <span>⌕</span>

                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by patient or prescription ID"
                    >
                </div>
            </form>

            <div class="table-responsive">
                <table class="table prescriptions-table align-middle">
                    <thead>
                        <tr>
                            <th>Prescription ID</th>
                            <th>Patient</th>
                            <th>Date</th>
                            <th>Prepared by</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($prescriptions as $prescription)
                            @php
                                $status = strtolower($prescription->status ?? 'issued');
                                $itemsCount = data_get($prescription, 'items_count', 0);
                            @endphp

                            <tr>
                                <td>
                                    {{ $prescription->prescription_id }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $prescription->patient_id }}
                                    </strong>

                                    <small>
                                        Patient ID
                                    </small>
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($prescription->prescription_date)->format('d M Y') }}
                                </td>

                                <td>
                                    {{ $prescription->created_by ?? 'Clinic staff' }}
                                </td>

                                <td>
                                    {{ $itemsCount }} items
                                </td>

                                <td>
                                    @if($status === 'draft')
                                        <span class="status-badge draft-badge">
                                            Draft
                                        </span>
                                    @else
                                        <span class="status-badge active-badge">
                                            {{ ucfirst($status) }}
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('prescriptions.show', $prescription) }}"
                                        class="table-action"
                                    >
                                        {{ $status === 'draft' ? 'Continue' : 'View' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    No prescriptions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="patients-pagination">
                <span>
                    Showing {{ $prescriptions->firstItem() ?? 0 }}
                    to {{ $prescriptions->lastItem() ?? 0 }}
                    of {{ $prescriptions->total() }} prescriptions
                </span>

                <div>
                    {{ $prescriptions->links() }}
                </div>
            </div>

        </section>

    </main>
</div>
@endsection