@extends('layouts.app')

@section('title', 'User Details — ' . $user->name)

@section('content')
@php
    $clinic = auth()->user()->clinic;
    $roleName = $user->role?->name ?? 'Staff';

    $navigation = [
        ['route' => 'dashboard', 'icon' => 'bi-speedometer2', 'label' => __('Dashboard')],
        ['route' => 'patients.index', 'icon' => 'bi-person-vcard', 'label' => __('Patients')],
        ['route' => 'visits.index', 'icon' => 'bi-calendar-check', 'label' => __('Visits')],
        ['route' => 'prescriptions.index', 'icon' => 'bi-prescription', 'label' => __('Prescriptions')],
        ['route' => 'payments.index', 'icon' => 'bi-cash-coin', 'label' => __('Payments')],
        ['route' => 'expenses.index', 'icon' => 'bi-receipt', 'label' => __('Expenses')],
        ['route' => 'reports.index', 'icon' => 'bi-bar-chart-line', 'label' => __('Reports')],
        ['route' => 'users.index', 'icon' => 'bi-people', 'label' => __('Users')],
        ['route' => 'settings.index', 'icon' => 'bi-gear', 'label' => __('Settings')],
    ];
@endphp

<div class="app-shell">

    {{-- User Details — Sidebar --}}
    @include('layouts._sidebar')

    {{-- User Details — Main Content --}}
    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <a href="{{ route('users.index') }}" class="back-link">
                    <i class="bi bi-arrow-left app-icon" aria-hidden="true"></i>
                    {{ __('Back to users') }}
                </a>

                <h1>{{ __('User details') }}</h1>
                <p class="page-subtitle">
                    {{ __('View this clinic user’s account and assigned role.') }}
                </p>
            </div>

            @clinicCan('users.edit')<a
                href="{{ route('users.edit', $user) }}"
                class="btn btn-primary"
            >
                <i class="bi bi-pencil-square app-icon" aria-hidden="true"></i>
                {{ __('Edit user') }}
            </a>@endclinicCan
        </header>

        @if(session('success'))
            <div class="alert alert-success" role="status">
                {{ __(session('success')) }}
            </div>
        @endif

        {{-- User Details — Profile --}}
        <section class="settings-panel account-profile-card">
            <div class="account-profile-avatar">
                @if($user->profile_photo_path)
                    <img
                        src="{{ asset('storage/' . $user->profile_photo_path) }}"
                        alt="{{ $user->name }} profile photo"
                        class="account-profile-photo"
                    >
                @else
                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                @endif
            </div>

            <div class="account-profile-info">
                <h2>{{ $user->name }}</h2>
                <p>{{ $roleName === 'Doctor' ? __('Doctor / Hakeem') : $roleName }}</p>

                <span>
                    {{ $user->is_active ? __('Active account') : 'Inactive account' }}
                </span>
            </div>
        </section>

        {{-- User Details — Account Information --}}
        <section class="user-form-panel">
            <div class="user-form-heading">
                <div class="form-section-number">01</div>

                <div>
                    <h2>{{ __('Account information') }}</h2>
                    <p>{{ __('Saved details for this user.') }}</p>
                </div>
            </div>

            <div class="user-form-grid">
                <div class="form-field">
                    <span class="form-label">{{ __('Full name') }}</span>
                    <p class="mb-0">{{ $user->name }}</p>
                </div>

                <div class="form-field">
                    <span class="form-label">{{ __('Email address') }}</span>
                    <p class="mb-0 text-break">{{ $user->email }}</p>
                </div>

                <div class="form-field">
                    <span class="form-label">{{ __('Phone number') }}</span>
                    <p class="mb-0"><bdi class="phone-number" dir="ltr">&#x2066;{{ $user->phone ?: __('Not provided') }}&#x2069;</bdi></p>
                </div>

                <div class="form-field">
                    <span class="form-label">{{ __('Clinic') }}</span>
                    <p class="mb-0">{{ $clinic->name }}</p>
                </div>

                <div class="form-field">
                    <span class="form-label">{{ __('Role') }}</span>
                    <p class="mb-0">
                        {{ $roleName === 'Doctor' ? __('Doctor / Hakeem') : $roleName }}
                    </p>
                </div>

                <div class="form-field">
                    <span class="form-label">{{ __('Joined') }}</span>
                    <p class="mb-0">
                        {{ $user->created_at?->format('d M Y') ?? '—' }}
                    </p>
                </div>
            </div>
        </section>
    </main>
</div>
@endsection