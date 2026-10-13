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

@include('layouts._sidebar')