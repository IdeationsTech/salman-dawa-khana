@extends('layouts.app')

@section('title', 'Security — ' . $clinic->name)

@section('content')
@php
    $sidebarLinks = [
        ['route' => 'dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
        ['route' => 'patients.index', 'icon' => 'bi-person-vcard', 'label' => 'Patients'],
        ['route' => 'visits.index', 'icon' => 'bi-calendar-check', 'label' => 'Visits'],
        ['route' => 'prescriptions.index', 'icon' => 'bi-prescription', 'label' => 'Prescriptions'],
        ['route' => 'payments.index', 'icon' => 'bi-cash-coin', 'label' => 'Payments'],
        ['route' => 'expenses.index', 'icon' => 'bi-receipt', 'label' => 'Expenses'],
        ['route' => 'reports.index', 'icon' => 'bi-bar-chart-line', 'label' => 'Reports'],
        ['route' => 'settings.index', 'icon' => 'bi-gear', 'label' => 'Settings'],
    ];

    $settingsLinks = [
        ['route' => 'settings.index', 'icon' => 'bi-hospital', 'label' => 'Clinic profile'],
        ['route' => 'settings.payments', 'icon' => 'bi-cash-coin', 'label' => 'Payment settings'],
        ['route' => 'settings.account', 'icon' => 'bi-person-vcard', 'label' => 'User account'],
        ['route' => 'settings.notifications', 'icon' => 'bi-bell', 'label' => 'Notifications'],
        ['route' => 'settings.security', 'icon' => 'bi-shield-lock', 'label' => 'Security'],
    ];
@endphp

<div class="app-shell">

    {{-- Security — Application Sidebar --}}
    @include('layouts._sidebar')
    
    {{-- Security — Main Content --}}
    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">Settings</p>
                <h1>Security</h1>

                <p class="page-subtitle">
                    Manage your account password.
                </p>
            </div>
        </header>

        @if(session('success'))
            <div class="alert alert-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Please correct the following:</strong>

                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
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
                        class="settings-nav-item {{ request()->routeIs($link['route']) ? 'active' : '' }}"
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
                            <h2>Change password</h2>
                            <p>Confirm your current password before choosing a new one.</p>
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
                                    Current password *
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
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="new_password" class="form-label">
                                    New password *
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
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="password_confirmation" class="form-label">
                                    Confirm new password *
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
                            <strong>Password requirements</strong>

                            <ul>
                                <li>At least 8 characters.</li>
                                <li>Different from your current password.</li>
                                <li>Both new-password fields must match.</li>
                            </ul>
                        </div>

                        <div class="settings-panel-actions">
                            <button type="submit" class="btn btn-primary">
                                <i
                                    class="bi bi-shield-check app-icon"
                                    aria-hidden="true"
                                ></i>
                                Update password
                            </button>
                        </div>
                    </form>
                </section>

                {{-- Security — Current Account --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Signed-in account</h2>
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
                                Sign out
                            </button>
                        </form>
                    </div>
                </section>

            </div>
        </section>
    </main>
</div>
@endsection