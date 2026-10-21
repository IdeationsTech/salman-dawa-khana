@extends('layouts.app')

@section('title', 'Notifications — ' . $clinic->name)

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

    $preference = fn ($key, $default = '1') => old(
        $key,
        $settings->get($key)?->setting_value ?? $default
    );

    $alerts = [
        [
            'key' => 'payment_alert_enabled',
            'label' => __('Payment received'),
            'description' => 'Show an in-app alert when a patient payment is recorded.',
        ],
        [
            'key' => 'patient_alert_enabled',
            'label' => 'New patient registration',
            'description' => 'Show an in-app alert when a patient is added.',
        ],
    ];
@endphp

<div class="app-shell">

    {{-- Notifications — Application Sidebar --}}
    @include('layouts._sidebar')
    
    {{-- Notifications — Main Content --}}
    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">{{ __('Settings') }}</p>
                <h1>{{ __('Notifications') }}</h1>
                <a
                    href="{{ route('notifications.index') }}"
                    class="btn btn-outline-secondary mt-2"
                >
                    <i class="bi bi-bell app-icon" aria-hidden="true"></i>
                    {{ __('View clinic notifications') }}
                </a>

                <p class="page-subtitle">
                    {{ __('Configure clinic alerts and daily summary preferences.') }}
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
                <form
                    method="POST"
                    action="{{ route('settings.notifications.update') }}"
                >
                    @csrf
                    @method('PUT')

                    {{-- Notifications — In-App Channel --}}
                    <section class="settings-panel">
                        <div class="settings-panel-heading">
                            <div>
                                <h2>{{ __('In-app notifications') }}</h2>
                                <p>{{ __('Routine clinic alerts appear inside the application.') }}</p>
                            </div>
                        </div>

                        <div class="settings-toggle-row">
                            <div>
                                <label for="in_app_enabled">
                                    <strong>{{ __('Enable in-app notifications') }}</strong>
                                </label>

                                <small>
                                    {{ __('Turn clinic alerts on or off.') }}
                                </small>
                            </div>

                            <input
                                type="hidden"
                                name="in_app_enabled"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                id="in_app_enabled"
                                name="in_app_enabled"
                                class="form-check-input"
                                value="1"
                                @checked((string) $preference('in_app_enabled') === '1')
                            >
                        </div>
                    </section>

                    {{-- Notifications — Clinic Alert Preferences --}}
                    <section class="settings-panel">
                        <div class="settings-panel-heading">
                            <div>
                                <h2>{{ __('Clinic alerts') }}</h2>
                                <p>{{ __('Choose which in-app alerts your clinic receives.') }}</p>
                            </div>
                        </div>

                        <div class="settings-toggle-list">
                            @foreach($alerts as $alert)
                                <div class="settings-toggle-row">
                                    <div>
                                        <label for="{{ $alert['key'] }}">
                                            <strong>{{ $alert['label'] }}</strong>
                                        </label>

                                        <small>{{ $alert['description'] }}</small>
                                    </div>

                                    <input
                                        type="hidden"
                                        name="{{ $alert['key'] }}"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        id="{{ $alert['key'] }}"
                                        name="{{ $alert['key'] }}"
                                        class="form-check-input"
                                        value="1"
                                        @checked((string) $preference($alert['key']) === '1')
                                    >
                                </div>
                            @endforeach
                        </div>
                    </section>

                    {{-- Notifications — Daily Email Summary --}}
                    <section class="settings-panel">
                        <div class="settings-panel-heading">
                            <div>
                                <h2>{{ __('Daily email summary') }}</h2>
                                <p>{{ __('Receive one combined clinic summary per day.') }}</p>
                            </div>
                        </div>

                        <div class="settings-form-grid">
                            <div class="form-field settings-field-wide">
                                <label for="notification_email" class="form-label">
                                    {{ __('Summary email address') }}
                                </label>

                                <input
                                    type="email"
                                    id="notification_email"
                                    name="notification_email"
                                    class="form-control"
                                    value="{{ old(
                                        'notification_email',
                                        $settings->get('notification_email')?->setting_value
                                            ?? auth()->user()->email
                                    ) }}"
                                    maxlength="160"
                                    autocomplete="email"
                                >
                            </div>
                        </div>

                        <div class="settings-toggle-row">
                            <div>
                                <label for="daily_summary_enabled">
                                    <strong>{{ __('Send daily clinic summary') }}</strong>
                                </label>

                                <small>
                                    {{ __('Include daily visits, collections and expenses.') }}
                                </small>
                            </div>

                            <input
                                type="hidden"
                                name="daily_summary_enabled"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                id="daily_summary_enabled"
                                name="daily_summary_enabled"
                                class="form-check-input"
                                value="1"
                                @checked((string) $preference('daily_summary_enabled', '0') === '1')
                            >
                        </div>

                        <div class="settings-panel-actions">
                            <button type="submit" class="btn btn-primary">
                                <i
                                    class="bi bi-check2-circle app-icon"
                                    aria-hidden="true"
                                ></i>
                                {{ __('Save notification settings') }}
                            </button>
                        </div>
                    </section>
                </form>
            </div>
        </section>
    </main>
</div>
@endsection