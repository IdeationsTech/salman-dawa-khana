@extends('layouts.app')

@section('title', 'Payment Settings — ' . $clinic->name)

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

    $preference = function ($key, $default = '1') use ($settings) {
        return old(
            $key,
            $settings->get($key)?->setting_value ?? $default
        );
    };

    $methodOptions = [
        [
            'key' => 'cash_enabled',
            'label' => 'Cash payments',
            'description' => 'Accept cash payments from patients.',
        ],
        [
            'key' => 'bank_enabled',
            'label' => 'Bank transfers',
            'description' => 'Record payments received by bank transfer.',
        ],
        [
            'key' => 'card_enabled',
            'label' => 'Card payments',
            'description' => 'Record payments received through card or POS.',
        ],
    ];

    $billingOptions = [
        [
            'key' => 'show_previous_due',
            'label' => 'Show previous due',
            'description' => 'Show the patient’s previous outstanding balance on receipts.',
        ],
        [
            'key' => 'allow_partial_payments',
            'label' => 'Allow partial payments',
            'description' => 'Accept less than the remaining bill balance.',
        ],
    ];
@endphp

<div class="app-shell">

    {{-- Payment Settings — Application Sidebar --}}
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

    {{-- Payment Settings — Main Content --}}
    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">Settings</p>
                <h1>Payment settings</h1>

                <p class="page-subtitle">
                    Configure currency and patient payment preferences.
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

            {{-- Payment Settings — Navigation --}}
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
                <form
                    method="POST"
                    action="{{ route('settings.payments.update') }}"
                >
                    @csrf
                    @method('PUT')

                    {{-- Payment Settings — Currency and Format --}}
                    <section class="settings-panel">
                        <div class="settings-panel-heading">
                            <div>
                                <h2>Currency and format</h2>
                                <p>Choose your clinic currency and amount display.</p>
                            </div>
                        </div>

                        <div class="settings-form-grid">
                            <div class="form-field">
                                <label for="currency" class="form-label">
                                    Clinic currency *
                                </label>

                                <select
                                    id="currency"
                                    name="currency"
                                    class="form-select"
                                    required
                                >
                                    @foreach([
                                        'AED' => 'AED — UAE Dirham',
                                        'PKR' => 'PKR — Pakistani Rupee',
                                        'USD' => 'USD — US Dollar',
                                    ] as $code => $label)
                                        <option
                                            value="{{ $code }}"
                                            @selected(old('currency', $clinic->currency) === $code)
                                        >
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-field">
                                <label for="decimal_places" class="form-label">
                                    Decimal places *
                                </label>

                                <select
                                    id="decimal_places"
                                    name="decimal_places"
                                    class="form-select"
                                    required
                                >
                                    <option
                                        value="2"
                                        @selected((string) $preference('decimal_places', '2') === '2')
                                    >
                                        2 decimal places
                                    </option>

                                    <option
                                        value="0"
                                        @selected((string) $preference('decimal_places', '2') === '0')
                                    >
                                        0 decimal places
                                    </option>
                                </select>
                            </div>

                            <div class="form-field settings-field-wide">
                                <small class="text-muted">
                                    Changing currency does not convert existing amounts.
                                    Decimal places affect display only.
                                </small>
                            </div>
                        </div>
                    </section>

                    {{-- Payment Settings — Accepted Methods --}}
                    <section class="settings-panel">
                        <div class="settings-panel-heading">
                            <div>
                                <h2>Payment methods</h2>
                                <p>Keep at least one payment method enabled.</p>
                            </div>
                        </div>

                        <div class="settings-toggle-list">
                            @foreach($methodOptions as $option)
                                <div class="settings-toggle-row">
                                    <div>
                                        <label for="{{ $option['key'] }}">
                                            <strong>{{ $option['label'] }}</strong>
                                        </label>

                                        <small>{{ $option['description'] }}</small>
                                    </div>

                                    <input
                                        type="hidden"
                                        name="{{ $option['key'] }}"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        id="{{ $option['key'] }}"
                                        name="{{ $option['key'] }}"
                                        class="form-check-input"
                                        value="1"
                                        @checked((string) $preference($option['key']) === '1')
                                    >
                                </div>
                            @endforeach
                        </div>
                    </section>

                    {{-- Payment Settings — Billing Preferences --}}
                    <section class="settings-panel">
                        <div class="settings-panel-heading">
                            <div>
                                <h2>Billing preferences</h2>
                                <p>Configure balance display and payment entry.</p>
                            </div>
                        </div>

                        <div class="settings-toggle-list">
                            @foreach($billingOptions as $option)
                                <div class="settings-toggle-row">
                                    <div>
                                        <label for="{{ $option['key'] }}">
                                            <strong>{{ $option['label'] }}</strong>
                                        </label>

                                        <small>{{ $option['description'] }}</small>
                                    </div>

                                    <input
                                        type="hidden"
                                        name="{{ $option['key'] }}"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        id="{{ $option['key'] }}"
                                        name="{{ $option['key'] }}"
                                        class="form-check-input"
                                        value="1"
                                        @checked((string) $preference($option['key']) === '1')
                                    >
                                </div>
                            @endforeach
                        </div>

                        <div class="settings-panel-actions">
                            <button type="submit" class="btn btn-primary">
                                <i
                                    class="bi bi-check2-circle app-icon"
                                    aria-hidden="true"
                                ></i>
                                Save payment settings
                            </button>
                        </div>
                    </section>
                </form>
            </div>
        </section>
    </main>
</div>
@endsection