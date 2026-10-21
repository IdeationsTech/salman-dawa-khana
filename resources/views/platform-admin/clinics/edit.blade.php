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
            {{ __('Back to clinic details') }}
        </a>

        <h1 class="pa-page-title">{{ $clinic->name }}</h1>

        <p class="pa-page-description">
            {{ __('Clinic #') }}{{ $clinic->clinic_id }}
            · {{ \Illuminate\Support\Str::headline($clinic->onboarding_status) }}
        </p>
    </div>

    <a
        href="{{ route('platform-admin.clinics.complimentary.create', $clinic->clinic_id) }}"
        class="pa-button"
    >
        {{ __('Grant complimentary Lifetime') }}
    </a>
</div>

@if (session('success'))
    <div class="pa-notice" role="status">
        {{ __(session('success')) }}
    </div>
@endif

@if ($errors->any())
    <div class="pa-notice pa-notice-error" role="alert">
        <strong>{{ __('Please correct the following:') }}</strong>

        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ __($error) }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="pa-panel">
    <header class="pa-panel-header">
        <div>
            <h2 class="pa-panel-title">{{ __('Clinic country and currency') }}</h2>

            <p class="pa-panel-description">
                {{ \App\Support\ClinicLocaleCatalog::countryName($clinic->country_code) }}
                · {{ $clinic->currency }}
            </p>
        </div>
    </header>

    <div class="pa-panel-body">
        <p class="pa-form-description">
            {{ __('Changes require a reason and client consultation. Existing amounts retain their original currency.') }}
        </p>

        <a
            href="{{ route('platform-admin.clinics.currency.edit', $clinic->clinic_id) }}"
            class="pa-button"
        >
            {{ __('Change country / currency') }}
        </a>
    </div>
</section>

<section class="pa-panel">
    <header class="pa-panel-header">
        <div>
            <h2 class="pa-panel-title">{{ __('Clinic information') }}</h2>

            <p class="pa-panel-description">
                {{ __('Update the clinic name, contact details and logo.') }}
            </p>
        </div>
    </header>

    <div class="pa-panel-body">
        <form
            method="POST"
            action="{{ route('platform-admin.clinics.details.update', $clinic->clinic_id) }}"
            enctype="multipart/form-data"
            class="pa-form"
        >
            @csrf
            @method('PUT')

            <div class="pa-form-grid">
                <div class="pa-field">
                    <label for="clinic-name">{{ __('Clinic name') }} *</label>

                    <input
                        id="clinic-name"
                        type="text"
                        name="name"
                        class="pa-input"
                        maxlength="160"
                        value="{{ old('name', $clinic->name) }}"
                        required
                    >
                </div>

                <div class="pa-field">
                    <label for="clinic-phone">{{ __('Phone number') }} *</label>

                    <input
                        id="clinic-phone"
                        type="tel"
                        name="phone"
                        class="pa-input"
                        maxlength="30"
                        value="{{ old('phone', $clinic->phone) }}"
                        required
                    >
                </div>

                <div class="pa-field">
                    <label for="clinic-address">{{ __('Address') }}</label>

                    <textarea
                        id="clinic-address"
                        name="address"
                        class="pa-input"
                        rows="3"
                        maxlength="255"
                    >{{ old('address', $clinic->address) }}</textarea>
                </div>

                <div class="pa-field">
                    <label for="clinic-logo">{{ __('Clinic logo') }}</label>

                    @if ($clinic->logo_path)
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($clinic->logo_path) }}"
                            alt="{{ $clinic->name }} logo"
                            class="pa-clinic-logo-preview"
                        >
                    @endif

                    <input
                        id="clinic-logo"
                        type="file"
                        name="logo"
                        class="pa-input"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <p class="pa-form-description">
                        {{ __('JPG, PNG or WebP. Maximum 2 MB.') }}
                    </p>

                    @if ($clinic->logo_path)
                        <label class="pa-logo-remove">
                            <input
                                type="checkbox"
                                name="remove_logo"
                                value="1"
                                @checked(old('remove_logo'))
                            >

                            <span>{{ __('Remove current logo') }}</span>
                        </label>
                    @endif
                </div>
            </div>

            <button type="submit" class="pa-button pa-button-primary">
                {{ __('Save clinic details') }}
            </button>
        </form>
    </div>
</section>

<div class="pa-detail-grid">
    <section class="pa-panel">
        <header class="pa-panel-header">
            <div>
                <h2 class="pa-panel-title">{{ __('Clinic access') }}</h2>

                <p class="pa-panel-description">
                    {{ __('Suspension blocks clinic users on their next request.') }}
                </p>
            </div>
        </header>

        <div class="pa-panel-body">
            <dl class="pa-detail-list">
                <div>
                    <dt>{{ __('Current status') }}</dt>
                    <dd>
                        {{ \Illuminate\Support\Str::headline($clinic->onboarding_status) }}
                    </dd>
                </div>

                <div>
                    <dt>{{ __('Current plan') }}</dt>
                    <dd>{{ $license->plan_name ?? 'No current license' }}</dd>
                </div>

                <div>
                    <dt>{{ __('License status') }}</dt>
                    <dd>
                        {{ $license
                            ? \Illuminate\Support\Str::headline($license->status)
                            : 'Not assigned' }}
                    </dd>
                </div>
            </dl>

            @if (in_array($clinic->onboarding_status, ['approved', 'suspended'], true))
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
                        <label for="status-reason">{{ __('Reason') }} *</label>

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
                    {{ __('Approve this clinic through its details page before editing its access.') }}
                </p>
            @endif
        </div>
    </section>

    <section class="pa-panel">
        <header class="pa-panel-header">
            <div>
                <h2 class="pa-panel-title">{{ __('Change subscription') }}</h2>

                <p class="pa-panel-description">
                    {{ __('Verify manual payment before issuing a new license.') }}
                </p>
            </div>
        </header>

        <div class="pa-panel-body">
            @if (
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
                        <label for="edit-plan">{{ __('New plan') }} *</label>

                        <select
                            id="edit-plan"
                            name="plan_code"
                            class="pa-input"
                            required
                        >
                            <option value="">{{ __('Select a plan') }}</option>

                            @foreach ($plans as $plan)
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
                                {{ __('Payment method') }} *
                            </label>

                            <select
                                id="edit-payment-method"
                                name="payment_method"
                                class="pa-input"
                                required
                            >
                                <option value="">{{ __('Select method') }}</option>

                                <option
                                    value="bank_transfer"
                                    @selected(old('payment_method') === 'bank_transfer')
                                >
                                    {{ __('Bank transfer') }}
                                </option>

                                <option
                                    value="cash"
                                    @selected(old('payment_method') === 'cash')
                                >
                                    {{ __('Cash') }}
                                </option>
                            </select>
                        </div>

                        <div class="pa-field">
                            <label for="edit-amount">
                                {{ __('Amount received (AED)') }} *
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
                            <label for="edit-paid-at">{{ __('Payment date') }} *</label>

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
                                {{ __('Bank/reference number') }}
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
                        <label for="plan-reason">{{ __('Reason for change') }} *</label>

                        <textarea
                            id="plan-reason"
                            name="reason"
                            class="pa-input"
                            rows="3"
                            maxlength="2000"
                            required
                        >{{ old('reason') }}</textarea>
                    </div>

                    <button type="submit" class="pa-button pa-button-primary">
                        {{ __('Verify payment & change subscription') }}
                    </button>
                </form>
            @else
                <p class="pa-form-description">
                    {{ __('Approve the clinic and activate a priced subscription plan before changing its subscription.') }}
                </p>
            @endif
        </div>
    </section>
</div>

<section class="pa-panel">
    <header class="pa-panel-header">
        <h2 class="pa-panel-title">{{ __('License history') }}</h2>
    </header>

    @if ($licenseHistory->isEmpty())
        <div class="pa-empty">
            <p class="pa-empty-title">{{ __('No licenses issued yet') }}</p>
        </div>
    @else
        <div class="pa-panel-body">
            <dl class="pa-detail-list pa-detail-list-wide">
                @foreach ($licenseHistory as $pastLicense)
                    <div>
                        <dt>
                            {{ __('License #') }}{{ $pastLicense->clinic_license_id }}

                            @if (
                                (string) $clinic->current_license_id
                                === (string) $pastLicense->clinic_license_id
                            )
                                · Current
                            @endif
                        </dt>

                        <dd>
                            {{ $pastLicense->plan_name }}
                            · {{ \Illuminate\Support\Str::headline($pastLicense->status) }}
                            · {{ \Illuminate\Support\Carbon::parse($pastLicense->starts_at)->format('d M Y') }}

                            @if ($pastLicense->ends_at)
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