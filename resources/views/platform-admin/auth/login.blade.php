@extends('platform-admin.auth.layout')

@section('title', 'Admin Login')
@section('heading', 'Welcome back')
@section('description', 'Sign in to your platform administrator account.')

@section('form')
<form
    method="POST"
    action="{{ route('platform-admin.login.submit') }}"
>
    @csrf

    <div class="mb-3">
        <label for="admin-email" class="form-label">
            Email address *
        </label>

        <input
            type="email"
            id="admin-email"
            name="email"
            class="form-control"
            value="{{ old('email') }}"
            maxlength="160"
            autocomplete="username"
            required
            autofocus
        >
    </div>

    <div class="mb-3">
        <label for="admin-password" class="form-label">
            Password *
        </label>

        <input
            type="password"
            id="admin-password"
            name="password"
            class="form-control"
            autocomplete="current-password"
            required
        >
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <label class="d-flex align-items-center gap-2">
            <input
                type="checkbox"
                name="remember"
                value="1"
                @checked(old('remember'))
            >
            <span>Remember me</span>
        </label>

        <a
            href="{{ route('platform-admin.password.request') }}"
            class="auth-link"
        >
            Forgot password?
        </a>
    </div>

    <button type="submit" class="btn btn-primary w-100">
        Sign in
    </button>
</form>
@endsection