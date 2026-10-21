@extends('layouts.app')

@section('title', __('Patients'))

@section('content')
<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">{{ __('Clinic management') }}</p>
                <h1>{{ __('Patients') }}</h1>
                <p class="page-subtitle">
                    {{ __('Manage your patient records and visit history.') }}
                </p>
            </div>

            @clinicCan('patients.create')<a href="{{ route('patients.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle" aria-hidden="true"></i>
                {{ __('Add patient') }}
            </a>@endclinicCan
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="status">
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

        <section class="patient-stats-grid">
            <article class="patient-stat-card">
                <span class="patient-stat-label">{{ __('Total patients') }}</span>
                <strong>{{ number_format($totalPatients) }}</strong>
                <small>{{ __('All registered patients') }}</small>
            </article>

            <article class="patient-stat-card">
                <span class="patient-stat-label">{{ __('Active patients') }}</span>
                <strong>{{ number_format($activePatients) }}</strong>
                <small class="stat-success">{{ __('Currently active records') }}</small>
            </article>

            <article class="patient-stat-card">
                <span class="patient-stat-label">{{ __('New this month') }}</span>
                <strong>{{ number_format($newPatientsThisMonth) }}</strong>
                <small class="stat-success">{{ __('Recently registered') }}</small>
            </article>

            <article class="patient-stat-card">
                <span class="patient-stat-label">{{ __('Outstanding balance') }}</span>
                <strong>
                    {{ $currency }}
                    {{ number_format($outstandingBalance, $moneyDecimals) }}
                </strong>
                <small class="stat-warning">
                    {{ __('Outstanding') }} {{ $currency }} {{ __('patient bills') }}
                </small>
            </article>
        </section>

        <section class="patients-panel">
            <div class="patients-toolbar">
                <div>
                    <h2>{{ __('All patients') }}</h2>
                    <p>
                        {{ __('Balances and CSV amounts show') }} {{ $currency }} {{ __('bills only. Other currencies are not added to these balances.') }}
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
                        {{ __('Export CSV') }}
                    </button>

                    @clinicCan('patients.create')<a
                        href="{{ route('patients.create') }}"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-plus-circle" aria-hidden="true"></i>
                        {{ __('New patient') }}
                    </a>@endclinicCan
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
                        placeholder="{{ __('Name, phone, patient code, CNIC or Emirates ID') }}"
                        aria-label="{{ __('Search patients') }}"
                        maxlength="160"
                    >
                </div>

                <select
                    name="status"
                    class="form-select"
                    aria-label="{{ __('Patient status') }}"
                >
                    <option value="all" @selected(request('status', 'all') === 'all')>
                        {{ __('All statuses') }}
                    </option>

                    <option value="active" @selected(request('status') === 'active')>
                        {{ __('Active') }}
                    </option>

                    <option value="inactive" @selected(request('status') === 'inactive')>
                        {{ __('Inactive') }}
                    </option>
                </select>

                <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>

                <a href="{{ route('patients.index') }}" class="btn btn-light">
                    {{ __('Clear') }}
                </a>
            </form>

            <div class="table-responsive">
                <table class="table patients-table align-middle" id="patients-table">
                    <thead>
                        <tr>
                            <th>{{ __('Patient ID') }}</th>
                            <th>{{ __('Patient name') }}</th>
                            <th>{{ __('Phone number') }}</th>
                            <th>{{ __('Last visit') }}</th>
                            <th>{{ __('Balance (') }}{{ $currency }})</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Action') }}</th>
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
                                            {{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="status-badge due-badge">
                                            {{ __('Inactive') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('patients.show', $patient) }}"
                                        class="table-action"
                                    >
                                        {{ __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    {{ __('No patients match these filters.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="patients-pagination">
                <span>
                    {{ __('Showing') }} {{ $patients->firstItem() ?? 0 }}
                    {{ __('to') }} {{ $patients->lastItem() ?? 0 }}
                    {{ __('of') }} {{ $patients->total() }} {{ __('patients') }}
                </span>
                <div>{{ $patients->links() }}</div>
            </div>
        </section>
    </main>
</div>
@endsection