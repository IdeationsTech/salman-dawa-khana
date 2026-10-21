@extends('layouts.app')

@section('title', 'Reset Password — Clinicora')

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
            <p class="brand-description">{{ __('Set a new password for your account.') }}</p>
        </div>

        <div class="auth-form-panel">
            <div class="auth-form-content">
                <div class="auth-heading">
                    <p class="eyebrow">{{ __('ACCOUNT RECOVERY') }}</p>
                    <h2>{{ __('Reset your password') }}</h2>
                    <p>{{ __('Choose a new password with at least 8 characters.') }}</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)<li>{{ __($error) }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Email address') }} *</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            value="{{ old('email', $email) }}"
                            maxlength="160"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('New password') }} *</label>
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

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">
                            {{ __('Confirm new password') }} *
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

                    <button type="submit" class="btn btn-primary w-100">
                        {{ __('Reset password') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection