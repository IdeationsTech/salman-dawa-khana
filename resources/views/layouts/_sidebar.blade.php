<aside class="app-sidebar no-print">
    <div class="sidebar-brand">
        <div class="sidebar-logo">+</div>
        <span>Salman Dawa Khana</span>
    </div>

    <nav class="sidebar-nav" aria-label="Main navigation">
        @foreach ([
            ['dashboard', 'dashboard', '▦', 'Dashboard'],
            ['patients.index', 'patients.*', '♙', 'Patients'],
            ['visits.index', 'visits.*', '▣', 'Visits'],
            ['prescriptions.index', 'prescriptions.*', '✎', 'Prescriptions'],
            ['payments.index', 'payments.*', '₨', 'Payments'],
            ['expenses.index', 'expenses.*', '◈', 'Expenses'],
            ['reports.index', 'reports.*', '◌', 'Reports'],
            ['users.index', 'users.*', '♧', 'Users'],
            ['settings.index', 'settings.*', '⚙', 'Settings'],
        ] as [$routeName, $pattern, $icon, $label])
            <a
                href="{{ route($routeName) }}"
                class="sidebar-link {{ request()->routeIs($pattern) ? 'active' : '' }}"
            >
                <span aria-hidden="true">{{ $icon }}</span>
                <span>{{ $label }}</span>
            </a>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <span class="status-dot"></span>
        System online
    </div>
</aside>