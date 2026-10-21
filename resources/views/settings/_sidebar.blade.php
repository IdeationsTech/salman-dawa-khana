@php
    $navigation = [
        ['route' => 'dashboard', 'icon' => 'bi-speedometer2', 'label' => __('Dashboard')],
        ['route' => 'patients.index', 'icon' => 'bi-person-vcard', 'label' => __('Patients')],
        ['route' => 'visits.index', 'icon' => 'bi-calendar-check', 'label' => __('Visits')],
        ['route' => 'prescriptions.index', 'icon' => 'bi-prescription', 'label' => __('Prescriptions')],
        ['route' => 'payments.index', 'icon' => 'bi-cash-coin', 'label' => __('Payments')],
        ['route' => 'expenses.index', 'icon' => 'bi-receipt', 'label' => __('Expenses')],
        ['route' => 'reports.index', 'icon' => 'bi-bar-chart-line', 'label' => __('Reports')],
        ['route' => 'settings.index', 'icon' => 'bi-gear', 'label' => __('Settings')],
    ];
@endphp

@include('layouts._sidebar')