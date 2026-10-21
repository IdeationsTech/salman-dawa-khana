@extends('platform-admin.layouts.app')

@section('title', 'Admin Account')
@section('page_title', 'Admin Account')

@section('content')
    {{-- Admin Account — Page Heading --}}
    <div class="pa-page-heading">
        <div>
            <h1>{{ __('Admin account') }}</h1>
            <p>{{ __('Manage your profile and account password.') }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="pa-notice pa-notice-success" role="status">
            {{ __(session('success')) }}
        </div>
    @endif

    @if($errors->any())
        <div class="pa-notice pa-notice-error" role="alert">
            <strong>{{ __('Please correct the following:') }}</strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ __($error) }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="pa-detail-grid">
        {{-- Admin Account — Profile Settings --}}
        <section class="pa-panel">
            <div class="pa-panel-header">
                <h2>{{ __('Profile details') }}</h2>
                <p>{{ __('Update the name and email used for your admin account.') }}</p>
            </div>

            <div class="pa-panel-body">
                <form
                    method="POST"
                    action="{{ route('platform-admin.account.profile.update') }}"
                    class="pa-form"
                >
                    @csrf
                    @method('PUT')

                    <div class="pa-field">
                        <label for="admin-name">{{ __('Full name') }} *</label>

                        <input
                            type="text"
                            id="admin-name"
                            name="name"
                            class="pa-input"
                            value="{{ old('name', $admin->name) }}"
                            maxlength="120"
                            autocomplete="name"
                            required
                        >
                    </div>

                    <div class="pa-field">
                        <label for="admin-email">{{ __('Email address') }} *</label>

                        <input
                            type="email"
                            id="admin-email"
                            name="email"
                            class="pa-input"
                            value="{{ old('email', $admin->email) }}"
                            maxlength="160"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="pa-field">
                        <label for="profile-current-password">
                            {{ __('Current password') }} *
                        </label>

                        <input
                            type="password"
                            id="profile-current-password"
                            name="profile_current_password"
                            class="pa-input"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="pa-button pa-button-primary"
                        >
                            {{ __('Save profile') }}
                        </button>
                    </div>
                </form>
            </div>
        </section>

        {{-- Admin Account — Password Settings --}}
        <section class="pa-panel">
            <div class="pa-panel-header">
                <h2>{{ __('Change password') }}</h2>
                <p>{{ __('Your new password must contain at least 12 characters.') }}</p>
            </div>

            <div class="pa-panel-body">
                <form
                    method="POST"
                    action="{{ route('platform-admin.account.password.update') }}"
                    class="pa-form"
                >
                    @csrf
                    @method('PUT')

                    <div class="pa-field">
                        <label for="current-password">
                            {{ __('Current password') }} *
                        </label>

                        <input
                            type="password"
                            id="current-password"
                            name="current_password"
                            class="pa-input"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <div class="pa-field">
                        <label for="new-password">{{ __('New password') }} *</label>

                        <input
                            type="password"
                            id="new-password"
                            name="password"
                            class="pa-input"
                            minlength="12"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <div class="pa-field">
                        <label for="password-confirmation">
                            {{ __('Confirm new password') }} *
                        </label>

                        <input
                            type="password"
                            id="password-confirmation"
                            name="password_confirmation"
                            class="pa-input"
                            minlength="12"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="pa-button pa-button-primary"
                        >
                            {{ __('Change password') }}
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection