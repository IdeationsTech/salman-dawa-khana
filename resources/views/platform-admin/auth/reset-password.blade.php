@extends('platform-admin.auth.layout')

@section('title', 'Reset Admin Password')
@section('heading', 'Set a new password')
@section('description', 'Use at least 12 characters for your admin password.')

@section('form')
<form
    method="POST"
    action="{{ route('platform-admin.password.update') }}"
>
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div class="mb-3">
        <label for="admin-reset-email" class="form-label">
            {{ __('Email address') }} *
        </label>

        <input
            type="email"
            id="admin-reset-email"
            name="email"
            class="form-control"
            value="{{ old('email', $email) }}"
            maxlength="160"
            autocomplete="email"
            required
        >
    </div>

    <div class="mb-3">
        <label for="admin-new-password" class="form-label">
            {{ __('New password') }} *
        </label>

        <input
            type="password"
            id="admin-new-password"
            name="password"
            class="form-control"
            minlength="12"
            maxlength="72"
            autocomplete="new-password"
            required
        >
    </div>

    <div class="mb-4">
        <label for="admin-confirm-password" class="form-label">
            {{ __('Confirm password') }} *
        </label>

        <input
            type="password"
            id="admin-confirm-password"
            name="password_confirmation"
            class="form-control"
            minlength="12"
            maxlength="72"
            autocomplete="new-password"
            required
        >
    </div>

    <button type="submit" class="btn btn-primary w-100">
        {{ __('Reset password') }}
    </button>
</form>

<div class="auth-footer">
    <a
        href="{{ route('platform-admin.login') }}"
        class="auth-link"
    >
        {{ __('Back to admin sign in') }}
    </a>
</div>
@endsection