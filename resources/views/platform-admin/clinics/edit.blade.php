@extends('platform-admin.layouts.app')

@section('title', 'Edit Clinic')
@section('page_title', 'Edit Clinic')

@section('content')
    <div class="pa-page-heading">
        <div>
            <a
                href="{{ route('platform-admin.clinics.show', $clinic->clinic_id) }}"
                class="pa-back-link"
            >
                ← Back to clinic details
            </a>

            <h1 class="pa-page-title">
                {{ $clinic->name }}
            </h1>

            <p class="pa-page-description">
                Clinic #{{ $clinic->clinic_id }}
                · {{ \Illuminate\Support\Str::headline($clinic->onboarding_status) }}
            </p>
        </div>
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
                    <h2 class="pa-panel-title">Clinic access</h2>

                    <p class="pa-panel-description">
                        Suspension blocks clinic users on their next request.
                    </p>
                </div>
            </header>

            <div class="pa-panel-body">
                <dl class="pa-detail-list">
                    <div>
                        <dt>Current status</dt>
                        <dd>
                            {{ \Illuminate\Support\Str::headline($clinic->onboarding_status) }}
                        </dd>
                    </div>

                    <div>
                        <dt>Current plan</dt>
                        <dd>{{ $license->plan_name ?? 'No current license' }}</dd>
                    </div>

                    <div>
                        <dt>License status</dt>
                        <dd>
                            {{ $license
                                ? \Illuminate\Support\Str::headline($license->status)
                                : 'Not assigned' }}
                        </dd>
                    </div>
                </dl>

                @if(in_array($clinic->onboarding_status, ['approved', 'suspended'], true))
                    <div class="pa-form-divider"></div>

                    <form
                        method="POST"
                        action="{{ route('platform-admin.clinics.status', $clinic->clinic_id) }}"
                        class="pa-form"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="action"
                            value="{{ $clinic->onboarding_status === 'approved' ? 'suspend' : 'reactivate' }}"
                        >

                        <div class="pa-field">
                            <label for="status-reason">
                                Reason *
                            </label>

                            <textarea
                                id="status-reason"
                                name="reason"
                                class="pa-input"
                                rows="3"
                                maxlength="2000"
                                required
                            >{{ old('reason') }}</textarea>
                        </div>

                        <button type="submit" class="pa-button">
                            {{ $clinic->onboarding_status === 'approved'
                                ? 'Suspend clinic'
                                : 'Reactivate clinic' }}
                        </button>
                    </form>
                @else
                    <p class="pa-form-description">
                        This clinic must be approved through its details page
                        before its access can be edited.
                    </p>
                @endif
            </div>
        </section>

        <section class="pa-panel">
            <header class="pa-panel-header">
                <div>
                    <h2 class="pa-panel-title">Change subscription</h2>

                    <p class="pa-panel-description">
                        Verify manual payment before issuing a new license.
                    </p>
                </div>
            </header>

            <div class="pa-panel-body">
                @if(
                    in_array($clinic->onboarding_status, ['approved', 'suspended'], true)
                    && $plans->isNotEmpty()
                )
                    <form
                        method="POST"
                        action="{{ route('platform-admin.clinics.subscription', $clinic->clinic_id) }}"
                        class="pa-form"
                    >
                        @csrf

                        <div class="pa-field">
                            <label for="edit-plan">New plan *</label>

                            <select
                                id="edit-plan"
                                name="plan_code"
                                class="pa-input"
                                required
                            >
                                <option value="">Select a plan</option>

                                @foreach($plans as $plan)
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
                                <label for="edit-payment-method">
                                    Payment method *
                                </label>

                                <select
                                    id="edit-payment-method"
                                    name="payment_method"
                                    class="pa-input"
                                    required
                                >
                                    <option value="">Select method</option>

                                    <option
                                        value="bank_transfer"
                                        @selected(old('payment_method') === 'bank_transfer')
                                    >
                                        Bank transfer
                                    </option>

                                    <option
                                        value="cash"
                                        @selected(old('payment_method') === 'cash')
                                    >
                                        Cash
                                    </option>
                                </select>
                            </div>

                            <div class="pa-field">
                                <label for="edit-amount">
                                    Amount received (AED) *
                                </label>

                                <input
                                    id="edit-amount"
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
                                <label for="edit-paid-at">
                                    Payment date *
                                </label>

                                <input
                                    id="edit-paid-at"
                                    type="date"
                                    name="paid_at"
                                    class="pa-input"
                                    max="{{ now()->toDateString() }}"
                                    value="{{ old('paid_at') }}"
                                    required
                                >
                            </div>

                            <div class="pa-field">
                                <label for="edit-reference">
                                    Bank/reference number
                                </label>

                                <input
                                    id="edit-reference"
                                    type="text"
                                    name="reference_no"
                                    class="pa-input"
                                    maxlength="100"
                                    value="{{ old('reference_no') }}"
                                >
                            </div>
                        </div>

                        <div class="pa-field">
                            <label for="plan-reason">
                                Reason for change *
                            </label>

                            <textarea
                                id="plan-reason"
                                name="reason"
                                class="pa-input"
                                rows="3"
                                maxlength="2000"
                                required
                            >{{ old('reason') }}</textarea>
                        </div>

                        <button
                            type="submit"
                            class="pa-button pa-button-primary"
                        >
                            Verify payment & change subscription
                        </button>
                    </form>
                @else
                    <p class="pa-form-description">
                        Approve the clinic and activate a priced subscription
                        plan before changing its subscription.
                    </p>
                @endif
            </div>
        </section>
    </div>

    <section class="pa-panel">
        <header class="pa-panel-header">
            <div>
                <h2 class="pa-panel-title">License history</h2>
            </div>
        </header>

        @if($licenseHistory->isEmpty())
            <div class="pa-empty">
                <p class="pa-empty-title">
                    No licenses issued yet
                </p>
            </div>
        @else
            <div class="pa-panel-body">
                <dl class="pa-detail-list pa-detail-list-wide">
                    @foreach($licenseHistory as $pastLicense)
                        <div>
                            <dt>
                                License #{{ $pastLicense->clinic_license_id }}
                                @if(
                                    $clinic->current_license_id
                                    === $pastLicense->clinic_license_id
                                )
                                    · Current
                                @endif
                            </dt>

                            <dd>
                                {{ $pastLicense->plan_name }}
                                · {{ \Illuminate\Support\Str::headline($pastLicense->status) }}
                                · {{ \Illuminate\Support\Carbon::parse($pastLicense->starts_at)->format('d M Y') }}
                                @if($pastLicense->ends_at)
                                    – {{ \Illuminate\Support\Carbon::parse($pastLicense->ends_at)->format('d M Y') }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    </section>
@endsection