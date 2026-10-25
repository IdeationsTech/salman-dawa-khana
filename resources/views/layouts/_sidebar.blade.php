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


<svg class="sidebar-icon-sprite" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" style="display:none">
    <symbol id="sidebar-icon-speedometer2" viewBox="0 0 16 16">
        <path d="M2 13a6 6 0 1 1 12 0"/><path d="M8 8l3-3"/><path d="M8 13h.01"/>
    </symbol>
    <symbol id="sidebar-icon-person-vcard" viewBox="0 0 16 16">
        <rect x="1.5" y="2" width="13" height="12" rx="1.5"/><circle cx="5" cy="6" r="1.5"/><path d="M3 11c.5-1.2 1.2-1.8 2-1.8s1.5.6 2 1.8M9 5h3M9 8h3"/>
    </symbol>
    <symbol id="sidebar-icon-calendar-check" viewBox="0 0 16 16">
        <rect x="2" y="3" width="12" height="11" rx="1.5"/><path d="M5 1.5v3m6-3v3M2 6h12M5 9l1.5 1.5L11 8"/>
    </symbol>
    <symbol id="sidebar-icon-prescription" viewBox="0 0 16 16">
        <path d="M4 1.5h6l3 3v10H4zM10 1.5v3h3M8 7v4M6 9h4"/>
    </symbol>
    <symbol id="sidebar-icon-bag-check" viewBox="0 0 16 16">
        <path d="M2 5h12l-1 9H3zM5 5a3 3 0 0 1 6 0M5.5 9l1.5 1.5L10 8"/>
    </symbol>
    <symbol id="sidebar-icon-cash-coin" viewBox="0 0 16 16">
        <circle cx="6.5" cy="8" r="5.5"/><path d="M6.5 5.5v5m-1.5-3.8c0-.7.6-1.2 1.5-1.2s1.5.5 1.5 1.2-.6 1.1-1.5 1.3-1.5.6-1.5 1.3.6 1.2 1.5 1.2 1.5-.5 1.5-1.2"/><circle cx="12" cy="4" r="2.5"/>
    </symbol>
    <symbol id="sidebar-icon-receipt" viewBox="0 0 16 16">
        <path d="M3 1.5l1.5 1L6 1.5l2 1 2-1 1.5 1 1.5-1v13l-1.5-1-1.5 1-2-1-2 1-1.5-1L3 14.5zM5.5 6h5M5.5 9h5M5.5 12h3"/>
    </symbol>
    <symbol id="sidebar-icon-bar-chart-line" viewBox="0 0 16 16">
        <path d="M2 14V9h2v5M7 14V5h2v9M12 14V2h2v12M1 14.5h14M2 6l3-2 3 1 5-3"/>
    </symbol>
    <symbol id="sidebar-icon-bell" viewBox="0 0 16 16">
        <path d="M3 11.5h10l-1.2-1.7V6a3.8 3.8 0 0 0-7.6 0v3.8zM6.5 13a1.6 1.6 0 0 0 3 0"/>
    </symbol>
    <symbol id="sidebar-icon-people" viewBox="0 0 16 16">
        <circle cx="6" cy="5" r="2.2"/><path d="M1.5 13a4.5 4.5 0 0 1 9 0M11 3a2 2 0 0 1 0 4M12 9a3.5 3.5 0 0 1 2.5 3.5"/>
    </symbol>
    <symbol id="sidebar-icon-clock-history" viewBox="0 0 16 16">
        <circle cx="8.5" cy="8" r="5.5"/><path d="M8.5 5v3l2 1M2 3v3h3"/>
    </symbol>
    <symbol id="sidebar-icon-gear" viewBox="0 0 16 16">
        <circle cx="8" cy="8" r="2.2"/><path d="M8 1.5v2M8 12.5v2M1.5 8h2M12.5 8h2M3.4 3.4l1.4 1.4m6.4 6.4 1.4 1.4m0-9.2-1.4 1.4m-6.4 6.4-1.4 1.4"/>
    </symbol>
    <symbol id="sidebar-icon-hospital" viewBox="0 0 16 16">
        <path d="M2 14.5V3.5h12v11M1 14.5h14M6 14.5v-4h4v4M5 6h1M10 6h1M5 8h1M10 8h1M7 4.5v3m-1.5-1.5h3"/>
    </symbol>
    <symbol id="sidebar-icon-chevron" viewBox="0 0 16 16">
        <path d="m4 3 5 5-5 5M8 3l5 5-5 5"/>
    </symbol>
    <symbol id="sidebar-icon-signout" viewBox="0 0 16 16">
        <path d="M7 2H3v12h4M9 5l3 3-3 3M5 8h7"/>
    </symbol>
</svg>

<aside id="clinic-sidebar" class="app-sidebar no-print" tabindex="-1" aria-labelledby="clinic-sidebar-title">
    <button
        type="button"
        class="clinic-sidebar-expand"
        data-sidebar-rail-toggle
        aria-controls="clinic-sidebar"
        aria-expanded="false"
        aria-label="{{ __('Expand navigation') }}"
        title="{{ __('Expand navigation') }}"
    >
        <svg class="sidebar-vector-icon sidebar-expand-icon" viewBox="0 0 16 16" aria-hidden="true"><use href="#sidebar-icon-chevron"></use></svg>
    </button>

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
                    <svg class="app-icon sidebar-vector-icon" viewBox="0 0 16 16" aria-hidden="true"><use href="#sidebar-icon-hospital"></use></svg>
                @endif
            </div>
            <span id="clinic-sidebar-title">{{ $sidebarClinic->name }}</span>
        </div>
        <x-language-switcher />
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
                    aria-label="{{ __($label) }}"
                    title="{{ __($label) }}"
                    @if (request()->routeIs($pattern)) aria-current="page" @endif
                >
                    <span><svg class="app-icon sidebar-vector-icon" viewBox="0 0 16 16" aria-hidden="true"><use href="#sidebar-icon-{{ $icon }}"></use></svg></span>
                    <span>{{ __($label) }}</span>
                </a>
            @endforeach
        </nav>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-signout" aria-label="{{ __('Sign out') }}" title="{{ __('Sign out') }}">
                    <svg class="app-icon sidebar-vector-icon" viewBox="0 0 16 16" aria-hidden="true"><use href="#sidebar-icon-signout"></use></svg>
                    <span>{{ __('Sign out') }}</span>
                </button>
            </form>
        </div>
    </div>
</aside>

@push('scripts')
<script>
(() => {
    const sidebar = document.getElementById('clinic-sidebar');
    const toggle = sidebar?.querySelector('[data-sidebar-rail-toggle]');
    const compact = window.matchMedia('(max-width: 991.98px)');

    if (!sidebar || !toggle) return;

    const setExpanded = (expanded) => {
        sidebar.classList.toggle('is-expanded', expanded);
        toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');

        if (!expanded && sidebar.contains(document.activeElement)) {
            document.activeElement.blur();
        }
    };

    toggle.addEventListener('click', () => {
        setExpanded(!sidebar.classList.contains('is-expanded'));
    });

    sidebar.addEventListener('click', (event) => {
        if (compact.matches && event.target.closest('a[href]')) {
            setExpanded(false);
        }
    });

    sidebar.addEventListener('submit', () => {
        if (compact.matches) setExpanded(false);
    });

    document.addEventListener('pointerdown', (event) => {
        if (compact.matches && !sidebar.contains(event.target)) {
            setExpanded(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (compact.matches && event.key === 'Escape' && sidebar.classList.contains('is-expanded')) {
            setExpanded(false);
            sidebar.querySelector(':focus')?.blur();
        }
    });

    compact.addEventListener('change', (event) => {
        if (!event.matches) setExpanded(false);
    });
})();
</script>
@endpush
