@extends('layouts.app')

@section('title', 'Create Clinic Account — Salman Dawa Khana')

@section('content')
<div class="auth-page">
    <div class="auth-card register-card">

        <div class="auth-brand-panel">
            <div class="brand-mark">+</div>

            <h1>Salman Dawa Khana</h1>

            <p class="brand-tagline">
                Patient Care &amp; Clinic Management
            </p>

            <div class="brand-divider"></div>

            <p class="brand-description">
                Create your clinic account and start managing
                patients, prescriptions and payments in one place.
            </p>
        </div>

        <div class="auth-form-panel">
            <div class="auth-form-content">

                <div class="auth-heading">
                    <p class="eyebrow">GET STARTED</p>
                    <h2>Create your clinic account</h2>
                    <p>Enter your clinic and owner details below.</p>
                </div>

                <form action="#" method="POST">
                    @csrf

                    <div class="register-form-grid">
                        <div>
                            <label for="clinic_name" class="form-label">
                                Clinic Name
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="clinic_name"
                                name="clinic_name"
                                placeholder="Salman Dawa Khana"
                            >
                        </div>

                        <div>
                            <label for="owner_name" class="form-label">
                                Owner Name
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="owner_name"
                                name="owner_name"
                                placeholder="Your full name"
                            >
                        </div>

                        <div>
                            <label for="email" class="form-label">
                                Email Address
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                placeholder="you@example.com"
                            >
                        </div>

                        <div>
                            <label for="phone" class="form-label">
                                Phone Number
                            </label>

                            <input
                                type="tel"
                                class="form-control"
                                id="phone"
                                name="phone"
                                placeholder="+971 50 000 0000"
                            >
                        </div>

                        <div>
                            <label for="password" class="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="Create a password"
                            >
                        </div>

                        <div>
                            <label for="password_confirmation" class="form-label">
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Repeat your password"
                            >
                        </div>
                    </div>

                    <div class="form-check register-terms">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="terms"
                            name="terms"
                        >

                        <label class="form-check-label" for="terms">
                            I agree to the
                            <a href="#" class="auth-link">Terms of Service</a>
                            and
                            <a href="#" class="auth-link">Privacy Policy</a>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        Create Clinic Account
                    </button>
                </form>

                <div class="auth-footer">
                    <span>Already have an account?</span>
                    <a href="/login" class="auth-link">Sign in</a>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection