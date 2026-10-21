@php
    $settingsNavigation = [
        ['route' => 'settings.index', 'icon' => 'bi-hospital', 'label' => __('Clinic profile')],
        ['route' => 'settings.payments', 'icon' => 'bi-cash-coin', 'label' => __('Payment settings')],
        ['route' => 'settings.account', 'icon' => 'bi-person-vcard', 'label' => __('User account')],
        ['route' => 'settings.notifications', 'icon' => 'bi-bell', 'label' => __('Notifications')],
        ['route' => 'settings.security', 'icon' => 'bi-shield-lock', 'label' => __('Security')],
    ];
@endphp

<aside class="settings-navigation">
    @foreach($settingsNavigation as $item)
        <a
            href="{{ route($item['route']) }}"
            class="settings-nav-item {{ request()->routeIs($item['route']) ? __('active') : '' }}"
            @if(request()->routeIs($item['route']))
                aria-current="page"
            @endif
        >
            <span>
                <i
                    class="bi {{ $item['icon'] }} app-icon"
                    aria-hidden="true"
                ></i>
            </span>

            {{ __($item['label']) }}
        </a>
    @endforeach
</aside>