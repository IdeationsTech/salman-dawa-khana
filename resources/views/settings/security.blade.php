@extends('layouts.app')

@section('title', 'Security — ' . $clinic->name)

@section('content')
@php
    $sidebarLinks = [
        ['route' => 'dashboard', 'icon' => 'bi-speedometer2', 'label' => __('Dashboard')],
        ['route' => 'patients.index', 'icon' => 'bi-person-vcard', 'label' => __('Patients')],
        ['route' => 'visits.index', 'icon' => 'bi-calendar-check', 'label' => __('Visits')],
        ['route' => 'prescriptions.index', 'icon' => 'bi-prescription', 'label' => __('Prescriptions')],
        ['route' => 'payments.index', 'icon' => 'bi-cash-coin', 'label' => __('Payments')],
        ['route' => 'expenses.index', 'icon' => 'bi-receipt', 'label' => __('Expenses')],
        ['route' => 'reports.index', 'icon' => 'bi-bar-chart-line', 'label' => __('Reports')],
        ['route' => 'settings.index', 'icon' => 'bi-gear', 'label' => __('Settings')],
    ];

    $settingsLinks = [
        ['route' => 'settings.index', 'icon' => 'bi-hospital', 'label' => __('Clinic profile')],
        ['route' => 'settings.payments', 'icon' => 'bi-cash-coin', 'label' => __('Payment settings')],
        ['route' => 'settings.account', 'icon' => 'bi-person-vcard', 'label' => __('User account')],
        ['route' => 'settings.notifications', 'icon' => 'bi-bell', 'label' => __('Notifications')],
        ['route' => 'settings.security', 'icon' => 'bi-shield-lock', 'label' => __('Security')],
    ];
@endphp

<div class="app-shell">

    {{-- Security — Application Sidebar --}}
    @include('layouts._sidebar')
    
    {{-- Security — Main Content --}}
    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">{{ __('Settings') }}</p>
                <h1>{{ __('Security') }}</h1>

                <p class="page-subtitle">
                    {{ __('Manage your account password.') }}
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

            {{-- Security — Settings Navigation --}}
            <aside class="settings-navigation">
                @foreach($settingsLinks as $link)
                    <a
                        href="{{ route($link['route']) }}"
                        class="settings-nav-item {{ request()->routeIs($link['route']) ? __('active') : '' }}"
                        @if(request()->routeIs($link['route']))
                            aria-current="page"
                        @endif
                    >
                        <span>
                            <i
                                class="bi {{ $link['icon'] }} app-icon"
                                aria-hidden="true"
                            ></i>
                        </span>

                        {{ $link['label'] }}
                    </a>
                @endforeach
            </aside>

            <div class="settings-content">

                {{-- Security — Change Password Form --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>{{ __('Change password') }}</h2>
                            <p>{{ __('Confirm your current password before choosing a new one.') }}</p>
                        </div>

                        <div class="security-icon">
                            <i
                                class="bi bi-shield-lock app-icon"
                                aria-hidden="true"
                            ></i>
                        </div>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('settings.security.password.update') }}"
                    >
                        @csrf
                        @method('PUT')

                        <div class="settings-form-grid">
                            <div class="form-field settings-field-wide">
                                <label for="current_password" class="form-label">
                                    {{ __('Current password') }} *
                                </label>

                                <input
                                    type="password"
                                    id="current_password"
                                    name="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    autocomplete="current-password"
                                    required
                                >

                                @error('current_password')
                                    <div class="invalid-feedback">{{ __($message) }}</div>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="new_password" class="form-label">
                                    {{ __('New password') }} *
                                </label>

                                <input
                                    type="password"
                                    id="new_password"
                                    name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    minlength="8"
                                    autocomplete="new-password"
                                    required
                                >

                                @error('password')
                                    <div class="invalid-feedback">{{ __($message) }}</div>
                                @enderror
                            </div>

                            <div class="form-field">
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
                        </div>

                        <div class="password-requirements">
                            <strong>{{ __('Password requirements') }}</strong>

                            <ul>
                                <li>{{ __('At least 8 characters.') }}</li>
                                <li>{{ __('Different from your current password.') }}</li>
                                <li>{{ __('Both new-password fields must match.') }}</li>
                            </ul>
                        </div>

                        <div class="settings-panel-actions">
                            <button type="submit" class="btn btn-primary">
                                <i
                                    class="bi bi-shield-check app-icon"
                                    aria-hidden="true"
                                ></i>
                                {{ __('Update password') }}
                            </button>
                        </div>
                    </form>
                </section>

                {{-- Security — Current Account --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>{{ __('Signed-in account') }}</h2>
                            <p>{{ $user->name }} · {{ $user->email }}</p>
                        </div>
                    </div>

                    <div class="settings-panel-actions">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button
                                type="submit"
                                class="btn btn-outline-secondary"
                            >
                                <i
                                    class="bi bi-box-arrow-right app-icon"
                                    aria-hidden="true"
                                ></i>
                                {{ __('Sign out') }}
                            </button>
                        </form>
                    </div>
                </section>

            </div>
        </section>
        @if (auth()->user()->role?->name === 'Owner')
            <section class="settings-panel mt-4">
                <h2>{{ __('Encrypted backups') }}</h2>
                <p>{{ __('Server backups include the database and uploaded clinic files. The daily schedule keeps 30 days of backups.') }}</p>
                <p>{{ __('Server operator: run') }} <code>php artisan clinic:backup</code> {{ __('to create a backup. Run') }} <code>php artisan clinic:restore /absolute/path/to/file.backup</code> {{ __('to verify one without changing data.') }}</p>
                <p>{{ __('Enable the Laravel scheduler on your server with') }} <code>php artisan schedule:run</code> every minute. Keep a secure copy of backups and the original APP_KEY outside the server.</p>
            </section>
        @endif
    </main>
</div>
@endsection