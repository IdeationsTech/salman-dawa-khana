@extends('layouts.app')

@section('title', 'Add User — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- User Form Sidebar --}}
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">+</div>
            <span>Salman Dawa Khana</span>
        </div>

        <nav class="sidebar-nav">
            <a href="/dashboard" class="sidebar-link">
                <span>▦</span><span>Dashboard</span>
            </a>

            <a href="/patients" class="sidebar-link">
                <span>♙</span><span>Patients</span>
            </a>

            <a href="/visits" class="sidebar-link">
                <span>▣</span><span>Visits</span>
            </a>

            <a href="/prescriptions" class="sidebar-link">
                <span>✎</span><span>Prescriptions</span>
            </a>

            <a href="/payments" class="sidebar-link">
                <span>₨</span><span>Payments</span>
            </a>

            <a href="/expenses" class="sidebar-link">
                <span>◈</span><span>Expenses</span>
            </a>

            <div class="sidebar-divider"></div>

            <a href="/reports" class="sidebar-link">
                <span>◌</span><span>Reports</span>
            </a>

            <a href="/users" class="sidebar-link active">
                <span>♟</span><span>Users &amp; Roles</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    {{-- User Form Main Content --}}
    <main class="dashboard-main">

        <div class="form-page-header">
            <a href="/users" class="back-link">
                ← Back to users
            </a>

            <h1>Add new user</h1>
            <p>Create a staff account and assign a system role.</p>
        </div>

        <form action="#" method="POST">
            @csrf

            {{-- Personal Details --}}
            <section class="user-form-panel">
                <div class="user-form-heading">
                    <div class="form-section-number">01</div>

                    <div>
                        <h2>Personal details</h2>
                        <p>Enter the new user's contact information.</p>
                    </div>
                </div>

                <div class="user-form-grid">
                    <div class="form-field">
                        <label for="first_name" class="form-label">
                            First name <span>*</span>
                        </label>

                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            class="form-control"
                            placeholder="e.g. Sara"
                        >
                    </div>

                    <div class="form-field">
                        <label for="last_name" class="form-label">
                            Last name
                        </label>

                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            class="form-control"
                            placeholder="e.g. Ahmed"
                        >
                    </div>

                    <div class="form-field">
                        <label for="email" class="form-label">
                            Email address <span>*</span>
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="staff@example.com"
                        >
                    </div>

                    <div class="form-field">
                        <label for="phone" class="form-label">
                            Phone number
                        </label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            class="form-control"
                            placeholder="+971 50 000 0000"
                        >
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
                    <label for="role" class="form-label">
                        System role <span>*</span>
                    </label>

                    <select id="role" name="role" class="form-select">
                        <option selected>Select a role</option>
                        <option>Doctor</option>
                        <option>Receptionist</option>
                        <option>Accountant</option>
                    </select>
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
                            class="form-control"
                            placeholder="Create a password"
                        >
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
                        checked
                    >
                </div>
            </section>

            <div class="user-form-actions">
                <a href="/users" class="btn btn-light">
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