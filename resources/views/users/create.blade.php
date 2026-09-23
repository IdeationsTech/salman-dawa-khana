@extends('layouts.app')

@section('title', 'Add User — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">+</div>
            <span>Salman Dawa Khana</span>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="sidebar-link">
                <span>▦</span><span>Dashboard</span>
            </a>

            <a href="{{ route('patients.index') }}" class="sidebar-link">
                <span>♙</span><span>Patients</span>
            </a>

            <a href="{{ route('visits.index') }}" class="sidebar-link">
                <span>▣</span><span>Visits</span>
            </a>

            <a href="{{ route('prescriptions.index') }}" class="sidebar-link">
                <span>✎</span><span>Prescriptions</span>
            </a>

            <a href="{{ route('payments.index') }}" class="sidebar-link">
                <span>₨</span><span>Payments</span>
            </a>

            <a href="{{ route('expenses.index') }}" class="sidebar-link">
                <span>◈</span><span>Expenses</span>
            </a>

            <div class="sidebar-divider"></div>

            <a href="{{ route('reports.index') }}" class="sidebar-link">
                <span>◌</span><span>Reports</span>
            </a>

            <a href="{{ route('users.index') }}" class="sidebar-link active">
                <span>♟</span><span>Users &amp; Roles</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    <main class="dashboard-main">

        <div class="form-page-header">
            <a href="{{ route('users.index') }}" class="back-link">
                ← Back to users
            </a>

            <h1>Add new user</h1>
            <p>Create a staff account and assign a system role.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Please correct the following errors:</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            {{-- Personal Details --}}
            <section class="user-form-panel">
                <div class="user-form-heading">
                    <div class="form-section-number">01</div>

                    <div>
                        <h2>Personal details</h2>
                        <p>Enter the new user's account information.</p>
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
                            value="{{ old('name') }}"
                            placeholder="e.g. Sara Ahmed"
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
                            value="{{ old('email') }}"
                            placeholder="staff@example.com"
                            required
                        >

                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </section>

            {{-- Role and Access --}}
            <section class="user-form-panel">
                <div class="user-form-heading">
                    <div class="form-section-number">02</div>

                    <div>
                        <h2>Role and access</h2>
                        <p>Choose what this user can access.</p>
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

                        @foreach ($roles as $role)
                            <option
                                value="{{ $role->role_id }}"
                                @selected(old('role_id') == $role->role_id)
                            >
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('role_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="role-info-box">
                    <div class="role-info-icon">i</div>

                    <div>
                        <strong>Role permissions</strong>
                        <p>
                            The selected role will automatically determine the
                            user's access to patients, prescriptions, payments,
                            expenses and reports.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Login Credentials --}}
            <section class="user-form-panel">
                <div class="user-form-heading">
                    <div class="form-section-number">03</div>

                    <div>
                        <h2>Login credentials</h2>
                        <p>Set the password for the new account.</p>
                    </div>
                </div>

                <div class="user-form-grid">

                    <div class="form-field">
                        <label for="password" class="form-label">
                            Password <span>*</span>
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Create a password"
                            required
                        >

                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="password_confirmation" class="form-label">
                            Confirm password <span>*</span>
                        </label>

                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control"
                            placeholder="Repeat the password"
                            required
                        >
                    </div>

                </div>

                <div class="user-status-row">
                    <div>
                        <strong>Activate account immediately</strong>
                        <small>The user can sign in after creation.</small>
                    </div>

                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="is_active"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', true))
                    >
                </div>
            </section>

            <div class="user-form-actions">
                <a href="{{ route('users.index') }}" class="btn btn-light">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    Create user
                </button>
            </div>
        </form>

    </main>
</div>
@endsection