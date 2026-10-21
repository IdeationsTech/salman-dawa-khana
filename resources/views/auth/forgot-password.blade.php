@extends('layouts.app')

@section('title', 'Forgot Password — Clinicora')

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
                {{ __('Recover access to your clinic account.') }}
            </p>
        </div>

        <div class="auth-form-panel">
            <div class="auth-form-content">
                <div class="auth-heading">
                    <p class="eyebrow">{{ __('ACCOUNT RECOVERY') }}</p>
                    <h2>{{ __('Forgot your password?') }}</h2>
                    <p>{{ __('Enter your clinic account email.') }}</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success">{{ __(session('status')) }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Email address') }} *</label>
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

                    <button type="submit" class="btn btn-primary w-100">
                        {{ __('Send reset link') }}
                    </button>
                </form>

                <div class="auth-footer">
                    <a href="{{ route('login') }}" class="auth-link">{{ __('Back to sign in') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection