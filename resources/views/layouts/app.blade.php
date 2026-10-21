<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Salman Dawa Khana')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

@php
    $clinicRole = auth('web')->user()?->role?->name;

    $canManageUsers = $clinicRole === 'Owner';

    $canAccessExpenses = in_array(
        $clinicRole,
        ['Owner', 'Doctor', 'Accountant'],
        true
    );
@endphp

<body
    class="{{ $canManageUsers ? 'clinic-can-manage-users' : '' }} {{ $canAccessExpenses ? 'clinic-can-access-expenses' : '' }}"
    data-can-manage-users="{{ $canManageUsers ? '1' : '0' }}"
    data-can-access-expenses="{{ $canAccessExpenses ? '1' : '0' }}"
    data-users-url="{{ $canManageUsers ? route('users.index') : '' }}"
>
    @yield('content')

    {{-- Prescriptions — Shared Sidebar Submenu --}}
    @if(request()->routeIs('prescriptions.*'))
        <template
            data-prescription-submenu
            data-parent-url="{{ route('prescriptions.index') }}"
        >
            <div
                class="sidebar-subnav"
                aria-label="Prescription navigation"
            >
                <a
                    href="{{ route('prescriptions.index') }}"
                    class="sidebar-subnav-link {{ !request()->routeIs('prescriptions.templates.*') ? 'active' : '' }}"
                    @if(!request()->routeIs('prescriptions.templates.*'))
                        aria-current="page"
                    @endif
                >
                    Patient prescriptions
                </a>

                @clinicCan('prescriptions.templates.index')<a
                    href="{{ route('prescriptions.templates.index') }}"
                    class="sidebar-subnav-link {{ request()->routeIs('prescriptions.templates.*') ? 'active' : '' }}"
                    @if(request()->routeIs('prescriptions.templates.*'))
                        aria-current="page"
                    @endif
                >
                    Nuskha templates
                </a>@endclinicCan
            </div>
        </template>
    @endif

    @stack('scripts')
</body>
</html>