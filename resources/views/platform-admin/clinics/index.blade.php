@extends('platform-admin.layouts.app')

@section('title', 'Clinics')
@section('page_title', 'Clinics')

@section('content')
    <div class="pa-page-heading">
        <div>
            <h1 class="pa-page-title">Clinics</h1>

            <p class="pa-page-description">
                Review registrations and manage clinic approvals.
            </p>
        </div>
    </div>

    <section class="pa-stats" aria-label="Clinic statistics">
        <article class="pa-stat">
            <p class="pa-stat-label">Total clinics</p>
            <p class="pa-stat-value">{{ number_format($stats['total']) }}</p>
            <p class="pa-stat-note">All registered clinics</p>
        </article>

        <article class="pa-stat">
            <p class="pa-stat-label">Pending review</p>
            <p class="pa-stat-value">{{ number_format($stats['pending']) }}</p>
            <p class="pa-stat-note">Awaiting your decision</p>
        </article>

        <article class="pa-stat">
            <p class="pa-stat-label">Approved</p>
            <p class="pa-stat-value">{{ number_format($stats['approved']) }}</p>
            <p class="pa-stat-note">Approved clinics</p>
        </article>

        <article class="pa-stat">
            <p class="pa-stat-label">Rejected</p>
            <p class="pa-stat-value">{{ number_format($stats['rejected']) }}</p>
            <p class="pa-stat-note">Declined registrations</p>
        </article>
    </section>

    <section class="pa-panel" aria-labelledby="clinics-list-title">
        <header class="pa-panel-header">
            <div>
                <h2 class="pa-panel-title" id="clinics-list-title">
                    Clinic directory
                </h2>

                <p class="pa-panel-description">
                    Search by clinic, contact name, email or phone.
                </p>
            </div>
        </header>

        <form
            method="GET"
            action="{{ route('platform-admin.clinics.index') }}"
            class="pa-filter-form"
        >
            <div class="pa-field pa-filter-search">
                <label for="clinic-search">Search</label>

                <input
                    id="clinic-search"
                    type="search"
                    name="search"
                    class="pa-input"
                    value="{{ $filters['search'] }}"
                    placeholder="Clinic name, contact, email or phone"
                >
            </div>

            <div class="pa-field">
                <label for="onboarding-status">Approval status</label>

                <select
                    id="onboarding-status"
                    name="onboarding_status"
                    class="pa-input"
                >
                    <option value="all"
                        @selected($filters['onboarding_status'] === 'all')
                    >
                        All statuses
                    </option>

                    <option value="pending_review"
                        @selected($filters['onboarding_status'] === 'pending_review')
                    >
                        Pending review
                    </option>

                    <option value="approved"
                        @selected($filters['onboarding_status'] === 'approved')
                    >
                        Approved
                    </option>

                    <option value="rejected"
                        @selected($filters['onboarding_status'] === 'rejected')
                    >
                        Rejected
                    </option>
                </select>
            </div>

            <div class="pa-filter-actions">
                <button type="submit" class="pa-button pa-button-primary">
                    Apply filters
                </button>

                <a
                    href="{{ route('platform-admin.clinics.index') }}"
                    class="pa-button"
                >
                    Reset
                </a>
            </div>
        </form>

        @if($clinics->isEmpty())
            <div class="pa-empty">
                <p class="pa-empty-title">No clinics found</p>

                <p class="pa-empty-description">
                    Try another search or approval status.
                </p>
            </div>
        @else
            <div class="pa-table-wrap">
                <table class="pa-table">
                    <thead>
                        <tr>
                            <th scope="col">Clinic</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Approval</th>
                            <th scope="col">Registered</th>
                            <th scope="col">Action</th>
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
                                        Clinic #{{ $clinic->clinic_id }}
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
                                        {{ $clinic->phone ?? $clinic->customer_phone ?? 'No phone' }}
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
                                        View details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pa-list-footer">
                <span>
                    Showing {{ $clinics->firstItem() }}
                    to {{ $clinics->lastItem() }}
                    of {{ $clinics->total() }} clinics
                </span>

                @if($clinics->hasPages())
                    <nav
                        class="pa-pagination"
                        aria-label="Clinic pages"
                    >
                        @if($clinics->onFirstPage())
                            <span class="is-disabled">Previous</span>
                        @else
                            <a href="{{ $clinics->previousPageUrl() }}">
                                Previous
                            </a>
                        @endif

                        <span>
                            Page {{ $clinics->currentPage() }}
                            of {{ $clinics->lastPage() }}
                        </span>

                        @if($clinics->hasMorePages())
                            <a href="{{ $clinics->nextPageUrl() }}">
                                Next
                            </a>
                        @else
                            <span class="is-disabled">Next</span>
                        @endif
                    </nav>
                @endif
            </div>
        @endif
    </section>
@endsection