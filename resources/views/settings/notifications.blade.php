@extends('layouts.app')

@section('title', 'Notification Settings — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Notification Settings Sidebar --}}
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

            <a href="/settings/notifications" class="sidebar-link active">
                <span>⚙</span><span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    <main class="dashboard-main">

        <header class="page-header">
            <div>
                <p class="dashboard-date">Settings</p>
                <h1>Notifications</h1>
                <p class="page-subtitle">
                    Choose which clinic alerts you want to receive.
                </p>
            </div>
        </header>

        <section class="settings-layout">

            <aside class="settings-navigation">
                <a href="/settings" class="settings-nav-item">
                    <span>▣</span> Clinic profile
                </a>

                <a href="/settings/payments" class="settings-nav-item">
                    <span>₨</span> Payment settings
                </a>

                <a href="/settings/account" class="settings-nav-item">
                    <span>♙</span> User account
                </a>

                <a href="/settings/notifications" class="settings-nav-item active">
                    <span>◌</span> Notifications
                </a>

                <a href="/settings/security" class="settings-nav-item">
                    <span>▤</span> Security
                </a>
            </aside>

            <div class="settings-content">

                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Notification channels</h2>
                            <p>Choose where alerts should appear.</p>
                        </div>
                    </div>

                    <div class="settings-form-grid">
                        <div class="form-field">
                            <label for="notification_email" class="form-label">
                                Notification email
                            </label>

                            <input
                                type="email"
                                id="notification_email"
                                class="form-control"
                                value="ahmed@example.com"
                            >
                        </div>

                        <div class="form-field">
                            <label for="notification_phone" class="form-label">
                                Notification phone
                            </label>

                            <input
                                type="tel"
                                id="notification_phone"
                                class="form-control"
                                value="+971 50 123 4567"
                            >
                        </div>
                    </div>

                    <div class="settings-toggle-list">
                        <div class="settings-toggle-row">
                            <div>
                                <strong>Email notifications</strong>
                                <small>Receive important alerts by email.</small>
                            </div>

                            <input class="form-check-input" type="checkbox" checked>
                        </div>

                        <div class="settings-toggle-row">
                            <div>
                                <strong>In-app notifications</strong>
                                <small>Show alerts inside the dashboard.</small>
                            </div>

                            <input class="form-check-input" type="checkbox" checked>
                        </div>
                    </div>
                </section>

                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Clinic alerts</h2>
                            <p>Manage the alerts your team receives.</p>
                        </div>
                    </div>

                    <div class="settings-toggle-list">
                        <div class="settings-toggle-row">
                            <div>
                                <strong>Payment received</strong>
                                <small>Notify when a patient payment is recorded.</small>
                            </div>

                            <input class="form-check-input" type="checkbox" checked>
                        </div>

                        <div class="settings-toggle-row">
                            <div>
                                <strong>Outstanding payment reminder</strong>
                                <small>Alert when a patient has an unpaid balance.</small>
                            </div>

                            <input class="form-check-input" type="checkbox" checked>
                        </div>

                        <div class="settings-toggle-row">
                            <div>
                                <strong>New patient registration</strong>
                                <small>Notify when a new patient is added.</small>
                            </div>

                            <input class="form-check-input" type="checkbox" checked>
                        </div>

                        <div class="settings-toggle-row">
                            <div>
                                <strong>Daily clinic summary</strong>
                                <small>Receive a daily income and visit summary.</small>
                            </div>

                            <input class="form-check-input" type="checkbox">
                        </div>
                    </div>

                    <div class="settings-panel-actions">
                        <button class="btn btn-primary">
                            Save notification settings
                        </button>
                    </div>
                </section>

            </div>
        </section>

    </main>
</div>
@endsection