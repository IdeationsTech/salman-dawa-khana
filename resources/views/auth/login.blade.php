@extends('layouts.app')

@section('title', 'Login — Salman Dawa Khana')

@section('content')
<div class="auth-page">
    <div class="auth-card">

        <div class="auth-brand-panel">
            <div class="brand-mark">+</div>

            <h1>Salman Dawa Khana</h1>

            <p class="brand-tagline">
                Patient Care &amp; Clinic Management
            </p>

            <div class="brand-divider"></div>

            <p class="brand-description">
                A simple and reliable system for managing patients,
                prescriptions and clinic payments.
            </p>
        </div>

        <div class="auth-form-panel">
            <div class="auth-form-content">

                <div class="mobile-brand">
                    <div class="brand-mark">+</div>
                    <h2>Salman Dawa Khana</h2>
                </div>

                <div class="auth-heading">
                    <p class="eyebrow">WELCOME BACK</p>
                    <h2>Sign in to your account</h2>
                    <p>Enter your details to continue.</p>
                </div>

                <form action="#" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            Email Address
                        </label>

                        <input
                            type="email"
                            class="form-control form-control-lg"
                            id="email"
                            name="email"
                            placeholder="you@example.com"
                            autocomplete="email"
                        >
                    </div>

                    <div class="mb-3">
                        <div class="password-label-row">
                            <label for="password" class="form-label">
                                Password
                            </label>

                            <a href="#" class="auth-link">
                                Forgot password?
                            </a>
                        </div>

                        <input
                            type="password"
                            class="form-control form-control-lg"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                        >
                    </div>

                    <div class="form-check mb-4">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="remember"
                            name="remember"
                        >

                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        Sign In
                    </button>
                </form>

                <div class="auth-footer">
                    <span>New clinic?</span>
                    <a href="#" class="auth-link">Create an account</a>
                </div>

                <div class="secure-note">
                    <span>🔒</span>
                    Secure access to your clinic data
                </div>

            </div>
        </div>

    </div>
</div>
@endsection