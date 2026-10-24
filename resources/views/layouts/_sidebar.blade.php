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
        <i class="bi bi-chevron-double-right" aria-hidden="true"></i>
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
                    <i class="bi bi-hospital app-icon" aria-hidden="true"></i>
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
                    <span><i class="bi bi-{{ $icon }} app-icon" aria-hidden="true"></i></span>
                    <span>{{ __($label) }}</span>
                </a>
            @endforeach
        </nav>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-signout" aria-label="{{ __('Sign out') }}" title="{{ __('Sign out') }}">
                    <i class="bi bi-box-arrow-right app-icon" aria-hidden="true"></i>
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
