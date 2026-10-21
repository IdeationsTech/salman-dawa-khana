@extends('platform-admin.layouts.app')

@section('title', __('Clinics'))
@section('page_title', 'Clinics')

@section('content')
    <div class="pa-page-heading">
        <div>
            <h1 class="pa-page-title">{{ __('Clinics') }}</h1>

            <p class="pa-page-description">
                {{ __('Review registrations and manage clinic approvals.') }}
            </p>
        </div>
    </div>

    <section class="pa-stats" aria-label="{{ __('Clinic statistics') }}">
        <article class="pa-stat">
            <p class="pa-stat-label">{{ __('Total clinics') }}</p>
            <p class="pa-stat-value">{{ number_format($stats['total']) }}</p>
            <p class="pa-stat-note">{{ __('All registered clinics') }}</p>
        </article>

        <article class="pa-stat">
            <p class="pa-stat-label">{{ __('Pending review') }}</p>
            <p class="pa-stat-value">{{ number_format($stats['pending']) }}</p>
            <p class="pa-stat-note">{{ __('Awaiting your decision') }}</p>
        </article>

        <article class="pa-stat">
            <p class="pa-stat-label">{{ __('Approved') }}</p>
            <p class="pa-stat-value">{{ number_format($stats['approved']) }}</p>
            <p class="pa-stat-note">{{ __('Approved clinics') }}</p>
        </article>

        <article class="pa-stat">
            <p class="pa-stat-label">{{ __('Rejected') }}</p>
            <p class="pa-stat-value">{{ number_format($stats['rejected']) }}</p>
            <p class="pa-stat-note">{{ __('Declined registrations') }}</p>
        </article>
    </section>

    <section class="pa-panel" aria-labelledby="clinics-list-title">
        <header class="pa-panel-header">
            <div>
                <h2 class="pa-panel-title" id="clinics-list-title">
                    {{ __('Clinic directory') }}
                </h2>

                <p class="pa-panel-description">
                    {{ __('Search by clinic, contact name, email or phone.') }}
                </p>
            </div>
        </header>

        <form
            method="GET"
            action="{{ route('platform-admin.clinics.index') }}"
            class="pa-filter-form"
        >
            <div class="pa-field pa-filter-search">
                <label for="clinic-search">{{ __('Search') }}</label>

                <input
                    id="clinic-search"
                    type="search"
                    name="search"
                    class="pa-input"
                    value="{{ $filters['search'] }}"
                    placeholder="{{ __('Clinic name, contact, email or phone') }}"
                >
            </div>

            <div class="pa-field">
                <label for="onboarding-status">{{ __('Approval status') }}</label>

                <select
                    id="onboarding-status"
                    name="onboarding_status"
                    class="pa-input"
                >
                    <option value="all"
                        @selected($filters['onboarding_status'] === 'all')
                    >
                        {{ __('All statuses') }}
                    </option>

                    <option value="pending_review"
                        @selected($filters['onboarding_status'] === 'pending_review')
                    >
                        {{ __('Pending review') }}
                    </option>

                    <option value="approved"
                        @selected($filters['onboarding_status'] === 'approved')
                    >
                        {{ __('Approved') }}
                    </option>

                    <option value="rejected"
                        @selected($filters['onboarding_status'] === 'rejected')
                    >
                        {{ __('Rejected') }}
                    </option>
                </select>
            </div>

            <div class="pa-filter-actions">
                <button type="submit" class="pa-button pa-button-primary">
                    {{ __('Apply filters') }}
                </button>

                <a
                    href="{{ route('platform-admin.clinics.index') }}"
                    class="pa-button"
                >
                    {{ __('Reset') }}
                </a>
            </div>
        </form>

        @if($clinics->isEmpty())
            <div class="pa-empty">
                <p class="pa-empty-title">{{ __('No clinics found') }}</p>

                <p class="pa-empty-description">
                    {{ __('Try another search or approval status.') }}
                </p>
            </div>
        @else
            <div class="pa-table-wrap">
                <table class="pa-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Clinic') }}</th>
                            <th scope="col">{{ __('Customer') }}</th>
                            <th scope="col">{{ __('Contact') }}</th>
                            <th scope="col">{{ __('Approval') }}</th>
                            <th scope="col">{{ __('Registered') }}</th>
                            <th scope="col">{{ __('Action') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($clinics as $clinic)
                            <tr>
                                <td>
                                    <span class="pa-cell-title">
                                        {{ $clinic->name }}
                                    </span>

                                    <span class="pa-cell-subtitle">
                                        {{ __('Clinic #') }}{{ $clinic->clinic_id }}
                                    </span>
                                </td>

                                <td>
                                    <span class="pa-cell-title">
                                        {{ $clinic->contact_name ?? 'Not linked' }}
                                    </span>

                                    @if($clinic->company_name)
                                        <span class="pa-cell-subtitle">
                                            {{ $clinic->company_name }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <span class="pa-cell-title">
                                        {{ $clinic->customer_email ?? '—' }}
                                    </span>

                                    <span class="pa-cell-subtitle">
                                        <bdi class="phone-number" dir="ltr">&#x2066;{{ $clinic->phone ?? $clinic->customer_phone ?? __('No phone') }}&#x2069;</bdi>
                                    </span>
                                </td>

                                <td>
                                    <span @class([
                                        'pa-badge',
                                        'pa-badge-attention' =>
                                            $clinic->onboarding_status === 'pending_review',
                                    ])>
                                        {{ \Illuminate\Support\Str::headline($clinic->onboarding_status) }}
                                    </span>
                                </td>

                                <td class="pa-nowrap">
                                    {{ \Illuminate\Support\Carbon::parse($clinic->created_at)->format('d M Y') }}
                                </td>

                                <td>
                                    <a
                                        class="pa-link"
                                        href="{{ route('platform-admin.clinics.show', $clinic->clinic_id) }}"
                                    >
                                        {{ __('View details') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pa-list-footer">
                <span>
                    {{ __('Showing') }} {{ $clinics->firstItem() }}
                    {{ __('to') }} {{ $clinics->lastItem() }}
                    {{ __('of') }} {{ $clinics->total() }} {{ __('clinics') }}
                </span>

                @if($clinics->hasPages())
                    <nav
                        class="pa-pagination"
                        aria-label="{{ __('Clinic pages') }}"
                    >
                        @if($clinics->onFirstPage())
                            <span class="is-disabled">{{ __('Previous') }}</span>
                        @else
                            <a href="{{ $clinics->previousPageUrl() }}">
                                {{ __('Previous') }}
                            </a>
                        @endif

                        <span>
                            {{ __('Page') }} {{ $clinics->currentPage() }}
                            {{ __('of') }} {{ $clinics->lastPage() }}
                        </span>

                        @if($clinics->hasMorePages())
                            <a href="{{ $clinics->nextPageUrl() }}">
                                {{ __('Next') }}
                            </a>
                        @else
                            <span class="is-disabled">{{ __('Next') }}</span>
                        @endif
                    </nav>
                @endif
            </div>
        @endif
    </section>
@endsection