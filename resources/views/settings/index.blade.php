@extends('layouts.app')

@section('title', 'Settings — ' . $clinic->name)

@section('content')
@php
    $currentUser = auth()->user();
    $isOwner = $currentUser->role?->name === 'Owner';

    $patientIdCountry = old(
        'patient_id_country',
        $settings->get('patient_id_country')?->setting_value
            ?? \App\Support\ClinicLocaleCatalog::idCountry($clinic->country_code)
    );

    $idLabels = \App\Support\ClinicLocaleCatalog::idLabels();
@endphp

<div class="app-shell">
    @include('settings._sidebar')

    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">Application configuration</p>
                <h1>Settings</h1>
                <p class="page-subtitle">
                    Manage your clinic profile and system preferences.
                </p>
            </div>
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="status">
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

        <section class="settings-layout">
            @include('settings._navigation')

            <div class="settings-content">
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Clinic country and currency</h2>
                            <p>The platform administrator manages changes.</p>
                        </div>
                    </div>

                    <div class="settings-form-grid">
                        <div class="form-field">
                            <label for="clinic_country_display" class="form-label">
                                Clinic country
                            </label>
                            <input
                                id="clinic_country_display"
                                class="form-control"
                                value="{{ \App\Support\ClinicLocaleCatalog::countryName($clinic->country_code) }}"
                                readonly
                            >
                        </div>

                        <div class="form-field">
                            <label for="clinic_currency_display" class="form-label">
                                Clinic currency
                            </label>
                            <input
                                id="clinic_currency_display"
                                class="form-control"
                                value="{{ $clinic->currency }}"
                                readonly
                            >
                        </div>
                    </div>
                </section>

                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Clinic profile</h2>
                            <p>Update your clinic details and logo.</p>
                        </div>

                        <div class="clinic-settings-logo">
                            @if ($clinic->logo_path)
                                <img
                                    src="{{ asset('storage/' . $clinic->logo_path) }}"
                                    alt="{{ $clinic->name }} logo"
                                    class="settings-logo-image"
                                >
                            @else
                                <i class="bi bi-hospital app-icon" aria-hidden="true"></i>
                            @endif
                        </div>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('settings.update') }}"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        @method('PUT')

                        <div class="settings-form-grid">
                            <div class="form-field settings-field-wide">
                                <label for="clinic_name" class="form-label">
                                    Clinic name *
                                </label>
                                <input
                                    type="text"
                                    id="clinic_name"
                                    name="clinic_name"
                                    class="form-control"
                                    value="{{ old('clinic_name', $clinic->name) }}"
                                    maxlength="160"
                                    @disabled(!$isOwner)
                                    required
                                >
                            </div>

                            <div class="form-field">
                                <label for="clinic_phone" class="form-label">
                                    Phone number *
                                </label>
                                <x-phone-input
                                    name="clinic_phone"
                                    id="clinic_phone"
                                    :value="$clinic->phone"
                                    :required="true"
                                />
                            </div>

                            <div class="form-field">
                                <label for="account_email" class="form-label">
                                    Your account email
                                </label>
                                <input
                                    type="email"
                                    id="account_email"
                                    class="form-control"
                                    value="{{ $currentUser->email }}"
                                    readonly
                                >
                                <small class="text-muted">
                                    Manage your email in User account.
                                </small>
                            </div>

                            <div class="form-field settings-field-wide">
                                <label for="clinic_address" class="form-label">
                                    Full clinic address
                                </label>
                                <textarea
                                    id="clinic_address"
                                    name="clinic_address"
                                    class="form-control"
                                    rows="3"
                                    maxlength="255"
                                    @disabled(!$isOwner)
                                >{{ old('clinic_address', $clinic->address) }}</textarea>
                            </div>

                            <div class="form-field settings-field-wide">
                                <label for="print_location" class="form-label">
                                    Short location for print header
                                </label>
                                <input
                                    type="text"
                                    id="print_location"
                                    name="settings[print_location]"
                                    class="form-control"
                                    value="{{ old('settings.print_location', $settings->get('print_location')?->setting_value) }}"
                                    maxlength="160"
                                    placeholder="City, State, Country"
                                    @disabled(!$isOwner)
                                >
                                <small class="text-muted">
                                    Full address appears in the print footer.
                                </small>
                            </div>

                            @if ($isOwner)
                                <div class="form-field settings-field-wide">
                                    <label for="clinic_logo" class="form-label">
                                        {{ $clinic->logo_path ? 'Change clinic logo' : 'Upload clinic logo' }}
                                    </label>
                                    <input
                                        type="file"
                                        id="clinic_logo"
                                        name="clinic_logo"
                                        class="form-control"
                                        accept="image/png,image/jpeg,image/webp"
                                    >
                                    <small class="text-muted">
                                        PNG, JPG or WebP. Maximum 2 MB and 3000 × 3000 pixels.
                                    </small>
                                </div>

                                @if ($clinic->logo_path)
                                    <div class="form-field settings-field-wide">
                                        <div class="settings-toggle-row">
                                            <div>
                                                <label for="remove_logo">
                                                    <strong>Remove current logo</strong>
                                                </label>
                                                <small>
                                                    Select this without choosing a new file.
                                                </small>
                                            </div>
                                            <input
                                                type="checkbox"
                                                id="remove_logo"
                                                name="remove_logo"
                                                class="form-check-input"
                                                value="1"
                                                @checked(old('remove_logo'))
                                            >
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>

                        @if ($isOwner)
                            <div class="settings-panel-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check2-circle app-icon" aria-hidden="true"></i>
                                    Save clinic profile
                                </button>
                            </div>
                        @else
                            <p class="text-muted mt-3">
                                The clinic Owner manages clinic details.
                            </p>
                        @endif
                    </form>
                </section>

                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Patient identification</h2>
                            <p>Select the default ID format for new patients.</p>
                        </div>
                    </div>

                    @if ($isOwner)
                        <form
                            method="POST"
                            action="{{ route('settings.patient-id.update') }}"
                        >
                            @csrf
                            @method('PUT')

                            <div class="settings-form-grid">
                                <div class="form-field settings-field-wide">
                                    <label for="patient_id_country" class="form-label">
                                        Patient ID format *
                                    </label>

                                    <select
                                        id="patient_id_country"
                                        name="patient_id_country"
                                        class="form-select"
                                        required
                                    >
                                        @foreach ($idLabels as $code => $label)
                                            <option
                                                value="{{ $code }}"
                                                @selected($patientIdCountry === $code)
                                            >
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <small class="text-muted">
                                        This applies to new patients.
                                        Existing records retain their saved ID format.
                                        Other accepts an ID without a country-specific format.
                                    </small>
                                </div>
                            </div>

                            <div class="settings-panel-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check2-circle app-icon" aria-hidden="true"></i>
                                    Save ID format
                                </button>
                            </div>
                        </form>
                    @else
                        <p>{{ $idLabels[$patientIdCountry] ?? $idLabels['ZZ'] }}</p>
                        <small class="text-muted">
                            The clinic Owner manages this setting.
                        </small>
                    @endif
                </section>

                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Payment settings</h2>
                            <p>
                                Current clinic currency:
                                <strong>{{ $clinic->currency }}</strong>
                            </p>
                        </div>
                    </div>

                    <div class="settings-panel-actions">
                        <a
                            href="{{ route('settings.payments') }}"
                            class="btn btn-outline-secondary"
                        >
                            Open payment settings
                        </a>
                    </div>
                </section>

                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>User account</h2>
                            <p>Details of the currently signed-in user.</p>
                        </div>
                    </div>

                    <div class="settings-account-row">
                        <div class="settings-account-avatar">
                            {{ mb_strtoupper(mb_substr($currentUser->name, 0, 1)) }}
                        </div>
                        <div>
                            <strong>{{ $currentUser->name }}</strong>
                            <small>{{ $currentUser->email }}</small>
                        </div>
                        <a
                            href="{{ route('settings.account') }}"
                            class="btn btn-outline-secondary ms-auto"
                        >
                            Edit profile
                        </a>
                    </div>
                </section>
            </div>
        </section>
    </main>
</div>
@endsection