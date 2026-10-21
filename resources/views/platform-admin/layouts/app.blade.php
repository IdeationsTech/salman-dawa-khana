<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ur' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Platform Admin') | Platform Console</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts._language-script')
</head>

<body class="pa-app">
<div class="public-language-switcher"><x-language-switcher /></div>

    @php
        $platformAdmin = auth('platform_admin')->user();

        $navigation = [
            [
                'route' => 'platform-admin.dashboard',
                'match' => 'platform-admin.dashboard',
                'icon' => 'pa-icon-dashboard',
                'label' => __('Dashboard'),
            ],
            [
                'route' => 'platform-admin.clinics.index',
                'match' => 'platform-admin.clinics.*',
                'icon' => 'pa-icon-clinic',
                'label' => __('Clinics'),
            ],
            [
                'route' => 'platform-admin.licenses.index',
                'match' => 'platform-admin.licenses.*',
                'icon' => 'pa-icon-shield',
                'label' => 'Licenses',
            ],
            [
                'route' => 'platform-admin.payments.index',
                'match' => 'platform-admin.payments.*',
                'icon' => 'pa-icon-payments',
                'label' => __('Payments'),
            ],
            [
                'route' => 'platform-admin.plans.index',
                'match' => 'platform-admin.plans.*',
                'icon' => 'pa-icon-plans',
                'label' => __('Subscription plans'),
            ],
            [
                'route' => 'platform-admin.audit.index',
                'match' => 'platform-admin.audit.*',
                'icon' => 'pa-icon-clock',
                'label' => __('Audit log'),
            ],
            [
                'route' => 'platform-admin.account.index',
                'match' => 'platform-admin.account.*',
                'icon' => 'pa-icon-shield',
                'label' => __('Admin account'),
            ],
        ];
    @endphp

    <svg
        class="pa-symbols"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
        focusable="false"
    >
        <symbol id="pa-icon-shield" viewBox="0 0 24 24">
            <path d="M12 3 4 6v6c0 5 8 9 8 9s8-4 8-9V6l-8-3Z"/>
            <path d="m9 12 2 2 4-4"/>
        </symbol>

        <symbol id="pa-icon-dashboard" viewBox="0 0 24 24">
            <rect x="3" y="3" width="7" height="7" rx="1.5"/>
            <rect x="14" y="3" width="7" height="7" rx="1.5"/>
            <rect x="3" y="14" width="7" height="7" rx="1.5"/>
            <rect x="14" y="14" width="7" height="7" rx="1.5"/>
        </symbol>

        <symbol id="pa-icon-clinic" viewBox="0 0 24 24">
            <rect x="5" y="3" width="14" height="18" rx="2"/>
            <path d="M9 21v-5h6v5M9 7h6M12 4v6M9 12h.01M15 12h.01"/>
        </symbol>

        <symbol id="pa-icon-clock" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 7v5l3 2"/>
        </symbol>

        <symbol id="pa-icon-payments" viewBox="0 0 24 24">
            <rect x="3" y="5" width="18" height="14" rx="2"/>
            <path d="M3 10h18M7 15h4"/>
        </symbol>

        <symbol id="pa-icon-plans" viewBox="0 0 24 24">
            <rect x="5" y="3" width="14" height="18" rx="2"/>
            <path d="M9 7h6M9 12h6M9 17h4"/>
        </symbol>

        <symbol id="pa-icon-logout" viewBox="0 0 24 24">
            <path d="M9 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4"/>
            <path d="M9 12h12m-4-4 4 4-4 4"/>
        </symbol>
    </svg>

    <div class="pa-shell">
        <aside class="pa-sidebar">
            <a
                href="{{ route('platform-admin.dashboard') }}"
                class="pa-brand"
            >
                <span class="pa-brand-mark">
                    <svg class="pa-icon" aria-hidden="true">
                        <use href="#pa-icon-shield"></use>
                    </svg>
                </span>

                <span>
                    <span class="pa-brand-name">
                        {{ __('Platform Console') }}
                    </span>

                    <span class="pa-brand-caption">
                        {{ __('Clinic management platform') }}
                    </span>
                </span>
            </a>

            <p class="pa-nav-label">{{ __('Overview') }}</p>

            <nav class="pa-nav" aria-label="{{ __('Platform navigation') }}">
                @foreach($navigation as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="pa-nav-link {{ request()->routeIs($item['match']) ? 'is-active' : '' }}"
                        @if(request()->routeIs($item['match']))
                            aria-current="page"
                        @endif
                    >
                        <svg class="pa-icon" aria-hidden="true">
                            <use href="#{{ $item['icon'] }}"></use>
                        </svg>

                        <span>{{ __($item['label']) }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="pa-sidebar-footer">
                {{ __('Platform administration') }}
            </div>
        </aside>

        <div class="pa-workspace">
            <header class="pa-topbar">
                <div>
                    <p class="pa-topbar-title">
                        @yield('page_title', 'Dashboard')
                    </p>

                    <p class="pa-topbar-caption">
                        {{ __('Clinics, subscriptions and licenses') }}
                    </p>
                </div>

                <div class="pa-account">
                    <div class="pa-account-details">
                        <span class="pa-account-name">
                            {{ $platformAdmin->name }}
                        </span>

                        <span class="pa-account-role">
                            {{ __('Platform administrator') }}
                        </span>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('platform-admin.logout') }}"
                    >
                        @csrf

                        <button type="submit" class="pa-button">
                            <svg class="pa-icon" aria-hidden="true">
                                <use href="#pa-icon-logout"></use>
                            </svg>

                            <span>{{ __('Sign out') }}</span>
                        </button>
                    </form>
                </div>
            </header>

            <main class="pa-content" id="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>