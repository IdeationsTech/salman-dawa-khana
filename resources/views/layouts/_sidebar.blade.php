<aside class="app-sidebar no-print">
    <div class="sidebar-brand">
        <div class="sidebar-logo">+</div>
        <span>Salman Dawa Khana</span>
    </div>

    <nav class="sidebar-nav" aria-label="Main navigation">
        @foreach ([
            ['dashboard', 'dashboard', 'speedometer2', 'Dashboard'],
            ['patients.index', 'patients.*', 'person-vcard', 'Patients'],
            ['visits.index', 'visits.*', 'calendar-check', 'Visits'],
            ['prescriptions.index', 'prescriptions.*', 'prescription', 'Prescriptions'],
            ['payments.index', 'payments.*', 'cash-coin', 'Payments'],
            ['expenses.index', 'expenses.*', 'receipt', 'Expenses'],
            ['reports.index', 'reports.*', 'bar-chart-line', 'Reports'],
            ['users.index', 'users.*', 'people', 'Users'],
            ['settings.index', 'settings.*', 'gear', 'Settings'],
        ] as [$routeName, $pattern, $icon, $label])
            <a
                href="{{ route($routeName) }}"
                class="sidebar-link {{ request()->routeIs($pattern) ? 'active' : '' }}"
            >
                <i class="bi bi-{{ $icon }} app-icon" aria-hidden="true"></i>
                <span>{{ $label }}</span>
            </a>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <span class="status-dot"></span>
        System online
    </div>
</aside>