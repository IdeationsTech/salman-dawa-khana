@extends('layouts.app')

@section('title', 'User Account — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Account Settings Sidebar --}}
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">+</div>
            <span>Salman Dawa Khana</span>
        </div>

        <nav class="sidebar-nav">
            <a href="/dashboard" class="sidebar-link">
                <span>▦</span>
                <span>Dashboard</span>
            </a>

            <a href="/patients" class="sidebar-link">
                <span>♙</span>
                <span>Patients</span>
            </a>

            <a href="/visits" class="sidebar-link">
                <span>▣</span>
                <span>Visits</span>
            </a>

            <a href="/prescriptions" class="sidebar-link">
                <span>✎</span>
                <span>Prescriptions</span>
            </a>

            <a href="/payments" class="sidebar-link">
                <span>₨</span>
                <span>Payments</span>
            </a>

            <a href="/expenses" class="sidebar-link">
                <span>◈</span>
                <span>Expenses</span>
            </a>

            <div class="sidebar-divider"></div>

            <a href="/reports" class="sidebar-link">
                <span>◌</span>
                <span>Reports</span>
            </a>

            <a href="/settings/account" class="sidebar-link active">
                <span>⚙</span>
                <span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    {{-- Account Settings Content --}}
    <main class="dashboard-main">

        <header class="page-header">
            <div>
                <p class="dashboard-date">Settings</p>
                <h1>User account</h1>
                <p class="page-subtitle">
                    Manage your personal account information.
                </p>
            </div>
        </header>

        <section class="settings-layout">

            {{-- Settings Navigation --}}
            <aside class="settings-navigation">
                <a href="/settings" class="settings-nav-item">
                    <span>▣</span>
                    Clinic profile
                </a>

                <a href="/settings/payments" class="settings-nav-item">
                    <span>₨</span>
                    Payment settings
                </a>

                <a href="/settings/account" class="settings-nav-item active">
                    <span>♙</span>
                    User account
                </a>

                <a href="/settings/notifications" class="settings-nav-item">
                    <span>◌</span>
                    Notifications
                </a>

                <a href="/settings/security" class="settings-nav-item">
                    <span>▤</span>
                    Security
                </a>
            </aside>

            <div class="settings-content">

                {{-- Profile Header --}}
                <section class="settings-panel account-profile-card">
                    <div class="account-profile-avatar">AK</div>

                    <div class="account-profile-info">
                        <h2>Dr. Ahmed Khan</h2>
                        <p>Clinic Owner</p>
                        <span>Member since January 2026</span>
                    </div>

                    <button class="btn btn-outline-secondary ms-auto">
                        Change photo
                    </button>
                </section>

                {{-- Personal Information --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Personal information</h2>
                            <p>Update your name and contact details.</p>
                        </div>
                    </div>

                    <div class="settings-form-grid">
                        <div class="form-field">
                            <label for="first_name" class="form-label">
                                First name
                            </label>

                            <input
                                type="text"
                                id="first_name"
                                class="form-control"
                                value="Ahmed"
                            >
                        </div>

                        <div class="form-field">
                            <label for="last_name" class="form-label">
                                Last name
                            </label>

                            <input
                                type="text"
                                id="last_name"
                                class="form-control"
                                value="Khan"
                            >
                        </div>

                        <div class="form-field">
                            <label for="email" class="form-label">
                                Email address
                            </label>

                            <input
                                type="email"
                                id="email"
                                class="form-control"
                                value="ahmed@example.com"
                            >
                        </div>

                        <div class="form-field">
                            <label for="phone" class="form-label">
                                Phone number
                            </label>

                            <input
                                type="tel"
                                id="phone"
                                class="form-control"
                                value="+971 50 123 4567"
                            >
                        </div>
                    </div>

                    <div class="settings-panel-actions">
                        <button class="btn btn-primary">
                            Save changes
                        </button>
                    </div>
                </section>

                {{-- Role Information --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Role and access</h2>
                            <p>Your current permissions in the clinic system.</p>
                        </div>
                    </div>

                    <div class="account-role-box">
                        <div class="account-role-icon">★</div>

                        <div>
                            <strong>Clinic Owner</strong>
                            <small>
                                Full access to patients, prescriptions, payments,
                                expenses, reports and settings.
                            </small>
                        </div>

                        <span class="status-badge active-badge">Active</span>
                    </div>
                </section>

            </div>
        </section>

    </main>
</div>
@endsection