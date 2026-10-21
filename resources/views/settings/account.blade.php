@extends('layouts.app')

@section('title', 'User Account — ' . $clinic->name)

@section('content')
@php
    $roleName = $user->role?->name ?? 'Staff';
@endphp

<div class="app-shell">
    @include('settings._sidebar')

    {{-- User Account — Main Content --}}
    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">{{ __('Settings') }}</p>
                <h1>{{ __('User account') }}</h1>
                <p class="page-subtitle">
                    {{ __('Manage your personal information and profile photo.') }}
                </p>
            </div>
        </header>

        @if(session('success'))
            <div class="alert alert-success" role="status">
                {{ __(session('success')) }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>{{ __('Please correct the following:') }}</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ __($error) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="settings-layout">
            @include('settings._navigation')

            <div class="settings-content">

                {{-- User Account — Profile Summary --}}
                <section class="settings-panel account-profile-card">
                    <div class="account-profile-avatar">
                        @if($user->profile_photo_path)
                            <img
                                src="{{ asset('storage/' . $user->profile_photo_path) }}"
                                alt="{{ $user->name }} profile photo"
                                class="account-profile-photo"
                            >
                        @else
                            {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                        @endif
                    </div>

                    <div class="account-profile-info">
                        <h2>{{ $user->name }}</h2>
                        <p>{{ __($roleName) }}</p>
                        @if($user->created_at)
                            <span>
                                {{ __('Member since') }} {{ $user->created_at->format('F Y') }}
                            </span>
                        @endif
                    </div>
                </section>

                {{-- User Account — Profile Form --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>{{ __('Personal information') }}</h2>
                            <p>{{ __('Confirm changes with your current password.') }}</p>
                        </div>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('settings.account.update') }}"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        @method('PUT')

                        <div class="settings-form-grid">
                            <div class="form-field">
                                <label for="account_name" class="form-label">
                                    {{ __('Full name') }} *
                                </label>
                                <input
                                    type="text"
                                    id="account_name"
                                    name="name"
                                    class="form-control"
                                    value="{{ old('name', $user->name) }}"
                                    maxlength="120"
                                    autocomplete="name"
                                    required
                                >
                            </div>

                            <div class="form-field">
                                <label for="account_email" class="form-label">
                                    {{ __('Email address') }} *
                                </label>
                                <input
                                    type="email"
                                    id="account_email"
                                    name="email"
                                    class="form-control"
                                    value="{{ old('email', $user->email) }}"
                                    maxlength="160"
                                    autocomplete="email"
                                    required
                                >
                            </div>

                            {{-- User Account — International Personal Phone --}}
                            <div class="form-field settings-field-wide">
                                <label for="account_phone" class="form-label">
                                    {{ __('Personal phone number') }}
                                </label>

                                <x-phone-input
                                    name="phone"
                                    id="account_phone"
                                    :value="$user->phone"
                                />
                            </div>

                            <div class="form-field settings-field-wide">
                                <label for="profile_photo" class="form-label">
                                    {{ $user->profile_photo_path
                                        ? 'Change profile photo'
                                        : 'Upload profile photo' }}
                                </label>
                                <input
                                    type="file"
                                    id="profile_photo"
                                    name="profile_photo"
                                    class="form-control"
                                    accept="image/png,image/jpeg,image/webp"
                                >
                                <small class="text-muted">
                                    PNG, JPG or WebP. Maximum 2 MB and 3000 × 3000 pixels.
                                </small>
                            </div>

                            @if($user->profile_photo_path)
                                <div class="form-field settings-field-wide">
                                    <div class="settings-toggle-row">
                                        <div>
                                            <label for="remove_photo">
                                                <strong>{{ __('Remove current photo') }}</strong>
                                            </label>
                                            <small>
                                                {{ __('Select this without choosing a new photo.') }}
                                            </small>
                                        </div>
                                        <input
                                            type="checkbox"
                                            id="remove_photo"
                                            name="remove_photo"
                                            class="form-check-input"
                                            value="1"
                                            @checked(old('remove_photo'))
                                        >
                                    </div>
                                </div>
                            @endif

                            <div class="form-field settings-field-wide">
                                <label for="account_current_password" class="form-label">
                                    {{ __('Current password') }} *
                                </label>
                                <input
                                    type="password"
                                    id="account_current_password"
                                    name="current_password"
                                    class="form-control"
                                    autocomplete="current-password"
                                    required
                                >
                            </div>
                        </div>

                        <div class="settings-panel-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle app-icon" aria-hidden="true"></i>
                                {{ __('Save changes') }}
                            </button>
                        </div>
                    </form>
                </section>

                {{-- User Account — Role and Status --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>{{ __('Role and access') }}</h2>
                            <p>{{ __('Your assigned clinic role and account status.') }}</p>
                        </div>
                    </div>

                    <div class="account-role-box">
                        <div class="account-role-icon">
                            <i class="bi bi-person-badge app-icon" aria-hidden="true"></i>
                        </div>

                        <div>
                            <strong>{{ __($roleName) }}</strong>
                            <small>{{ $clinic->name }}</small>
                        </div>

                        <span class="status-badge {{ $user->is_active ? 'active-badge' : 'due-badge' }}">
                            {{ $user->is_active ? __('Active') : __('Inactive') }}
                        </span>
                    </div>
                </section>
            </div>
        </section>
    </main>
</div>
@endsection