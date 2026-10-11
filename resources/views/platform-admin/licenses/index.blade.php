@extends('platform-admin.layouts.app')

@section('title', 'Licenses')
@section('page_title', 'Licenses')

@section('content')
    <div class="pa-page-heading">
        <div>
            <h1 class="pa-page-title">
                Clinic licenses
            </h1>

            <p class="pa-page-description">
                Current licenses, expiry dates and clinic access status.
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="pa-notice pa-notice-error" role="alert">
            <strong>Please correct the following:</strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="pa-panel">
        <header class="pa-panel-header">
            <div>
                <h2 class="pa-panel-title">
                    Current licenses
                </h2>

                <p class="pa-panel-description">
                    {{ $licenses->total() }} matching records
                </p>
            </div>
        </header>

        <div class="pa-panel-body">
            <form
                method="GET"
                action="{{ route('platform-admin.licenses.index') }}"
                class="pa-form"
            >
                <div class="pa-form-grid">
                    <div class="pa-field">
                        <label for="license-search">
                            Search
                        </label>

                        <input
                            id="license-search"
                            type="search"
                            name="search"
                            class="pa-input"
                            maxlength="160"
                            placeholder="Clinic, customer, email or license ID"
                            value="{{ $filters['search'] }}"
                        >
                    </div>

                    <div class="pa-field">
                        <label for="license-status">
                            Status
                        </label>

                        <select
                            id="license-status"
                            name="status"
                            class="pa-input"
                        >
                            <option
                                value="all"
                                @selected($filters['status'] === 'all')
                            >
                                All statuses
                            </option>

                            <option
                                value="active"
                                @selected($filters['status'] === 'active')
                            >
                                Active
                            </option>

                            <option
                                value="expired"
                                @selected($filters['status'] === 'expired')
                            >
                                Expired
                            </option>

                            <option
                                value="expiring"
                                @selected($filters['status'] === 'expiring')
                            >
                                Expiring within 7 days
                            </option>

                            <option
                                value="suspended"
                                @selected($filters['status'] === 'suspended')
                            >
                                Suspended clinics
                            </option>
                        </select>
                    </div>

                    <div class="pa-field">
                        <label for="license-plan">
                            Plan
                        </label>

                        <select
                            id="license-plan"
                            name="plan"
                            class="pa-input"
                        >
                            <option
                                value="all"
                                @selected($filters['plan'] === 'all')
                            >
                                All plans
                            </option>

                            <option
                                value="trial_7d"
                                @selected($filters['plan'] === 'trial_7d')
                            >
                                7-Day Trial
                            </option>

                            <option
                                value="monthly"
                                @selected($filters['plan'] === 'monthly')
                            >
                                Monthly
                            </option>

                            <option
                                value="yearly"
                                @selected($filters['plan'] === 'yearly')
                            >
                                Yearly
                            </option>

                            <option
                                value="lifetime"
                                @selected($filters['plan'] === 'lifetime')
                            >
                                Lifetime
                            </option>
                        </select>
                    </div>
                </div>

                <div class="pa-license-filter-actions">
                    <button
                        type="submit"
                        class="pa-button pa-button-primary"
                    >
                        Apply filters
                    </button>

                    <a
                        href="{{ route('platform-admin.licenses.index') }}"
                        class="pa-button"
                    >
                        Clear
                    </a>
                </div>
            </form>
        </div>

        @if($licenses->isEmpty())
            <div class="pa-empty">
                <p class="pa-empty-title">
                    No licenses found
                </p>

                <p class="pa-empty-description">
                    Try changing the search or filters.
                </p>
            </div>
        @else
            <div class="pa-license-table-wrap">
                <table class="pa-table">
                    <thead>
                        <tr>
                            <th>License</th>
                            <th>Clinic</th>
                            <th>Plan</th>
                            <th>Grant</th>
                            <th>Starts</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($licenses as $license)
                            <tr>
                                <td>
                                    #{{ $license->clinic_license_id }}
                                </td>

                                <td>
                                    <span class="pa-cell-title">
                                        {{ $license->clinic_name }}
                                    </span>

                                    <span class="pa-cell-subtitle">
                                        {{ $license->customer_email ?? 'No customer email' }}
                                    </span>
                                </td>

                                <td>{{ $license->plan_name }}</td>

                                <td>
                                    {{ \Illuminate\Support\Str::headline($license->grant_type) }}
                                </td>

                                <td>
                                    {{ \Illuminate\Support\Carbon::parse($license->starts_at)->format('d M Y') }}
                                </td>

                                <td>
                                    {{ $license->ends_at
                                        ? \Illuminate\Support\Carbon::parse($license->ends_at)->format('d M Y')
                                        : 'No expiry' }}
                                </td>

                                <td>
                                    <span @class([
                                        'pa-badge',
                                        'pa-badge-attention' =>
                                            in_array(
                                                $license->display_status,
                                                ['expired', 'suspended'],
                                                true
                                            ),
                                    ])>
                                        {{ \Illuminate\Support\Str::headline($license->display_status) }}
                                    </span>
                                </td>

                                <td>
                                    <a
                                        href="{{ route('platform-admin.clinics.show', $license->clinic_id) }}"
                                        class="pa-back-link"
                                    >
                                        View clinic
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($licenses->hasPages())
                <div class="pa-panel-body">
                    {{ $licenses->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @endif
    </section>
@endsection