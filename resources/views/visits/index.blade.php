@extends('layouts.app')

@section('title', 'Visits — Salman Dawa Khana')

@section('content')
@php
    $statusLabels = [
        'in_progress' => 'In progress',
        'completed' => 'Completed',
        'follow_up_required' => 'Follow-up required',
        'cancelled' => 'Cancelled',
    ];

    $dateOptions = [
        'all dates' => 'All dates',
        'today' => 'Today',
        'this week' => 'This week',
        'this month' => 'This month',
    ];

    $visitTypes = [
        'General visit',
        'Follow-up',
        'New consultation',
        'Prescription refill',
    ];
@endphp

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

        {{-- VISITS — Filters and Records --}}
        <section class="visits-panel">

            <div class="visits-toolbar">
                <div>
                    <h2>Visit records</h2>
                    <p>View and manage patient visit history.</p>
                </div>

                <div class="visits-toolbar-actions flex-wrap">
                    <select
                        name="date_range"
                        class="form-select"
                        form="visit-filter-form"
                        aria-label="Filter by date"
                    >
                        @foreach ($dateOptions as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(request('date_range', 'all dates') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <select
                        name="visit_type"
                        class="form-select"
                        form="visit-filter-form"
                        aria-label="Filter by visit type"
                    >
                        <option value="all">All visit types</option>

                        @foreach ($visitTypes as $type)
                            <option
                                value="{{ $type }}"
                                @selected(request('visit_type') === $type)
                            >
                                {{ $type }}
                            </option>
                        @endforeach
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
                            value="not_recorded"
                            @selected(request('status') === 'not_recorded')
                        >
                            Not recorded
                        </option>

                        @foreach ($statusLabels as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(request('status') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <form
                method="GET"
                action="{{ route('visits.index') }}"
                id="visit-filter-form"
                data-server-visit-filters
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

                <div class="d-flex gap-2 mb-3">
                    <button type="submit" class="btn btn-primary">
                        Search / Apply
                    </button>

                    <a href="{{ route('visits.index') }}" class="btn btn-light">
                        Clear
                    </a>
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

                                <td>{{ $visit->visit_reason ?: '—' }}</td>

                                <td>
                                    {{ $visit->patient?->phone ?: '—' }}
                                </td>

                                <td>
                                    {{ $visit->diagnosis_name ?: 'Not recorded' }}
                                </td>

                                <td>
                                    @if ($visit->status === 'completed')
                                        <span class="status-badge active-badge">
                                            Completed
                                        </span>
                                    @elseif ($visit->status === 'follow_up_required')
                                        <span class="status-badge due-badge">
                                            Follow-up required
                                        </span>
                                    @elseif ($visit->status)
                                        <span class="status-badge">
                                            {{ $statusLabels[$visit->status] ?? $visit->status }}
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
                                    No visits match the selected filters.
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

                <div>{{ $visits->links() }}</div>
            </div>
        </section>
    </main>
</div>
@endsection