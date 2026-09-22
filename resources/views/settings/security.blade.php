@extends('layouts.app')

@section('title', 'Security Settings — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Security Settings Sidebar --}}
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

            <a href="/settings/security" class="sidebar-link active">
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
                <h1>Security</h1>
                <p class="page-subtitle">
                    Manage your password and account security.
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

                <a href="/settings/notifications" class="settings-nav-item">
                    <span>◌</span> Notifications
                </a>

                <a href="/settings/security" class="settings-nav-item active">
                    <span>▤</span> Security
                </a>
            </aside>

            <div class="settings-content">

                {{-- Change Password --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Change password</h2>
                            <p>Use a strong password to protect your account.</p>
                        </div>

                        <div class="security-icon">▤</div>
                    </div>

                    <div class="settings-form-grid">
                        <div class="form-field settings-field-wide">
                            <label for="current_password" class="form-label">
                                Current password
                            </label>

                            <input
                                type="password"
                                id="current_password"
                                class="form-control"
                                placeholder="Enter current password"
                            >
                        </div>

                        <div class="form-field">
                            <label for="new_password" class="form-label">
                                New password
                            </label>

                            <input
                                type="password"
                                id="new_password"
                                class="form-control"
                                placeholder="Enter new password"
                            >
                        </div>

                        <div class="form-field">
                            <label for="confirm_password" class="form-label">
                                Confirm new password
                            </label>

                            <input
                                type="password"
                                id="confirm_password"
                                class="form-control"
                                placeholder="Repeat new password"
                            >
                        </div>
                    </div>

                    <div class="password-requirements">
                        <strong>Password requirements</strong>

                        <ul>
                            <li>At least 8 characters</li>
                            <li>One uppercase letter</li>
                            <li>One number or special character</li>
                        </ul>
                    </div>

                    <div class="settings-panel-actions">
                        <button class="btn btn-primary">
                            Update password
                        </button>
                    </div>
                </section>

                {{-- Session Security --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Session security</h2>
                            <p>Manage how your account sessions are handled.</p>
                        </div>
                    </div>

                    <div class="settings-toggle-list">
                        <div class="settings-toggle-row">
                            <div>
                                <strong>Remember this device</strong>
                                <small>Keep this device signed in for 30 days.</small>
                            </div>

                            <input class="form-check-input" type="checkbox" checked>
                        </div>

                        <div class="settings-toggle-row">
                            <div>
                                <strong>Automatic logout</strong>
                                <small>Log out after extended inactivity.</small>
                            </div>

                            <input class="form-check-input" type="checkbox" checked>
                        </div>
                    </div>
                </section>

                {{-- Active Sessions --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Active sessions</h2>
                            <p>Devices currently signed in to your account.</p>
                        </div>
                    </div>

                    <div class="active-session-row">
                        <div class="session-device-icon">⌂</div>

                        <div>
                            <strong>Windows PC — Current device</strong>
                            <small>Chrome · Dubai, UAE · Active now</small>
                        </div>

                        <span class="session-current-badge">Current</span>
                    </div>

                    <div class="active-session-row">
                        <div class="session-device-icon">▣</div>

                        <div>
                            <strong>Mobile device</strong>
                            <small>Last active yesterday</small>
                        </div>

                        <button class="btn btn-sm btn-outline-danger">
                            Sign out
                        </button>
                    </div>
                </section>

            </div>
        </section>

    </main>
</div>
@endsection