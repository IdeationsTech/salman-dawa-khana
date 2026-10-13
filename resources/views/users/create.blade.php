@extends('layouts.app')

@section('title', isset($user) ? 'Edit User' : 'Add User')

@section('content')
@php
    $editing = isset($user);
    $clinic = auth()->user()->clinic;

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

    {{-- User Form — Application Sidebar --}}
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
                    class="sidebar-link {{ $item['route'] === 'users.index' ? 'active' : '' }}"
                    @if($item['route'] === 'users.index')
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

    {{-- User Form — Main Content --}}
    <main class="dashboard-main">
        <div class="form-page-header">
            <a href="{{ route('users.index') }}" class="back-link">
                <i class="bi bi-arrow-left app-icon" aria-hidden="true"></i>
                Back to users
            </a>

            <h1>{{ $editing ? 'Edit user' : 'Add new user' }}</h1>

            <p>
                {{ $editing
                    ? 'Update account details, role and access.'
                    : 'Create a clinic account and assign its role.' }}
            </p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Please correct the following:</strong>

                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ $editing
                ? route('users.update', $user)
                : route('users.store') }}"
        >
            @csrf

            @if($editing)
                @method('PUT')
            @endif

            {{-- User Form — Personal Details --}}
            <section class="user-form-panel">
                <div class="user-form-heading">
                    <div class="form-section-number">01</div>

                    <div>
                        <h2>Personal details</h2>
                        <p>Enter the user’s name, email and phone number.</p>
                    </div>
                </div>

                <div class="user-form-grid">
                    <div class="form-field">
                        <label for="name" class="form-label">
                            Full name <span>*</span>
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $editing ? $user->name : '') }}"
                            maxlength="120"
                            autocomplete="name"
                            required
                        >

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="email" class="form-label">
                            Email address <span>*</span>
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $editing ? $user->email : '') }}"
                            maxlength="160"
                            autocomplete="off"
                            required
                        >

                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- User Form — Required International Phone --}}
                    <div class="form-field">
                        <label for="user_phone" class="form-label">
                            Personal phone number <span>*</span>
                        </label>

                        <x-phone-input
                            name="phone"
                            id="user_phone"
                            :value="$editing ? $user->phone : ''"
                            :required="true"
                        />
                    </div>
                </div>
            </section>

            {{-- User Form — Role Assignment --}}
            <section class="user-form-panel">
                <div class="user-form-heading">
                    <div class="form-section-number">02</div>

                    <div>
                        <h2>Role and access</h2>
                        <p>Select the responsibilities of this account.</p>
                    </div>
                </div>

                <div class="form-field">
                    <label for="role_id" class="form-label">
                        System role <span>*</span>
                    </label>

                    <select
                        id="role_id"
                        name="role_id"
                        class="form-select @error('role_id') is-invalid @enderror"
                        required
                    >
                        <option value="">Select a role</option>

                        @foreach($roles as $role)
                            <option
                                value="{{ $role->role_id }}"
                                @selected(
                                    (string) old(
                                        'role_id',
                                        $editing ? $user->role_id : ''
                                    ) === (string) $role->role_id
                                )
                            >
                                {{ $role->name === 'Doctor'
                                    ? 'Doctor / Hakeem'
                                    : $role->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('role_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="role-info-box">
                    <div class="role-info-icon">
                        <i class="bi bi-info-circle app-icon" aria-hidden="true"></i>
                    </div>

                    <div>
                        <strong>Role access</strong>

                        <p>
                            Owner manages users and clinic administration.
                            Doctor/Hakeem can access expenses.
                            Receptionist cannot access users or expenses.
                        </p>
                    </div>
                </div>
            </section>

            {{-- User Form — Password and Account Status --}}
            <section class="user-form-panel">
                <div class="user-form-heading">
                    <div class="form-section-number">03</div>

                    <div>
                        <h2>Login credentials</h2>

                        <p>
                            {{ $editing
                                ? 'Leave both password fields empty to keep the current password.'
                                : 'Set a password with at least 8 characters.' }}
                        </p>
                    </div>
                </div>

                <div class="user-form-grid">
                    <div class="form-field">
                        <label for="password" class="form-label">
                            {{ $editing ? 'New password' : 'Password' }}
                            @if(!$editing)
                                <span>*</span>
                            @endif
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            minlength="8"
                            autocomplete="new-password"
                            @required(!$editing)
                        >

                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="password_confirmation" class="form-label">
                            Confirm password
                            @if(!$editing)
                                <span>*</span>
                            @endif
                        </label>

                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control"
                            minlength="8"
                            autocomplete="new-password"
                            @required(!$editing)
                        >
                    </div>
                </div>

                <div class="user-status-row">
                    <div>
                        <label for="is_active">
                            <strong>Active account</strong>
                        </label>

                        <small>Inactive accounts cannot sign in.</small>
                    </div>

                    <input type="hidden" name="is_active" value="0">

                    <input
                        type="checkbox"
                        id="is_active"
                        name="is_active"
                        class="form-check-input"
                        value="1"
                        @checked(
                            (string) old(
                                'is_active',
                                $editing ? (int) $user->is_active : 1
                            ) === '1'
                        )
                    >
                </div>
            </section>

            <div class="user-form-actions">
                <a href="{{ route('users.index') }}" class="btn btn-light">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    <i
                        class="bi {{ $editing ? 'bi-check2-circle' : 'bi-person-plus' }} app-icon"
                        aria-hidden="true"
                    ></i>

                    {{ $editing ? 'Save changes' : 'Create user' }}
                </button>
            </div>
        </form>
    </main>
</div>
@endsection