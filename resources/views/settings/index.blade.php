@extends('layouts.app')

@section('title', 'Settings — ' . $clinic->name)

@section('content')
@php
    $currentUser = auth()->user();

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

    {{-- Clinic Settings — Application Sidebar --}}
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

    {{-- Clinic Settings — Main Content --}}
    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">Application configuration</p>
                <h1>Settings</h1>

                <p class="page-subtitle">
                    Manage your clinic profile and system preferences.
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

            {{-- Clinic Settings — Navigation --}}
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

                {{-- Clinic Settings — Profile and Logo Form --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Clinic profile</h2>
                            <p>Update your clinic details and logo.</p>
                        </div>

                        <div class="clinic-settings-logo">
                            @if($clinic->logo_path)
                                <img
                                    src="{{ asset('storage/' . $clinic->logo_path) }}"
                                    alt="{{ $clinic->name }} logo"
                                    class="settings-logo-image"
                                >
                            @else
                                <i
                                    class="bi bi-hospital app-icon"
                                    aria-hidden="true"
                                ></i>
                            @endif
                        </div>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('settings.update') }}"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        @method('PUT')

                        <div class="settings-form-grid">
                            <div class="form-field settings-field-wide">
                                <label for="clinic_name" class="form-label">
                                    Clinic name *
                                </label>

                                <input
                                    type="text"
                                    id="clinic_name"
                                    name="clinic_name"
                                    class="form-control @error('clinic_name') is-invalid @enderror"
                                    value="{{ old('clinic_name', $clinic->name) }}"
                                    maxlength="160"
                                    required
                                >

                                @error('clinic_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="clinic_phone" class="form-label">
                                    Phone number *
                                </label>

                                <input
                                    type="tel"
                                    id="clinic_phone"
                                    name="clinic_phone"
                                    class="form-control @error('clinic_phone') is-invalid @enderror"
                                    value="{{ old('clinic_phone', $clinic->phone) }}"
                                    maxlength="30"
                                    autocomplete="tel"
                                    required
                                >

                                @error('clinic_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="account_email" class="form-label">
                                    Your account email
                                </label>

                                <input
                                    type="email"
                                    id="account_email"
                                    class="form-control"
                                    value="{{ $currentUser->email }}"
                                    readonly
                                >

                                <small class="text-muted">
                                    Manage your email in User account.
                                </small>
                            </div>

                            <div class="form-field settings-field-wide">
                                <label for="clinic_address" class="form-label">
                                    Clinic address
                                </label>

                                <textarea
                                    id="clinic_address"
                                    name="clinic_address"
                                    class="form-control @error('clinic_address') is-invalid @enderror"
                                    rows="3"
                                    maxlength="255"
                                >{{ old('clinic_address', $clinic->address) }}</textarea>

                                @error('clinic_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-field settings-field-wide">
                                <label for="clinic_logo" class="form-label">
                                    {{ $clinic->logo_path ? 'Change clinic logo' : 'Upload clinic logo' }}
                                </label>

                                <input
                                    type="file"
                                    id="clinic_logo"
                                    name="clinic_logo"
                                    class="form-control @error('clinic_logo') is-invalid @enderror"
                                    accept="image/png,image/jpeg,image/webp"
                                >

                                <small class="text-muted">
                                    PNG, JPG or WebP. Maximum 2 MB and 3000 × 3000 pixels.
                                </small>

                                @error('clinic_logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if($clinic->logo_path)
                                <div class="form-field settings-field-wide">
                                    <div class="settings-toggle-row">
                                        <div>
                                            <label for="remove_logo">
                                                <strong>Remove current logo</strong>
                                            </label>

                                            <small>
                                                Select this without choosing a new file.
                                            </small>
                                        </div>

                                        <input
                                            type="checkbox"
                                            id="remove_logo"
                                            name="remove_logo"
                                            class="form-check-input"
                                            value="1"
                                            @checked(old('remove_logo'))
                                        >
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="settings-panel-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle app-icon" aria-hidden="true"></i>
                                Save clinic profile
                            </button>
                        </div>
                    </form>
                </section>

                {{-- Clinic Settings — Payment Preferences --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Payment settings</h2>

                            <p>
                                Current clinic currency:
                                <strong>{{ $clinic->currency }}</strong>
                            </p>
                        </div>
                    </div>

                    <div class="settings-panel-actions">
                        <a
                            href="{{ route('settings.payments') }}"
                            class="btn btn-outline-secondary"
                        >
                            <i class="bi bi-cash-coin app-icon" aria-hidden="true"></i>
                            Open payment settings
                        </a>
                    </div>
                </section>

                {{-- Clinic Settings — Current User --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>User account</h2>
                            <p>Details of the currently signed-in user.</p>
                        </div>
                    </div>

                    <div class="settings-account-row">
                        <div class="settings-account-avatar">
                            {{ mb_strtoupper(mb_substr($currentUser->name, 0, 1)) }}
                        </div>

                        <div>
                            <strong>{{ $currentUser->name }}</strong>
                            <small>{{ $currentUser->email }}</small>
                        </div>

                        <a
                            href="{{ route('settings.account') }}"
                            class="btn btn-outline-secondary ms-auto"
                        >
                            Edit profile
                        </a>
                    </div>
                </section>

            </div>
        </section>
    </main>
</div>
@endsection