@extends('layouts.app')

@section('title', 'Settings — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Settings Sidebar --}}
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

            <a href="/settings" class="sidebar-link active">
                <span>⚙</span>
                <span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    {{-- Settings Main Content --}}
    <main class="dashboard-main">

        <header class="page-header">
            <div>
                <p class="dashboard-date">Application configuration</p>
                <h1>Settings</h1>
                <p class="page-subtitle">
                    Manage your clinic profile and system preferences.
                </p>
            </div>
        </header>

        <section class="settings-layout">

            {{-- Settings Navigation --}}
            <aside class="settings-navigation">
                <button class="settings-nav-item active">
                    <span>▣</span>
                    Clinic profile
                </button>

                <button class="settings-nav-item">
                    <span>₨</span>
                    Payment settings
                </button>

                <button class="settings-nav-item">
                    <span>♙</span>
                    User account
                </button>

                <button class="settings-nav-item">
                    <span>◌</span>
                    Notifications
                </button>

                <button class="settings-nav-item">
                    <span>▤</span>
                    Security
                </button>
            </aside>

            {{-- Settings Content --}}
            <div class="settings-content">

                {{-- Clinic Profile --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Clinic profile</h2>
                            <p>Update the information shown across your clinic system.</p>
                        </div>

                        <div class="clinic-settings-logo">+</div>
                    </div>

                    <div class="settings-form-grid">
                        <div class="form-field settings-field-wide">
                            <label for="clinic_name" class="form-label">
                                Clinic name
                            </label>

                            <input
                                type="text"
                                id="clinic_name"
                                class="form-control"
                                value="Salman Dawa Khana"
                            >
                        </div>

                        <div class="form-field">
                            <label for="clinic_phone" class="form-label">
                                Phone number
                            </label>

                            <input
                                type="tel"
                                id="clinic_phone"
                                class="form-control"
                                value="+971 50 123 4567"
                            >
                        </div>

                        <div class="form-field">
                            <label for="clinic_email" class="form-label">
                                Email address
                            </label>

                            <input
                                type="email"
                                id="clinic_email"
                                class="form-control"
                                value="info@salmadawakhan.com"
                            >
                        </div>

                        <div class="form-field settings-field-wide">
                            <label for="clinic_address" class="form-label">
                                Clinic address
                            </label>

                            <textarea
                                id="clinic_address"
                                class="form-control"
                                rows="3"
                            >Al Nahda, Dubai, United Arab Emirates</textarea>
                        </div>
                    </div>

                    <div class="settings-panel-actions">
                        <button class="btn btn-primary">
                            Save clinic profile
                        </button>
                    </div>
                </section>

                {{-- Payment Settings --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Payment settings</h2>
                            <p>Configure currency and default payment options.</p>
                        </div>
                    </div>

                    <div class="settings-form-grid">
                        <div class="form-field">
                            <label for="currency" class="form-label">
                                Currency
                            </label>

                            <select id="currency" class="form-select">
                                <option selected>PKR — Pakistani Rupee</option>
                                <option>AED — UAE Dirham</option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label for="default_method" class="form-label">
                                Default payment method
                            </label>

                            <select id="default_method" class="form-select">
                                <option selected>Cash</option>
                                <option>Bank transfer</option>
                                <option>Card</option>
                            </select>
                        </div>
                    </div>

                    <div class="settings-toggle-row">
                        <div>
                            <strong>Show previous due on patient bills</strong>
                            <small>Display outstanding balances during payment entry.</small>
                        </div>

                        <input
                            class="form-check-input"
                            type="checkbox"
                            checked
                        >
                    </div>
                </section>

                {{-- Account Information --}}
                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>User account</h2>
                            <p>Details of the currently signed-in user.</p>
                        </div>
                    </div>

                    <div class="settings-account-row">
                        <div class="settings-account-avatar">AK</div>

                        <div>
                            <strong>Dr. Ahmed Khan</strong>
                            <small>Clinic Owner · ahmed@example.com</small>
                        </div>

                        <button class="btn btn-outline-secondary ms-auto">
                            Edit profile
                        </button>
                    </div>
                </section>

            </div>
        </section>

    </main>
</div>
@endsection