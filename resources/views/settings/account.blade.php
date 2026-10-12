@extends('layouts.app')

@section('title', 'User Account — ' . $clinic->name)

@section('content')
@php
    $roleName = $user->role?->name ?? 'Staff';

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

    {{-- User Account — Application Sidebar --}}
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">
                <i class="bi bi-hospital app-icon" aria-hidden="true"></i>
            </div>

            <span>{{ $clinic->name }}</span>
        </div>

        <nav class="sidebar-nav">
            @foreach($sidebarLinks as $link)
                @if($link['route'] === 'reports.index')
                    <div class="sidebar-divider"></div>
                @endif

                <a
                    href="{{ route($link['route']) }}"
                    class="sidebar-link {{ $link['route'] === 'settings.index' ? 'active' : '' }}"
                    @if($link['route'] === 'settings.index')
                        aria-current="page"
                    @endif
                >
                    <span>
                        <i
                            class="bi {{ $link['icon'] }} app-icon"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    {{-- User Account — Main Content --}}
    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">Settings</p>
                <h1>User account</h1>

                <p class="page-subtitle">
                    Manage your personal account information.
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

            {{-- User Account — Settings Navigation --}}
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

                {{-- User Account — Profile Summary --}}
                <section class="settings-panel account-profile-card">
                    <div class="account-profile-avatar">
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    </div>

                    <div class="account-profile-info">
                        <h2>{{ $user->name }}</h2>
                        <p>{{ $roleName }}</p>

                        @if($user->created_at)
                            <span>
                                Member since {{ $user->created_at->format('F Y') }}
                            </span>
                        @endif
                    </div>
                </section>

                {{-- User Account — Personal Information Form --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Personal information</h2>
                            <p>Update your name and login email address.</p>
                        </div>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('settings.account.update') }}"
                    >
                        @csrf
                        @method('PUT')

                        <div class="settings-form-grid">
                            <div class="form-field">
                                <label for="account_name" class="form-label">
                                    Full name *
                                </label>

                                <input
                                    type="text"
                                    id="account_name"
                                    name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $user->name) }}"
                                    maxlength="120"
                                    autocomplete="name"
                                    required
                                >

                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="account_email" class="form-label">
                                    Email address *
                                </label>

                                <input
                                    type="email"
                                    id="account_email"
                                    name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}"
                                    maxlength="160"
                                    autocomplete="email"
                                    required
                                >

                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-field settings-field-wide">
                                <label for="account_current_password" class="form-label">
                                    Current password *
                                </label>

                                <input
                                    type="password"
                                    id="account_current_password"
                                    name="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    autocomplete="current-password"
                                    required
                                >

                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="settings-panel-actions">
                            <button type="submit" class="btn btn-primary">
                                <i
                                    class="bi bi-check2-circle app-icon"
                                    aria-hidden="true"
                                ></i>
                                Save changes
                            </button>
                        </div>
                    </form>
                </section>

                {{-- User Account — Role Information --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Role and access</h2>
                            <p>Your assigned clinic role and account status.</p>
                        </div>
                    </div>

                    <div class="account-role-box">
                        <div class="account-role-icon">
                            <i
                                class="bi bi-person-badge app-icon"
                                aria-hidden="true"
                            ></i>
                        </div>

                        <div>
                            <strong>{{ $roleName }}</strong>
                            <small>{{ $clinic->name }}</small>
                        </div>

                        <span
                            class="status-badge {{ $user->is_active ? 'active-badge' : 'due-badge' }}"
                        >
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </section>

            </div>
        </section>
    </main>
</div>
@endsection