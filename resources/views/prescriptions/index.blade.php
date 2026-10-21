@extends('layouts.app')

@section('title', 'Prescriptions — Salman Dawa Khana')

@section('content')
<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">

        {{-- PRESCRIPTIONS — Page Header --}}
        <header class="page-header">
            <div>
                <p class="dashboard-date">{{ __('Patient care') }}</p>
                <h1>{{ __('Prescriptions') }}</h1>
                <p class="page-subtitle">
                    {{ __('Manage patient nuskhas and prescription records.') }}
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @clinicCan('prescriptions.templates.index')<a
                    href="{{ route('prescriptions.templates.index') }}"
                    class="btn btn-outline-primary"
                >
                    {{ __('Nuskha Templates') }}
                </a>@endclinicCan

                @clinicCan('prescriptions.create')<a
                    href="{{ route('prescriptions.create') }}"
                    class="btn btn-primary"
                >
                    + {{ __('Create prescription') }}
                </a>@endclinicCan
            </div>
        </header>

        {{-- PRESCRIPTIONS — Messages --}}
        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ __(session('success')) }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>{{ __('Please correct the following:') }}</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ __($error) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- PRESCRIPTIONS — Summary --}}
        <section class="prescription-summary-grid">
            <article class="prescription-summary-card">
                <span>{{ __('Total prescriptions') }}</span>
                <strong>{{ number_format($totalPrescriptions) }}</strong>
                <small>{{ __('All clinic records') }}</small>
            </article>

            <article class="prescription-summary-card">
                <span>{{ __('Created this month') }}</span>
                <strong>{{ number_format($monthlyPrescriptions) }}</strong>
                <small class="stat-success">{{ __('Current month') }}</small>
            </article>

            <article class="prescription-summary-card">
                <span>{{ __('Today\'s prescriptions') }}</span>
                <strong>{{ number_format($todayPrescriptions) }}</strong>
                <small>{{ __('Today\'s records') }}</small>
            </article>

            <article class="prescription-summary-card">
                <span>{{ __('Saved templates') }}</span>
                <strong>{{ number_format($savedTemplates) }}</strong>
                <small>{{ __('Active reusable nuskha templates') }}</small>
            </article>
        </section>

        {{-- PRESCRIPTIONS — Records --}}
        <section class="prescriptions-panel">

            <div class="prescriptions-toolbar">
                <div>
                    <h2>{{ __('Prescription records') }}</h2>
                    <p>{{ __('View and manage patient prescription history.') }}</p>
                </div>

                <div class="prescriptions-toolbar-actions">
                    <select
                        name="date_range"
                        class="form-select"
                        form="prescription-filter-form"
                        aria-label="{{ __('Filter prescriptions by date') }}"
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
                                {{ __($label) }}
                            </option>
                        @endforeach
                    </select>

                    <select
                        name="status"
                        class="form-select"
                        form="prescription-filter-form"
                        aria-label="{{ __('Filter prescriptions by status') }}"
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
                                {{ __($label) }}
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
                        placeholder="{{ __('Search by patient name, phone or prescription number') }}"
                        aria-label="{{ __('Search prescriptions') }}"
                    >
                </div>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        {{ __('Search / Apply') }}
                    </button>

                    <a
                        href="{{ route('prescriptions.index') }}"
                        class="btn btn-light btn-sm"
                    >
                        {{ __('Reset') }}
                    </a>
                </div>
            </form>

            {{-- PRESCRIPTIONS — Table --}}
            <div class="table-responsive">
                <table class="table prescriptions-table align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Prescription ID') }}</th>
                            <th>{{ __('Patient') }}</th>
                            <th>{{ __('Date & time') }}</th>
                            <th>{{ __('Prepared by') }}</th>
                            <th>{{ __('Nuskhas') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Action') }}</th>
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
                                            <bdi class="phone-number" dir="ltr">&#x2066;{{ $prescription->patient->phone }}&#x2069;</bdi>
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
                                    @php
                                        $nuskhaCount = $prescription->nuskhas_count
                                            ?: ($prescription->nuskha_name ? 1 : 0);
                                    @endphp
                                    {{ $nuskhaCount }}
                                    {{ $nuskhaCount === 1 ? 'nuskha' : 'nuskhas' }}
                                </td>

                                <td>
                                    <span class="status-badge {{ $badgeClass }}">
                                        {{ $status !== '' ? ucfirst($status) : 'Unknown' }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ $status === 'draft' && \App\Support\ClinicPermissions::allows(auth()->user(), 'prescriptions.edit')
                                            ? route('prescriptions.edit', $prescription)
                                            : route('prescriptions.show', $prescription) }}"
                                        class="table-action"
                                    >
                                        {{ $status === 'draft' && \App\Support\ClinicPermissions::allows(auth()->user(), 'prescriptions.edit') ? __('Continue') : __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    {{ __('No prescriptions found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PRESCRIPTIONS — Pagination --}}
            <div class="patients-pagination">
                <span>
                    {{ __('Showing') }} {{ $prescriptions->firstItem() ?? 0 }}
                    {{ __('to') }} {{ $prescriptions->lastItem() ?? 0 }}
                    {{ __('of') }} {{ $prescriptions->total() }} {{ __('prescriptions') }}
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
