@extends('layouts.app')

@section('title', 'Sign In — Clinicora')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-brand-panel">
            <div class="brand-mark">
                <i class="bi bi-heart-pulse" aria-hidden="true"></i>
            </div>
            <h1>Clinicora</h1>
            <p class="brand-tagline">{{ __('Clinic Management, Simplified.') }}</p>
            <div class="brand-divider"></div>
            <p class="brand-description">
                {{ __('Manage patients, prescriptions, orders and clinic payments.') }}
            </p>
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
                    <p class="eyebrow">{{ __('WELCOME BACK') }}</p>
                    <h2>{{ __('Sign in to your account') }}</h2>
                    <p>{{ __('Enter your details to continue.') }}</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert alert-success" role="status">
                        {{ __(session('status')) }}
                    </div>
                @endif

                <form action="{{ route('login.submit') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Email address') }} *</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control form-control-lg"
                            value="{{ old('email') }}"
                            maxlength="160"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <div class="password-label-row">
                            <label for="password" class="form-label">{{ __('Password') }} *</label>
                            <a href="{{ route('password.request') }}" class="auth-link">
                                {{ __('Forgot password?') }}
                            </a>
                        </div>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control form-control-lg"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <div class="form-check mb-4">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            class="form-check-input"
                            value="1"
                            @checked(old('remember'))
                        >
                        <label for="remember" class="form-check-label">{{ __('Remember me') }}</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        {{ __('Sign in') }}
                    </button>
                </form>

                <div class="auth-footer">
                    <span>{{ __('New clinic?') }}</span>
                    <a href="{{ route('register') }}" class="auth-link">{{ __('Create an account') }}</a>
                </div>

                <div class="secure-note">
                    <i class="bi bi-shield-check" aria-hidden="true"></i>
                    {{ __('Access to your clinic workspace') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection