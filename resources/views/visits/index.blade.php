@extends('layouts.app')

@section('title', 'Visits — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- VISITS — Sidebar --}}
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">+</div>
            <span>Salman Dawa Khana</span>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="sidebar-link">
                <span>▦</span><span>Dashboard</span>
            </a>

            <a href="{{ route('patients.index') }}" class="sidebar-link">
                <span>♙</span><span>Patients</span>
            </a>

            <a href="{{ route('visits.index') }}" class="sidebar-link active">
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

    {{-- VISITS — Main Content --}}
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

        @if (session('success'))
            <div class="alert alert-success" role="alert">
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

        {{-- VISITS — Summary --}}
        <section class="visit-summary-grid">
            <article class="visit-summary-card">
                <span>Today's visits</span>
                <strong>{{ $todayVisits }}</strong>
                <small>Today's patient visits</small>
            </article>

            <article class="visit-summary-card">
                <span>This month's visits</span>
                <strong>{{ $monthVisits }}</strong>
                <small>{{ now('Asia/Dubai')->format('F Y') }}</small>
            </article>

            <article class="visit-summary-card">
                <span>Total visits</span>
                <strong>{{ $totalVisits }}</strong>
                <small>All recorded visits</small>
            </article>

            <article class="visit-summary-card">
                <span>Follow-up visits</span>
                <strong>{{ $followUpVisits }}</strong>
                <small>Visits recorded as Follow-up</small>
            </article>
        </section>

        {{-- VISITS — Records --}}
        <section class="visits-panel">

            <div class="visits-toolbar">
                <div>
                    <h2>Visit records</h2>
                    <p>View and manage patient visit history.</p>
                </div>

                <div class="visits-toolbar-actions">
                    <select
                        name="date_range"
                        class="form-select"
                        form="visit-filter-form"
                        aria-label="Filter by date"
                    >
                        <option
                            value="all dates"
                            @selected(request('date_range', 'all dates') === 'all dates')
                        >
                            All dates
                        </option>

                        <option
                            value="today"
                            @selected(request('date_range') === 'today')
                        >
                            Today
                        </option>

                        <option
                            value="this week"
                            @selected(request('date_range') === 'this week')
                        >
                            This week
                        </option>

                        <option
                            value="this month"
                            @selected(request('date_range') === 'this month')
                        >
                            This month
                        </option>
                    </select>

                    <select
                        name="status"
                        class="form-select"
                        form="visit-filter-form"
                        aria-label="Filter by status"
                    >
                        <option
                            value="all"
                            @selected(request('status', 'all') === 'all')
                        >
                            All statuses
                        </option>

                        <option
                            value="completed"
                            @selected(request('status') === 'completed')
                        >
                            Completed
                        </option>

                        <option
                            value="follow-up"
                            @selected(request('status') === 'follow-up')
                        >
                            Follow-up
                        </option>

                        <option
                            value="cancelled"
                            @selected(request('status') === 'cancelled')
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
                        placeholder="Search by patient name, phone, patient code or visit ID"
                        aria-label="Search visits"
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
                            <th>Phone</th>
                            <th>Tashkhees</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($visits as $visit)
                            <tr>
                                <td>{{ $visit->visit_id }}</td>

                                <td>
                                    <strong>
                                        {{ $visit->patient?->full_name ?? 'Patient unavailable' }}
                                    </strong>

                                    <small>
                                        {{ $visit->patient?->patient_code ?? '—' }}
                                    </small>
                                </td>

                                <td>
                                    {{ $visit->visit_date->format('d M Y') }}

                                    <small>
                                        {{ $visit->visit_date->format('l') }}
                                        ·
                                        {{ $visit->visit_date->format('h:i A') }}
                                    </small>
                                </td>

                                <td>
                                    {{ $visit->visit_reason ?: '—' }}
                                </td>

                                <td>
                                    {{ $visit->patient?->phone ?: '—' }}
                                </td>

                                <td>
                                    {{ $visit->diagnosis_name ?: 'Not recorded' }}
                                </td>

                                <td>
                                    @if ($visit->status)
                                        <span class="status-badge">
                                            {{ ucfirst(str_replace('_', ' ', $visit->status)) }}
                                        </span>
                                    @else
                                        <span class="text-muted">
                                            Not recorded
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
                                <td colspan="8" class="text-center py-4">
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