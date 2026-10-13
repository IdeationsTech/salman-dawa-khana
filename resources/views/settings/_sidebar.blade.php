@php
    $navigation = [
        ['route' => 'dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
        ['route' => 'patients.index', 'icon' => 'bi-person-vcard', 'label' => 'Patients'],
        ['route' => 'visits.index', 'icon' => 'bi-calendar-check', 'label' => 'Visits'],
        ['route' => 'prescriptions.index', 'icon' => 'bi-prescription', 'label' => 'Prescriptions'],
        ['route' => 'payments.index', 'icon' => 'bi-cash-coin', 'label' => 'Payments'],
        ['route' => 'expenses.index', 'icon' => 'bi-receipt', 'label' => 'Expenses'],
        ['route' => 'reports.index', 'icon' => 'bi-bar-chart-line', 'label' => 'Reports'],
        ['route' => 'settings.index', 'icon' => 'bi-gear', 'label' => 'Settings'],
    ];
@endphp

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
        @foreach($navigation as $item)
            @if($item['route'] === 'reports.index')
                <div class="sidebar-divider"></div>
            @endif

            <a
                href="{{ route($item['route']) }}"
                class="sidebar-link {{ $item['route'] === 'settings.index' ? 'active' : '' }}"
                @if($item['route'] === 'settings.index')
                    aria-current="page"
                @endif
            >
                <span>
                    <i
                        class="bi {{ $item['icon'] }} app-icon"
                        aria-hidden="true"
                    ></i>
                </span>

                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <span class="status-dot"></span>
        System online
    </div>
</aside>