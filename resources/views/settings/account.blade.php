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
                @if($clinic->logo_path)
                    <img
                        src="{{ asset('storage/' . $clinic->logo_path) }}"
                        alt="{{ $clinic->name }} logo"
                        class="settings-logo-image"
                    >
                @else
                    <i class="bi bi-hospital app-icon" aria-hidden="true"></i>
                @endif
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
                    Manage your personal information and profile photo.
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
                        <p>{{ $roleName }}</p>

                        @if($user->created_at)
                            <span>
                                Member since {{ $user->created_at->format('F Y') }}
                            </span>
                        @endif
                    </div>
                </section>

                {{-- User Account — Profile Form --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Personal information</h2>
                            <p>Update your details and confirm with your current password.</p>
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
                                    Full name *
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
                                    Email address *
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

                            <div class="form-field settings-field-wide">
                                <label for="account_phone" class="form-label">
                                    Personal phone number
                                </label>

                                <input
                                    type="tel"
                                    id="account_phone"
                                    name="phone"
                                    class="form-control"
                                    value="{{ old('phone', $user->phone) }}"
                                    maxlength="30"
                                    autocomplete="tel"
                                >
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
                                                <strong>Remove current photo</strong>
                                            </label>

                                            <small>
                                                Select this without choosing a new photo.
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
                                    Current password *
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
                                <i
                                    class="bi bi-check2-circle app-icon"
                                    aria-hidden="true"
                                ></i>
                                Save changes
                            </button>
                        </div>
                    </form>
                </section>

                {{-- User Account — Role and Status --}}
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