@extends('layouts.app')

@section('title', 'Create Clinic Account — Salman Dawa Khana')

@section('content')
<div class="auth-page">
    <div class="auth-card register-card">

        {{-- Registration Brand Panel --}}
        <div class="auth-brand-panel">
            <div class="brand-mark">+</div>

            <h1>Salman Dawa Khana</h1>

            <p class="brand-tagline">
                Patient Care &amp; Clinic Management
            </p>

            <div class="brand-divider"></div>

            <p class="brand-description">
                Create your clinic account and start managing patients,
                prescriptions and payments in one place.
            </p>
        </div>

        {{-- Registration Form Panel --}}
        <div class="auth-form-panel">
            <div class="auth-form-content">

                <div class="mobile-brand">
                    <div class="brand-mark">+</div>
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
                >
                    @csrf

                    <div class="register-form-grid">

                        <div>
                            <label for="clinic_name" class="form-label">
                                Clinic Name
                            </label>

                            <input
                                type="text"
                                class="form-control @error('clinic_name') is-invalid @enderror"
                                id="clinic_name"
                                name="clinic_name"
                                value="{{ old('clinic_name') }}"
                                placeholder="Salman Dawa Khana"
                                required
                            >

                            @error('clinic_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div>
                            <label for="owner_name" class="form-label">
                                Owner Name
                            </label>

                            <input
                                type="text"
                                class="form-control @error('owner_name') is-invalid @enderror"
                                id="owner_name"
                                name="owner_name"
                                value="{{ old('owner_name') }}"
                                placeholder="Your full name"
                                required
                            >

                            @error('owner_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="form-label">
                                Email Address
                            </label>

                            <input
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="you@example.com"
                                autocomplete="email"
                                required
                            >

                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="form-label">
                                Phone Number
                            </label>

                            <input
                                type="tel"
                                class="form-control @error('phone') is-invalid @enderror"
                                id="phone"
                                name="phone"
                                value="{{ old('phone') }}"
                                placeholder="+971 50 000 0000"
                                required
                            >

                            @error('phone')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                placeholder="Create a password"
                                autocomplete="new-password"
                                required
                            >

                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="password_confirmation"
                                class="form-label"
                            >
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Repeat your password"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                    </div>

                    <div class="form-check register-terms">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="terms"
                            name="terms"
                            value="1"
                            {{ old('terms') ? 'checked' : '' }}
                            required
                        >

                        <label class="form-check-label" for="terms">
                            I agree to the
                            <a href="#" class="auth-link">
                                Terms of Service
                            </a>
                            and
                            <a href="#" class="auth-link">
                                Privacy Policy
                            </a>
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary btn-lg w-100"
                    >
                        Create Clinic Account
                    </button>
                </form>

                <div class="auth-footer">
                    <span>Already have an account?</span>

                    <a href="{{ route('login') }}" class="auth-link">
                        Sign in
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection