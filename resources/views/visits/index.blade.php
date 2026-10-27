@extends('layouts.app')

@section('title', 'Visits — Salman Dawa Khana')

@section('content')
@php
    $statusLabels = [
        'in_progress' => __('In progress'),
        'completed' => __('Completed'),
        'follow_up_required' => __('Follow-up required'),
        'cancelled' => __('Cancelled'),
    ];

    $dateOptions = [
        'all dates' => __('All dates'),
        'today' => __('Today'),
        'this week' => __('This week'),
        'this month' => __('This month'),
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
    @include('layouts._sidebar')
    
    <main class="dashboard-main">

        <header class="page-header">
            <div>
                <p class="dashboard-date">{{ __('Clinic management') }}</p>
                <h1>{{ __('Visits') }}</h1>
                <p class="page-subtitle">
                    {{ __('Track patient visits and consultation records.') }}
                </p>
            </div>

            @clinicCan('visits.create')<a href="{{ route('visits.create') }}" class="btn btn-primary">
                + {{ __('New visit') }}
            </a>@endclinicCan
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ __(session('success')) }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ __($error) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- VISITS — Summary --}}
        <section class="visit-summary-grid">
            <article class="visit-summary-card">
                <span>{{ __('Today\'s visits') }}</span>
                <strong>{{ $todayVisits }}</strong>
                <small>{{ __('Today\'s patient visits') }}</small>
            </article>

            <article class="visit-summary-card">
                <span>{{ __('This month\'s visits') }}</span>
                <strong>{{ $monthVisits }}</strong>
                <small>{{ \App\Support\ClinicClock::now()->format('F Y') }}</small>
            </article>

            <article class="visit-summary-card">
                <span>{{ __('Total visits') }}</span>
                <strong>{{ $totalVisits }}</strong>
                <small>{{ __('All recorded visits') }}</small>
            </article>

            <article class="visit-summary-card">
                <span>{{ __('Follow-up visits') }}</span>
                <strong>{{ $followUpVisits }}</strong>
                <small>{{ __('Visits recorded as Follow-up') }}</small>
            </article>
        </section>

        {{-- VISITS — Filters and Records --}}
        <section class="visits-panel">

            <div class="visits-toolbar">
                <div>
                    <h2>{{ __('Visit records') }}</h2>
                    <p>{{ __('View and manage patient visit history.') }}</p>
                </div>

                <div class="visits-toolbar-actions flex-wrap">
                    <select
                        name="date_range"
                        class="form-select"
                        form="visit-filter-form"
                        aria-label="{{ __('Filter by date') }}"
                    >
                        @foreach ($dateOptions as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(request('date_range', 'all dates') === $value)
                            >
                                {{ __($label) }}
                            </option>
                        @endforeach
                    </select>

                    <select
                        name="visit_type"
                        class="form-select"
                        form="visit-filter-form"
                        aria-label="{{ __('Filter by visit type') }}"
                    >
                        <option value="all">{{ __('All visit types') }}</option>

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
                        aria-label="{{ __('Filter by status') }}"
                    >
                        <option
                            value="all"
                            @selected(request('status', 'all') === 'all')
                        >
                            {{ __('All statuses') }}
                        </option>

                        <option
                            value="not_recorded"
                            @selected(request('status') === 'not_recorded')
                        >
                            {{ __('Not recorded') }}
                        </option>

                        @foreach ($statusLabels as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(request('status') === $value)
                            >
                                {{ __($label) }}
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
                        placeholder="{{ __('Search by patient name, phone, patient code or visit ID') }}"
                        aria-label="{{ __('Search visits') }}"
                    >
                </div>

                <div class="d-flex gap-2 mb-3">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Search / Apply') }}
                    </button>

                    <a href="{{ route('visits.index') }}" class="btn btn-light">
                        {{ __('Clear') }}
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table visits-table align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Visit ID') }}</th>
                            <th>{{ __('Patient') }}</th>
                            <th>{{ __('Date & Time') }}</th>
                            <th>{{ __('Visit Type') }}</th>
                            <th>{{ __('Phone') }}</th>
                            <th>{{ __('Tashkhees') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Dues') }}</th>
                            <th>{{ __('Bill') }}</th>
                            <th class="text-end">{{ __('Action') }}</th>
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
                                    <bdi class="phone-number" dir="ltr">&#x2066;{{ $visit->patient?->phone ?: '—' }}&#x2069;</bdi>
                                </td>

                                <td>
                                    {{ $visit->diagnosis_name ?: __('Not recorded') }}
                                </td>

                                <td>
                                    @if ($visit->status === 'completed')
                                        <span class="status-badge active-badge">
                                            {{ __('Completed') }}
                                        </span>
                                    @elseif ($visit->status === 'follow_up_required')
                                        <span class="status-badge pending-badge">
                                            {{ __('Follow-up required') }}
                                        </span>
                                    @elseif ($visit->status)
                                        <span class="status-badge">
                                            {{ $statusLabels[$visit->status] ?? $visit->status }}
                                        </span>
                                    @else
                                        <span class="text-muted">
                                            {{ __('Not recorded') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="visit-dues-cell">
                                    @php($patientDues = $patientDuesById[$visit->patient_id] ?? 0)
                                    <strong>{{ $currency }} {{ number_format((float) $patientDues, 2) }}</strong>
                                </td>

                                <td class="visit-bill-cell">
                                    @if ($visit->patient)
                                        @clinicCan('payments.create')
                                            <a
                                                href="{{ route('payments.create', (float) $patientDues > 0
                                                    ? ['mode' => 'existing_bill', 'patient_id' => $visit->patient_id, 'lock_patient' => 1]
                                                    : ['mode' => 'new_bill', 'patient_id' => $visit->patient_id, 'visit_id' => $visit->visit_id, 'lock_patient' => 1]) }}"
                                                class="table-action visit-billing-action"
                                            >
                                                {{ (float) $patientDues > 0 ? __('Record payment') : __('Make bill') }}
                                            </a>
                                        @endclinicCan
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('visits.show', $visit) }}"
                                        class="table-action"
                                    >
                                        {{ __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    {{ __('No visits match the selected filters.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="patients-pagination">
                <span>
                    {{ __('Showing') }} {{ $visits->firstItem() ?? 0 }}
                    {{ __('to') }} {{ $visits->lastItem() ?? 0 }}
                    {{ __('of') }} {{ $visits->total() }} {{ __('visits') }}
                </span>

                <div>{{ $visits->links() }}</div>
            </div>
        </section>
    </main>
</div>
@endsection
