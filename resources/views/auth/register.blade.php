@extends('layouts.app')

@section('title', 'Create Clinic Account — Salman Dawa Khana')

@section('content')
<div class="auth-page">
    <div class="auth-card register-card">
        <div class="auth-brand-panel">
            <div class="brand-mark">
                <i class="bi bi-hospital" aria-hidden="true"></i>
            </div>

            <h1>Salman Dawa Khana</h1>
            <p class="brand-tagline">Patient Care &amp; Clinic Management</p>
            <div class="brand-divider"></div>

            <p class="brand-description">
                Create your clinic account and manage patients,
                prescriptions and payments in one place.
            </p>
        </div>

        <div class="auth-form-panel">
            <div class="auth-form-content">
                <div class="mobile-brand">
                    <div class="brand-mark">
                        <i class="bi bi-hospital" aria-hidden="true"></i>
                    </div>
                    <h2>Salman Dawa Khana</h2>
                </div>

                <div class="auth-heading">
                    <p class="eyebrow">GET STARTED</p>
                    <h2>Create your clinic account</h2>
                    <p>Enter your clinic and owner details below.</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <button
                            type="button"
                            class="btn-close float-end"
                            data-dismiss-alert
                            aria-label="Close"
                        ></button>

                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    action="{{ route('register.submit') }}"
                    method="POST"
                    data-clinic-signup
                >
                    @csrf

                    <div class="register-form-grid">
                        <div>
                            <label for="clinic_name" class="form-label">
                                Clinic Name *
                            </label>

                            <input
                                type="text"
                                id="clinic_name"
                                name="clinic_name"
                                class="form-control"
                                value="{{ old('clinic_name') }}"
                                maxlength="160"
                                required
                            >
                        </div>

                        <div>
                            <label for="owner_name" class="form-label">
                                Owner Name *
                            </label>

                            <input
                                type="text"
                                id="owner_name"
                                name="owner_name"
                                class="form-control"
                                value="{{ old('owner_name') }}"
                                maxlength="120"
                                autocomplete="name"
                                required
                            >
                        </div>

                        <div>
                            <label for="email" class="form-label">
                                Email Address *
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                value="{{ old('email') }}"
                                maxlength="160"
                                autocomplete="email"
                                required
                            >
                        </div>

                        <div>
                            <label for="phone" class="form-label">
                                Clinic Phone Number *
                            </label>

                            <x-phone-input
                                name="phone"
                                id="phone"
                                :required="true"
                            />
                        </div>

                        <div>
                            <label for="country_code" class="form-label">
                                Clinic Country *
                            </label>

                            <select
                                id="country_code"
                                name="country_code"
                                class="form-select"
                                data-signup-country
                                required
                            >
                                <option value="">Select country</option>

                                @foreach ($countries as $code => $name)
                                    <option
                                        value="{{ $code }}"
                                        data-default-currency="{{ $countryCurrencies[$code] ?? '' }}"
                                        @selected(old('country_code') === $code)
                                    >
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="currency" class="form-label">
                                Clinic Currency *
                            </label>

                            <select
                                id="currency"
                                name="currency"
                                class="form-select"
                                data-signup-currency
                                required
                            >
                                <option value="">Select currency</option>

                                @foreach ($currencies as $code => $name)
                                    <option
                                        value="{{ $code }}"
                                        @selected(old('currency') === $code)
                                    >
                                        {{ $code }} — {{ $name }}
                                    </option>
                                @endforeach
                            </select>

                            <small class="text-muted" data-signup-currency-help>
                                Country currency is selected automatically.
                                You may choose another before registering.
                                Afterwards, contact the platform administrator to change it.
                            </small>
                        </div>

                        <div>
                            <label for="password" class="form-label">
                                Password *
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                minlength="8"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <div>
                            <label for="password_confirmation" class="form-label">
                                Confirm Password *
                            </label>

                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-control"
                                minlength="8"
                                autocomplete="new-password"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-check register-terms">
                        <input
                            type="checkbox"
                            id="terms"
                            name="terms"
                            class="form-check-input"
                            value="1"
                            @checked(old('terms'))
                            required
                        >

                        <label for="terms" class="form-check-label">
                            I agree to the
                            <a href="#" class="auth-link">Terms of Service</a>
                            and
                            <a href="#" class="auth-link">Privacy Policy</a>.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        Create Clinic Account
                    </button>
                </form>

                <div class="auth-footer">
                    <span>Already have an account?</span>
                    <a href="{{ route('login') }}" class="auth-link">Sign in</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection