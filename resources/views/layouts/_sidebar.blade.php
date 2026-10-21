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

<aside class="app-sidebar no-print">
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
        <span>{{ $sidebarClinic->name }}</span>
    </div>

    <nav class="sidebar-nav" aria-label="Main navigation">
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
                class="sidebar-link {{ request()->routeIs($pattern) ? 'active' : '' }}"
                @if (request()->routeIs($pattern)) aria-current="page" @endif
            >
                <span><i class="bi bi-{{ $icon }} app-icon" aria-hidden="true"></i></span>
                <span>{{ $label }}</span>
            </a>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-signout">
                <i class="bi bi-box-arrow-right app-icon" aria-hidden="true"></i>
                <span>Sign out</span>
            </button>
        </form>
    </div>
</aside>