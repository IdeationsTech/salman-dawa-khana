@extends('layouts.app')

@section('title', 'Settings — ' . $clinic->name)

@section('content')
@php
    $currentUser = auth()->user();

    $sidebarLinks = [
        ['url' => '/dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
        ['url' => '/patients', 'icon' => 'bi-person-vcard', 'label' => 'Patients'],
        ['url' => '/visits', 'icon' => 'bi-calendar-check', 'label' => 'Visits'],
        ['url' => '/prescriptions', 'icon' => 'bi-prescription', 'label' => 'Prescriptions'],
        ['url' => '/payments', 'icon' => 'bi-cash-coin', 'label' => 'Payments'],
        ['url' => '/expenses', 'icon' => 'bi-receipt', 'label' => 'Expenses'],
        ['url' => '/reports', 'icon' => 'bi-bar-chart-line', 'label' => 'Reports'],
        ['url' => '/settings', 'icon' => 'bi-gear', 'label' => 'Settings'],
    ];
@endphp

<div class="app-shell">

    {{-- Settings — Application Sidebar --}}
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">
                <i class="bi bi-hospital app-icon" aria-hidden="true"></i>
            </div>

            <span>{{ $clinic->name }}</span>
        </div>

        <nav class="sidebar-nav">
            @foreach($sidebarLinks as $link)
                @if($link['url'] === '/reports')
                    <div class="sidebar-divider"></div>
                @endif

                <a
                    href="{{ url($link['url']) }}"
                    class="sidebar-link {{ $link['url'] === '/settings' ? 'active' : '' }}"
                    @if($link['url'] === '/settings')
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

    {{-- Settings — Main Content --}}
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

            {{-- Settings — Navigation --}}
            <aside class="settings-navigation">
                <a
                    href="{{ route('settings.index') }}"
                    class="settings-nav-item active"
                    aria-current="page"
                >
                    <span>
                        <i class="bi bi-hospital app-icon" aria-hidden="true"></i>
                    </span>
                    Clinic profile
                </a>

                <a
                    href="{{ route('settings.payments') }}"
                    class="settings-nav-item"
                >
                    <span>
                        <i class="bi bi-cash-coin app-icon" aria-hidden="true"></i>
                    </span>
                    Payment settings
                </a>

                <a
                    href="{{ route('settings.account') }}"
                    class="settings-nav-item"
                >
                    <span>
                        <i class="bi bi-person-vcard app-icon" aria-hidden="true"></i>
                    </span>
                    User account
                </a>

                <a
                    href="{{ route('settings.notifications') }}"
                    class="settings-nav-item"
                >
                    <span>
                        <i class="bi bi-bell app-icon" aria-hidden="true"></i>
                    </span>
                    Notifications
                </a>

                <a
                    href="{{ route('settings.security') }}"
                    class="settings-nav-item"
                >
                    <span>
                        <i class="bi bi-shield-lock app-icon" aria-hidden="true"></i>
                    </span>
                    Security
                </a>
            </aside>

            <div class="settings-content">

                {{-- Settings — Clinic Profile Form --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Clinic profile</h2>

                            <p>
                                Update the information shown across your clinic system.
                            </p>
                        </div>

                        <div class="clinic-settings-logo">
                            @if($clinic->logo_path)
                                <img
                                    src="{{ asset('storage/' . $clinic->logo_path) }}"
                                    alt="{{ $clinic->name }} logo"
                                    style="width: 100%; height: 100%; object-fit: contain;"
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
                                    Phone number
                                </label>

                                <input
                                    type="tel"
                                    id="clinic_phone"
                                    name="clinic_phone"
                                    class="form-control @error('clinic_phone') is-invalid @enderror"
                                    value="{{ old('clinic_phone', $clinic->phone) }}"
                                    maxlength="30"
                                    autocomplete="tel"
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
                                    autocomplete="street-address"
                                >{{ old('clinic_address', $clinic->address) }}</textarea>

                                @error('clinic_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="settings-panel-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle app-icon" aria-hidden="true"></i>
                                Save clinic profile
                            </button>
                        </div>
                    </form>
                </section>

                {{-- Settings — Payment Preferences Link --}}
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

                {{-- Settings — Signed-In User --}}
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