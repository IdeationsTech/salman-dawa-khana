@extends('platform-admin.layouts.app')

@section('title', 'Clinic Details')
@section('page_title', 'Clinic Details')

@section('content')
    @php
        $trialPlan = $plans->firstWhere('plan_code', 'trial_7d');

        $paidPlans = $plans
            ->filter(fn ($plan) => in_array(
                $plan->plan_code,
                ['monthly', 'yearly', 'lifetime'],
                true
            ))
            ->values();

        $canReview =
            $clinic->onboarding_status === 'pending_review'
            && $license === null;
    @endphp

    <div class="pa-page-heading">
        <div>
            <a
                href="{{ route('platform-admin.clinics.index') }}"
                class="pa-back-link"
            >
                ← Back to clinics
            </a>

            <h1 class="pa-page-title">
                {{ $clinic->name }}
            </h1>

            <p class="pa-page-description">
                Clinic #{{ $clinic->clinic_id }}
                · Registered
                {{ \Illuminate\Support\Carbon::parse($clinic->created_at)->format('d M Y') }}
            </p>
        </div>

        <span @class([
            'pa-badge',
            'pa-badge-attention' =>
                $clinic->onboarding_status === 'pending_review',
        ])>
            {{ \Illuminate\Support\Str::headline($clinic->onboarding_status) }}
        </span>
    </div>

    @if(session('success'))
        <div class="pa-notice" role="status">
            {{ session('success') }}
        </div>
    @endif

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

    <div class="pa-detail-grid">
        <section class="pa-panel">
            <header class="pa-panel-header">
                <div>
                    <h2 class="pa-panel-title">
                        Clinic information
                    </h2>
                </div>
            </header>

            <dl class="pa-detail-list">
                <div>
                    <dt>Clinic name</dt>
                    <dd>{{ $clinic->name }}</dd>
                </div>

                <div>
                    <dt>Phone</dt>
                    <dd>{{ $clinic->phone ?? 'Not provided' }}</dd>
                </div>

                <div>
                    <dt>Address</dt>
                    <dd>{{ $clinic->address ?? 'Not provided' }}</dd>
                </div>

                <div>
                    <dt>Clinic currency</dt>
                    <dd>{{ $clinic->currency }}</dd>
                </div>

                <div>
                    <dt>Approval status</dt>
                    <dd>
                        {{ \Illuminate\Support\Str::headline($clinic->onboarding_status) }}
                    </dd>
                </div>

                @if($clinic->reviewed_at)
                    <div>
                        <dt>Reviewed on</dt>

                        <dd>
                            {{ \Illuminate\Support\Carbon::parse($clinic->reviewed_at)->format('d M Y, h:i A') }}
                        </dd>
                    </div>
                @endif

                @if($clinic->rejection_reason)
                    <div>
                        <dt>Rejection reason</dt>
                        <dd>{{ $clinic->rejection_reason }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <section class="pa-panel">
            <header class="pa-panel-header">
                <div>
                    <h2 class="pa-panel-title">
                        Customer contact
                    </h2>
                </div>
            </header>

            <dl class="pa-detail-list">
                <div>
                    <dt>Contact name</dt>
                    <dd>{{ $clinic->contact_name ?? 'Not linked' }}</dd>
                </div>

                <div>
                    <dt>Company</dt>
                    <dd>{{ $clinic->company_name ?? 'Not provided' }}</dd>
                </div>

                <div>
                    <dt>Email</dt>
                    <dd>{{ $clinic->customer_email ?? 'Not provided' }}</dd>
                </div>

                <div>
                    <dt>Phone</dt>

                    <dd>
                        {{ $clinic->customer_phone ?? 'Not provided' }}
                    </dd>
                </div>

                <div>
                    <dt>Country</dt>
                    <dd>{{ $clinic->country_code ?? 'Not provided' }}</dd>
                </div>
            </dl>
        </section>
    </div>

    <section class="pa-panel">
        <header class="pa-panel-header">
            <div>
                <h2 class="pa-panel-title">
                    Current license
                </h2>
            </div>
        </header>

        @if($license)
            <dl class="pa-detail-list pa-detail-list-wide">
                <div>
                    <dt>Plan</dt>
                    <dd>{{ $license->plan_name }}</dd>
                </div>

                <div>
                    <dt>License status</dt>

                    <dd>
                        {{ \Illuminate\Support\Str::headline($license->status) }}
                    </dd>
                </div>

                <div>
                    <dt>Grant type</dt>

                    <dd>
                        {{ \Illuminate\Support\Str::headline($license->grant_type) }}
                    </dd>
                </div>

                <div>
                    <dt>Starts</dt>

                    <dd>
                        {{ \Illuminate\Support\Carbon::parse($license->starts_at)->format('d M Y, h:i A') }}
                    </dd>
                </div>

                <div>
                    <dt>Ends</dt>

                    <dd>
                        {{ $license->ends_at
                            ? \Illuminate\Support\Carbon::parse($license->ends_at)->format('d M Y, h:i A')
                            : 'No expiry date' }}
                    </dd>
                </div>

                @if($license->grant_reason)
                    <div>
                        <dt>Reason</dt>
                        <dd>{{ $license->grant_reason }}</dd>
                    </div>
                @endif
            </dl>
        @else
            <div class="pa-empty">
                <p class="pa-empty-title">
                    No license issued
                </p>

                <p class="pa-empty-description">
                    The clinic does not have a license record yet.
                </p>
            </div>
        @endif
    </section>

    @if($canReview)
        <div class="pa-detail-grid">
            <section class="pa-panel">
                <header class="pa-panel-header">
                    <div>
                        <h2 class="pa-panel-title">
                            Approve registration
                        </h2>

                        <p class="pa-panel-description">
                            Choose a plan after reviewing the clinic.
                        </p>
                    </div>
                </header>

                <div class="pa-panel-body">
                    @if($paidPlans->isNotEmpty())
                        <form
                            method="POST"
                            action="{{ route('platform-admin.clinics.approve', $clinic->clinic_id) }}"
                            class="pa-form"
                        >
                            @csrf

                            <h3 class="pa-form-heading">
                                Verified manual payment
                            </h3>

                            <p class="pa-form-description">
                                Enter the payment you have already
                                received and verified.
                            </p>

                            <div class="pa-field">
                                <label for="paid-plan">
                                    Subscription plan *
                                </label>

                                <select
                                    id="paid-plan"
                                    name="plan_code"
                                    class="pa-input"
                                    required
                                >
                                    <option value="">
                                        Select a plan
                                    </option>

                                    @foreach($paidPlans as $plan)
                                        <option
                                            value="{{ $plan->plan_code }}"
                                            @selected(old('plan_code') === $plan->plan_code)
                                        >
                                            {{ $plan->name }}
                                            — {{ $plan->currency }}
                                            {{ number_format((float) $plan->price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="pa-form-grid">
                                <div class="pa-field">
                                    <label for="payment-method">
                                        Payment method *
                                    </label>

                                    <select
                                        id="payment-method"
                                        name="payment_method"
                                        class="pa-input"
                                        required
                                    >
                                        <option value="">
                                            Select method
                                        </option>

                                        <option value="bank_transfer"
                                            @selected(old('payment_method') === 'bank_transfer')
                                        >
                                            Bank transfer
                                        </option>

                                        <option value="cash"
                                            @selected(old('payment_method') === 'cash')
                                        >
                                            Cash
                                        </option>
                                    </select>
                                </div>

                                <div class="pa-field">
                                    <label for="payment-amount">
                                        Amount received (AED) *
                                    </label>

                                    <input
                                        id="payment-amount"
                                        type="number"
                                        name="amount"
                                        class="pa-input"
                                        min="0.01"
                                        max="9999999999.99"
                                        step="0.01"
                                        value="{{ old('amount') }}"
                                        required
                                    >
                                </div>

                                <div class="pa-field">
                                    <label for="paid-at">
                                        Payment date *
                                    </label>

                                    <input
                                        id="paid-at"
                                        type="date"
                                        name="paid_at"
                                        class="pa-input"
                                        max="{{ now()->toDateString() }}"
                                        value="{{ old('paid_at') }}"
                                        required
                                    >
                                </div>

                                <div class="pa-field">
                                    <label for="reference-no">
                                        Bank/reference number
                                    </label>

                                    <input
                                        id="reference-no"
                                        type="text"
                                        name="reference_no"
                                        class="pa-input"
                                        maxlength="100"
                                        value="{{ old('reference_no') }}"
                                    >
                                </div>
                            </div>

                            <div class="pa-field">
                                <label for="payment-notes">
                                    Payment notes
                                </label>

                                <textarea
                                    id="payment-notes"
                                    name="payment_notes"
                                    class="pa-input"
                                    rows="3"
                                    maxlength="1000"
                                >{{ old('payment_notes') }}</textarea>
                            </div>

                            <button
                                type="submit"
                                class="pa-button pa-button-primary"
                            >
                                Verify payment & approve
                            </button>
                        </form>
                    @else
                        <p class="pa-form-description">
                            Monthly, Yearly and Lifetime plans will
                            become available here after their prices
                            are set and the plans are activated.
                        </p>
                    @endif

                    @if($trialPlan)
                        <div class="pa-form-divider"></div>

                        <form
                            method="POST"
                            action="{{ route('platform-admin.clinics.approve', $clinic->clinic_id) }}"
                        >
                            @csrf

                            <input
                                type="hidden"
                                name="plan_code"
                                value="trial_7d"
                            >

                            <h3 class="pa-form-heading">
                                Optional 7-day trial
                            </h3>

                            <p class="pa-form-description">
                                Approve this clinic with a seven-day
                                trial. No payment is recorded.
                            </p>

                            <button type="submit" class="pa-button">
                                Approve with trial
                            </button>
                        </form>
                    @endif
                </div>
            </section>

            <section class="pa-panel">
                <header class="pa-panel-header">
                    <div>
                        <h2 class="pa-panel-title">
                            Reject registration
                        </h2>

                        <p class="pa-panel-description">
                            Record the reason for declining this clinic.
                        </p>
                    </div>
                </header>

                <div class="pa-panel-body">
                    <form
                        method="POST"
                        action="{{ route('platform-admin.clinics.reject', $clinic->clinic_id) }}"
                        class="pa-form"
                    >
                        @csrf

                        <div class="pa-field">
                            <label for="rejection-reason">
                                Rejection reason *
                            </label>

                            <textarea
                                id="rejection-reason"
                                name="rejection_reason"
                                class="pa-input"
                                rows="5"
                                maxlength="2000"
                                required
                            >{{ old('rejection_reason') }}</textarea>
                        </div>

                        <button type="submit" class="pa-button">
                            Reject registration
                        </button>
                    </form>
                </div>
            </section>
        </div>
    @endif

    <section class="pa-panel">
        <header class="pa-panel-header">
            <div>
                <h2 class="pa-panel-title">
                    Recent activity
                </h2>
            </div>
        </header>

        @if($auditLogs->isEmpty())
            <div class="pa-empty">
                <p class="pa-empty-title">
                    No admin activity recorded
                </p>
            </div>
        @else
            <div class="pa-activity-list">
                @foreach($auditLogs as $log)
                    <div class="pa-activity-row">
                        <div>
                            <span class="pa-cell-title">
                                {{ \Illuminate\Support\Str::headline($log->action) }}
                            </span>

                            <span class="pa-cell-subtitle">
                                {{ $log->admin_name ?? 'System' }}
                            </span>
                        </div>

                        <time>
                            {{ \Illuminate\Support\Carbon::parse($log->created_at)->format('d M Y, h:i A') }}
                        </time>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection