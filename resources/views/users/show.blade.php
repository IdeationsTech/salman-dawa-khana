@extends('layouts.app')

@section('title', 'User Details — ' . $user->name)

@section('content')
@php
    $clinic = auth()->user()->clinic;
    $roleName = $user->role?->name ?? 'Staff';

    $navigation = [
        ['route' => 'dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
        ['route' => 'patients.index', 'icon' => 'bi-person-vcard', 'label' => 'Patients'],
        ['route' => 'visits.index', 'icon' => 'bi-calendar-check', 'label' => 'Visits'],
        ['route' => 'prescriptions.index', 'icon' => 'bi-prescription', 'label' => 'Prescriptions'],
        ['route' => 'payments.index', 'icon' => 'bi-cash-coin', 'label' => 'Payments'],
        ['route' => 'expenses.index', 'icon' => 'bi-receipt', 'label' => 'Expenses'],
        ['route' => 'reports.index', 'icon' => 'bi-bar-chart-line', 'label' => 'Reports'],
        ['route' => 'users.index', 'icon' => 'bi-people', 'label' => 'Users'],
        ['route' => 'settings.index', 'icon' => 'bi-gear', 'label' => 'Settings'],
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
                    Back to users
                </a>

                <h1>User details</h1>
                <p class="page-subtitle">
                    View this clinic user’s account and assigned role.
                </p>
            </div>

            <a
                href="{{ route('users.edit', $user) }}"
                class="btn btn-primary"
            >
                <i class="bi bi-pencil-square app-icon" aria-hidden="true"></i>
                Edit user
            </a>
        </header>

        @if(session('success'))
            <div class="alert alert-success" role="status">
                {{ session('success') }}
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
                <p>{{ $roleName === 'Doctor' ? 'Doctor / Hakeem' : $roleName }}</p>

                <span>
                    {{ $user->is_active ? 'Active account' : 'Inactive account' }}
                </span>
            </div>
        </section>

        {{-- User Details — Account Information --}}
        <section class="user-form-panel">
            <div class="user-form-heading">
                <div class="form-section-number">01</div>

                <div>
                    <h2>Account information</h2>
                    <p>Saved details for this user.</p>
                </div>
            </div>

            <div class="user-form-grid">
                <div class="form-field">
                    <span class="form-label">Full name</span>
                    <p class="mb-0">{{ $user->name }}</p>
                </div>

                <div class="form-field">
                    <span class="form-label">Email address</span>
                    <p class="mb-0 text-break">{{ $user->email }}</p>
                </div>

                <div class="form-field">
                    <span class="form-label">Phone number</span>
                    <p class="mb-0">{{ $user->phone ?: 'Not provided' }}</p>
                </div>

                <div class="form-field">
                    <span class="form-label">Clinic</span>
                    <p class="mb-0">{{ $clinic->name }}</p>
                </div>

                <div class="form-field">
                    <span class="form-label">Role</span>
                    <p class="mb-0">
                        {{ $roleName === 'Doctor' ? 'Doctor / Hakeem' : $roleName }}
                    </p>
                </div>

                <div class="form-field">
                    <span class="form-label">Joined</span>
                    <p class="mb-0">
                        {{ $user->created_at?->format('d M Y') ?? '—' }}
                    </p>
                </div>
            </div>
        </section>
    </main>
</div>
@endsection