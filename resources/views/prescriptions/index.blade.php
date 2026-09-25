@extends('layouts.app')

@section('title', 'Prescriptions — Salman Dawa Khana')

@section('content')
<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">

        {{-- PRESCRIPTIONS — Page Header --}}
        <header class="page-header">
            <div>
                <p class="dashboard-date">Patient care</p>
                <h1>Prescriptions</h1>
                <p class="page-subtitle">
                    Manage patient nuskhas and prescription records.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a
                    href="{{ route('prescriptions.templates.index') }}"
                    class="btn btn-outline-primary"
                >
                    Nuskha Templates
                </a>

                <a
                    href="{{ route('prescriptions.create') }}"
                    class="btn btn-primary"
                >
                    + Create Prescription
                </a>
            </div>
        </header>

        {{-- PRESCRIPTIONS — Messages --}}
        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Please correct the following:</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- PRESCRIPTIONS — Summary --}}
        <section class="prescription-summary-grid">
            <article class="prescription-summary-card">
                <span>Total prescriptions</span>
                <strong>{{ number_format($totalPrescriptions) }}</strong>
                <small>All clinic records</small>
            </article>

            <article class="prescription-summary-card">
                <span>Created this month</span>
                <strong>{{ number_format($monthlyPrescriptions) }}</strong>
                <small class="stat-success">Current month</small>
            </article>

            <article class="prescription-summary-card">
                <span>Today's prescriptions</span>
                <strong>{{ number_format($todayPrescriptions) }}</strong>
                <small>Today's records</small>
            </article>

            <article class="prescription-summary-card">
                <span>Saved templates</span>
                <strong>{{ number_format($savedTemplates) }}</strong>
                <small>Active reusable nuskha templates</small>
            </article>
        </section>

        {{-- PRESCRIPTIONS — Records --}}
        <section class="prescriptions-panel">

            <div class="prescriptions-toolbar">
                <div>
                    <h2>Prescription records</h2>
                    <p>View and manage patient prescription history.</p>
                </div>

                <div class="prescriptions-toolbar-actions">
                    <select
                        name="date_range"
                        class="form-select"
                        form="prescription-filter-form"
                        aria-label="Filter prescriptions by date"
                    >
                        @foreach ([
                            'all dates' => 'All dates',
                            'today' => 'Today',
                            'this week' => 'This week',
                            'this month' => 'This month',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(request('date_range', 'all dates') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <select
                        name="status"
                        class="form-select"
                        form="prescription-filter-form"
                        aria-label="Filter prescriptions by status"
                    >
                        @foreach ([
                            'all' => 'All statuses',
                            'issued' => 'Issued',
                            'draft' => 'Draft',
                            'cancelled' => 'Cancelled',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(request('status', 'all') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- PRESCRIPTIONS — Server Search --}}
            <form
                method="GET"
                action="{{ route('prescriptions.index') }}"
                id="prescription-filter-form"
                data-server-filters
                data-server-prescription-filters
            >
                <div class="prescription-search">
                    <span aria-hidden="true">⌕</span>

                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        maxlength="255"
                        placeholder="Search by patient name, phone or prescription number"
                        aria-label="Search prescriptions"
                    >
                </div>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        Search / Apply
                    </button>

                    <a
                        href="{{ route('prescriptions.index') }}"
                        class="btn btn-light btn-sm"
                    >
                        Reset
                    </a>
                </div>
            </form>

            {{-- PRESCRIPTIONS — Table --}}
            <div class="table-responsive">
                <table class="table prescriptions-table align-middle">
                    <thead>
                        <tr>
                            <th>Prescription ID</th>
                            <th>Patient</th>
                            <th>Date &amp; time</th>
                            <th>Prepared by</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($prescriptions as $prescription)
                            @php
                                $status = strtolower(
                                    $prescription->status ?? ''
                                );

                                $badgeClass = match ($status) {
                                    'issued' => 'active-badge',
                                    'draft' => 'draft-badge',
                                    'cancelled' => 'bg-danger-subtle text-danger',
                                    default => 'bg-secondary-subtle text-secondary',
                                };
                            @endphp

                            <tr>
                                <td>
                                    <span title="{{ $prescription->prescription_no }}">
                                        #{{ $prescription->prescription_id }}
                                    </span>
                                </td>

                                <td>
                                    <strong>
                                        {{ $prescription->patient?->full_name ?? '—' }}
                                    </strong>

                                    <small class="d-block">
                                        {{ $prescription->patient?->patient_code ?? '—' }}
                                    </small>

                                    @if ($prescription->patient?->phone)
                                        <small class="d-block">
                                            {{ $prescription->patient->phone }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    {{ $prescription->prescribed_at?->format('d M Y') ?? '—' }}

                                    <small class="d-block">
                                        {{ $prescription->prescribed_at?->format('h:i A') ?? '' }}
                                    </small>
                                </td>

                                <td>
                                    {{ $prescription->createdBy?->name ?? '—' }}
                                </td>

                                <td>
                                    {{ $prescription->items_count }}
                                    {{ $prescription->items_count === 1 ? 'item' : 'items' }}
                                </td>

                                <td>
                                    <span class="status-badge {{ $badgeClass }}">
                                        {{ $status !== '' ? ucfirst($status) : 'Unknown' }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ $status === 'draft'
                                            ? route('prescriptions.edit', $prescription)
                                            : route('prescriptions.show', $prescription) }}"
                                        class="table-action"
                                    >
                                        {{ $status === 'draft' ? 'Continue' : 'View' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    No prescriptions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PRESCRIPTIONS — Pagination --}}
            <div class="patients-pagination">
                <span>
                    Showing {{ $prescriptions->firstItem() ?? 0 }}
                    to {{ $prescriptions->lastItem() ?? 0 }}
                    of {{ $prescriptions->total() }} prescriptions
                </span>

                @if ($prescriptions->hasPages())
                    <div>
                        {{ $prescriptions->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>

        </section>
    </main>
</div>
@endsection