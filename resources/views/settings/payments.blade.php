@extends('layouts.app')

@section('title', 'Payment Settings — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Payment Settings Sidebar --}}
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

            <a href="/settings/payments" class="sidebar-link active">
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
                <h1>Payment settings</h1>
                <p class="page-subtitle">
                    Configure currency and payment preferences.
                </p>
            </div>
        </header>

        <section class="settings-layout">

            <aside class="settings-navigation">
                <a href="/settings" class="settings-nav-item">
                    <span>▣</span> Clinic profile
                </a>

                <a href="/settings/payments" class="settings-nav-item active">
                    <span>₨</span> Payment settings
                </a>

                <a href="/settings/account" class="settings-nav-item">
                    <span>♙</span> User account
                </a>

                <a href="/settings/notifications" class="settings-nav-item">
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
                            <h2>Currency and format</h2>
                            <p>Choose how amounts appear throughout the system.</p>
                        </div>
                    </div>

                    <div class="settings-form-grid">
                        <div class="form-field">
                            <label for="currency" class="form-label">
                                Default currency
                            </label>

                            <select id="currency" class="form-select">
                                <option selected>PKR — Pakistani Rupee</option>
                                <option>AED — UAE Dirham</option>
                                <option>USD — US Dollar</option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label for="decimal_places" class="form-label">
                                Decimal places
                            </label>

                            <select id="decimal_places" class="form-select">
                                <option selected>2 decimal places</option>
                                <option>0 decimal places</option>
                            </select>
                        </div>
                    </div>

                    <div class="settings-panel-actions">
                        <button class="btn btn-primary">
                            Save currency settings
                        </button>
                    </div>
                </section>

                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Payment methods</h2>
                            <p>Enable or disable methods accepted by the clinic.</p>
                        </div>
                    </div>

                    <div class="settings-toggle-list">
                        <div class="settings-toggle-row">
                            <div>
                                <strong>Cash payments</strong>
                                <small>Accept cash payments from patients.</small>
                            </div>

                            <input class="form-check-input" type="checkbox" checked>
                        </div>

                        <div class="settings-toggle-row">
                            <div>
                                <strong>Bank transfers</strong>
                                <small>Record bank transfer references.</small>
                            </div>

                            <input class="form-check-input" type="checkbox" checked>
                        </div>

                        <div class="settings-toggle-row">
                            <div>
                                <strong>Card payments</strong>
                                <small>Record card or POS payments.</small>
                            </div>

                            <input class="form-check-input" type="checkbox" checked>
                        </div>
                    </div>
                </section>

                <section class="settings-panel">
                    <div class="settings-panel-heading">
                        <div>
                            <h2>Billing preferences</h2>
                            <p>Control how patient balances are displayed.</p>
                        </div>
                    </div>

                    <div class="settings-toggle-list">
                        <div class="settings-toggle-row">
                            <div>
                                <strong>Show previous due</strong>
                                <small>Include previous outstanding balance on bills.</small>
                            </div>

                            <input class="form-check-input" type="checkbox" checked>
                        </div>

                        <div class="settings-toggle-row">
                            <div>
                                <strong>Allow partial payments</strong>
                                <small>Allow patients to pay less than the total due.</small>
                            </div>

                            <input class="form-check-input" type="checkbox" checked>
                        </div>
                    </div>
                </section>

            </div>
        </section>

    </main>
</div>
@endsection