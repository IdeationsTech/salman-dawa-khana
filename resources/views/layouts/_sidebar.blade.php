@php
    $sidebarUser = auth()->user();
    $sidebarClinic = $sidebarUser->clinic;
    $sidebarRole = $sidebarUser->role?->name;

    $sidebarNavigation = [
        ['dashboard', 'dashboard', 'speedometer2', 'Dashboard'],
        ['patients.index', 'patients.*', 'person-vcard', 'Patients'],
        ['visits.index', 'visits.*', 'calendar-check', 'Visits'],
        ['prescriptions.index', 'prescriptions.*', 'prescription', 'Prescriptions'],
        ['orders.index', 'orders.*', 'bag-check', 'Nuskha Orders'],
        ['payments.index', 'payments.*', 'cash-coin', 'Payments'],
        ['expenses.index', 'expenses.*', 'receipt', 'Expenses'],
        ['reports.index', 'reports.*', 'bar-chart-line', 'Reports'],
        ['notifications.index', 'notifications.*', 'bell', 'Notifications'],
        ['users.index', 'users.*', 'people', 'Users'],
        ['audit.index', 'audit.*', 'clock-history', 'Activity history'],
        ['settings.index', 'settings.*', 'gear', 'Settings'],
    ];
@endphp

<div class="clinic-mobile-bar no-print">
    <button type="button" class="clinic-menu-toggle" data-bs-toggle="offcanvas" data-bs-target="#clinic-sidebar" aria-controls="clinic-sidebar" aria-expanded="false" aria-label="{{ __('Open navigation') }}">
        <i class="bi bi-list" aria-hidden="true"></i>
    </button>
    <span>{{ $sidebarClinic->name }}</span>
    <x-language-switcher />
</div>

<aside id="clinic-sidebar" class="app-sidebar offcanvas-lg offcanvas-start no-print" tabindex="-1" aria-labelledby="clinic-sidebar-title">
    <div class="clinic-sidebar-heading">
    <div class="sidebar-brand">
        <div class="sidebar-logo">
            @if ($sidebarClinic->logo_path)
                <img
                    src="{{ asset('storage/' . $sidebarClinic->logo_path) }}"
                    alt="{{ $sidebarClinic->name }} logo"
                    class="settings-logo-image"
                >
            @else
                <i class="bi bi-hospital app-icon" aria-hidden="true"></i>
            @endif
        </div>
        <span id="clinic-sidebar-title">{{ $sidebarClinic->name }}</span>
    </div>
        <button type="button" class="btn-close btn-close-white clinic-sidebar-close" data-bs-dismiss="offcanvas" data-bs-target="#clinic-sidebar" aria-label="{{ __('Close navigation') }}"></button>
    </div>
    <div class="clinic-sidebar-body">

    <nav class="sidebar-nav" aria-label="{{ __('Main navigation') }}">
        @foreach ($sidebarNavigation as [$routeName, $pattern, $icon, $label])
            @if (!\App\Support\ClinicPermissions::allows(auth()->user(), $routeName)) @continue @endif
            @if (in_array($routeName, ['users.index', 'audit.index'], true) && $sidebarRole !== 'Owner')
                @continue
            @endif

            @if (
                in_array($routeName, ['expenses.index', 'reports.index'], true)
                && !in_array($sidebarRole, ['Owner', 'Doctor', 'Accountant'], true)
            )
                @continue
            @endif

            @if ($routeName === 'reports.index')
                <div class="sidebar-divider"></div>
            @endif

            <a
                href="{{ route($routeName) }}"
                class="sidebar-link {{ request()->routeIs($pattern) ? __('active') : '' }}"
                @if (request()->routeIs($pattern)) aria-current="page" @endif
            >
                <span><i class="bi bi-{{ $icon }} app-icon" aria-hidden="true"></i></span>
                <span>{{ __($label) }}</span>
            </a>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <x-language-switcher />
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-signout">
                <i class="bi bi-box-arrow-right app-icon" aria-hidden="true"></i>
                <span>{{ __('Sign out') }}</span>
            </button>
        </form>
    </div>
    </div>
</aside>