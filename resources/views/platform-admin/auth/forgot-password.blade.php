@extends('platform-admin.auth.layout')

@section('title', 'Forgot Admin Password')
@section('heading', 'Forgot your password?')
@section('description', 'Enter your platform administrator email address.')

@section('form')
<form
    method="POST"
    action="{{ route('platform-admin.password.email') }}"
>
    @csrf

    <div class="mb-4">
        <label for="admin-reset-email" class="form-label">
            Email address *
        </label>

        <input
            type="email"
            id="admin-reset-email"
            name="email"
            class="form-control"
            value="{{ old('email') }}"
            maxlength="160"
            autocomplete="email"
            required
            autofocus
        >
    </div>

    <button type="submit" class="btn btn-primary w-100">
        Send reset link
    </button>
</form>

<div class="auth-footer">
    <a
        href="{{ route('platform-admin.login') }}"
        class="auth-link"
    >
        Back to admin sign in
    </a>
</div>
@endsection