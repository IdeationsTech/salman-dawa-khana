@extends('layouts.app')

@section('title', 'Create Clinic Account — Clinicora')

@section('content')
<div class="auth-page clinicora-register">
    <div class="auth-card register-card">
        <div class="auth-brand-panel">
            <div class="clinicora-brand">
                <div class="brand-mark">
                    <i class="bi bi-heart-pulse" aria-hidden="true"></i>
                </div>

                <span>Clinicora</span>
            </div>

            <div class="clinicora-brand-content">
                <span class="clinicora-brand-eyebrow">
                    YOUR CLINIC, CONNECTED
                </span>

                <h1>
                    Better care.<br>
                    Simpler management.
                </h1>

                <p class="brand-tagline">
                    Clinic Management, Simplified.
                </p>

                <p class="brand-description">
                    Manage patients, consultations, prescriptions and
                    payments from one organized workspace.
                </p>

                <div class="clinicora-features">
                    <div>
                        <span>
                            <i class="bi bi-person-vcard" aria-hidden="true"></i>
                        </span>
                        <p>
                            <strong>Patient records</strong>
                            <small>A clear history of every visit.</small>
                        </p>
                    </div>

                    <div>
                        <span>
                            <i class="bi bi-prescription2" aria-hidden="true"></i>
                        </span>
                        <p>
                            <strong>Prescriptions &amp; orders</strong>
                            <small>Your daily work in one place.</small>
                        </p>
                    </div>

                    <div>
                        <span>
                            <i class="bi bi-shield-check" aria-hidden="true"></i>
                        </span>
                        <p>
                            <strong>Your clinic workspace</strong>
                            <small>Access for your authorized team.</small>
                        </p>
                    </div>
                </div>
            </div>

            <div class="clinicora-brand-footer">
                Built around the way your clinic works.
            </div>
        </div>

        <div class="auth-form-panel">
            <div class="auth-form-content">
                <div class="mobile-brand">
                    <div class="brand-mark">
                        <i class="bi bi-heart-pulse" aria-hidden="true"></i>
                    </div>

                    <h2>Clinicora</h2>
                </div>

                <div class="auth-heading">
                    <p class="eyebrow">GET STARTED</p>
                    <h2>Create your clinic account</h2>
                    <p>Enter your clinic and owner details.</p>
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
                                Clinic name *
                            </label>

                            <input
                                type="text"
                                id="clinic_name"
                                name="clinic_name"
                                class="form-control"
                                value="{{ old('clinic_name') }}"
                                maxlength="160"
                                placeholder="Your clinic name"
                                autocomplete="organization"
                                required
                            >
                        </div>

                        <div>
                            <label for="owner_name" class="form-label">
                                Owner name *
                            </label>

                            <input
                                type="text"
                                id="owner_name"
                                name="owner_name"
                                class="form-control"
                                value="{{ old('owner_name') }}"
                                maxlength="120"
                                placeholder="Full name"
                                autocomplete="name"
                                required
                            >
                        </div>

                        <div>
                            <label for="email" class="form-label">
                                Email address *
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                value="{{ old('email') }}"
                                maxlength="160"
                                placeholder="you@example.com"
                                autocomplete="email"
                                required
                            >
                        </div>

                        <div>
                            <label for="phone" class="form-label">
                                Clinic phone *
                            </label>

                            <x-phone-input
                                name="phone"
                                id="phone"
                                :required="true"
                            />
                        </div>

                        <div>
                            <label for="country_code" class="form-label">
                                Clinic country *
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
                                Clinic currency *
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
                        </div>

                        <div class="clinicora-currency-note">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>

                            <div>
                                <span data-signup-currency-help>
                                    Country currency is selected automatically.
                                    You can choose another before signup.
                                </span>

                                <small>
                                    After signup, currency changes are managed
                                    by the platform administrator.
                                </small>
                            </div>
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
                                placeholder="At least 8 characters"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <div>
                            <label for="password_confirmation" class="form-label">
                                Confirm password *
                            </label>

                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-control"
                                minlength="8"
                                placeholder="Repeat your password"
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

                    <button
                        type="submit"
                        class="btn btn-primary w-100 clinicora-submit"
                    >
                        Create clinic account
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
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