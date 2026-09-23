@extends('layouts.app')

@section('title', 'Visits — Salman Dawa Khana')

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

            <a href="{{ route('visits.index') }}" class="sidebar-link active">
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

                <h1>Visits</h1>

                <p class="page-subtitle">
                    Track patient visits and consultation records.
                </p>
            </div>

            <a href="{{ route('visits.create') }}" class="btn btn-primary">
                + New Visit
            </a>
        </header>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <section class="visit-summary-grid">
            <article class="visit-summary-card">
                <span>Today's visits</span>

                <strong>
                    {{ $todayVisits ?? 0 }}
                </strong>

                <small class="stat-success">
                    Today's patient visits
                </small>
            </article>

            <article class="visit-summary-card">
                <span>This month's visits</span>

                <strong>
                    {{ $monthVisits ?? 0 }}
                </strong>

                <small>
                    {{ now()->format('F Y') }}
                </small>
            </article>

            <article class="visit-summary-card">
                <span>Completed visits</span>

                <strong>
                    {{ $completedVisits ?? 0 }}
                </strong>

                <small class="stat-success">
                    Completed records
                </small>
            </article>

            <article class="visit-summary-card">
                <span>Follow-ups</span>

                <strong>
                    {{ $followUpVisits ?? 0 }}
                </strong>

                <small class="stat-warning">
                    Require attention
                </small>
            </article>
        </section>

        <section class="visits-panel">

            <div class="visits-toolbar">
                <div>
                    <h2>Visit records</h2>

                    <p>
                        View and manage patient visit history.
                    </p>
                </div>

                <div class="visits-toolbar-actions">

                    <select
                        name="date_range"
                        class="form-select"
                        form="visit-filter-form"
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
                        form="visit-filter-form"
                    >
                        <option value="all">
                            All statuses
                        </option>

                        <option
                            value="completed"
                            {{ request('status') === 'completed' ? 'selected' : '' }}
                        >
                            Completed
                        </option>

                        <option
                            value="follow-up"
                            {{ request('status') === 'follow-up' ? 'selected' : '' }}
                        >
                            Follow-up
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
                action="{{ route('visits.index') }}"
                id="visit-filter-form"
            >
                <div class="visit-search">
                    <span>⌕</span>

                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by patient name or visit ID"
                    >
                </div>
            </form>

            <div class="table-responsive">
                <table class="table visits-table align-middle">
                    <thead>
                        <tr>
                            <th>Visit ID</th>
                            <th>Patient</th>
                            <th>Date &amp; Time</th>
                            <th>Visit Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($visits as $visit)
                            <tr>
                                <td>
                                    {{ $visit->visit_id }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $visit->patient_id }}
                                    </strong>

                                    <small>
                                        Patient ID
                                    </small>
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') }}

                                    <br>

                                    <small>
                                        {{ \Carbon\Carbon::parse($visit->visit_date)->format('h:i A') }}
                                    </small>
                                </td>

                                <td>
                                    {{ $visit->visit_type ?? 'General visit' }}
                                </td>

                                <td>
                                    PKR {{ number_format($visit->amount ?? 0, 2) }}
                                </td>

                                <td>
                                    @if(strtolower($visit->status) === 'follow-up')
                                        <span class="status-badge due-badge">
                                            Follow-up
                                        </span>
                                    @else
                                        <span class="status-badge active-badge">
                                            {{ ucfirst($visit->status ?? 'Completed') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('visits.show', $visit) }}"
                                        class="table-action"
                                    >
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    No visits found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="patients-pagination">
                <span>
                    Showing {{ $visits->firstItem() ?? 0 }}
                    to {{ $visits->lastItem() ?? 0 }}
                    of {{ $visits->total() }} visits
                </span>

                <div>
                    {{ $visits->links() }}
                </div>
            </div>

        </section>

    </main>
</div>
@endsection