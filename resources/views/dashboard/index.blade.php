@extends('layouts.app')

@section('title', 'Dashboard — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Dashboard Sidebar --}}
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">+</div>
            <span>Salman Dawa Khana</span>
        </div>

        <nav class="sidebar-nav">
            <a href="#" class="sidebar-link active">
                <span>▦</span>
                <span>Dashboard</span>
            </a>

            <a href="#" class="sidebar-link">
                <span>♙</span>
                <span>Patients</span>
            </a>

            <a href="#" class="sidebar-link">
                <span>▣</span>
                <span>Visits</span>
            </a>

            <a href="#" class="sidebar-link">
                <span>✎</span>
                <span>Prescriptions</span>
            </a>

            <a href="#" class="sidebar-link">
                <span>▤</span>
                <span>Payments</span>
            </a>

            <a href="#" class="sidebar-link">
                <span>◈</span>
                <span>Expenses</span>
            </a>

            <div class="sidebar-divider"></div>

            <a href="#" class="sidebar-link">
                <span>◌</span>
                <span>Reports</span>
            </a>

            <a href="#" class="sidebar-link">
                <span>⚙</span>
                <span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    {{-- Dashboard Main Area --}}
    <main class="dashboard-main">

        {{-- Topbar --}}
        <header class="dashboard-topbar">
            <div>
                <p class="dashboard-date">Monday, 21 September 2026</p>
                <h1>Good morning, Dr. Ahmed</h1>
            </div>

            <div class="topbar-user">
                <div class="user-avatar">AK</div>
                <div>
                    <strong>Dr. Ahmed Khan</strong>
                    <small>Clinic Owner</small>
                </div>
            </div>
        </header>

        {{-- Summary Cards --}}
        <section class="dashboard-section">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">OVERVIEW</p>
                    <h2>Clinic summary</h2>
                </div>

                <button class="btn btn-primary">
                    + Add Patient
                </button>
            </div>

            <div class="summary-grid">
                <article class="summary-card">
                    <div class="summary-icon teal-icon">♙</div>
                    <div>
                        <p>Total Patients</p>
                        <h3>1,248</h3>
                        <span class="summary-positive">+12 this month</span>
                    </div>
                </article>

                <article class="summary-card">
                    <div class="summary-icon blue-icon">▣</div>
                    <div>
                        <p>Today's Visits</p>
                        <h3>18</h3>
                        <span class="summary-positive">+6 from yesterday</span>
                    </div>
                </article>

                <article class="summary-card">
                    <div class="summary-icon orange-icon">₨</div>
                    <div>
                        <p>Today's Income</p>
                        <h3>PKR 86,300</h3>
                        <span class="summary-positive">+18% this week</span>
                    </div>
                </article>

                <article class="summary-card">
                    <div class="summary-icon purple-icon">◷</div>
                    <div>
                        <p>Outstanding</p>
                        <h3>PKR 42,500</h3>
                        <span class="summary-warning">12 patients due</span>
                    </div>
                </article>
            </div>
        </section>

        {{-- Dashboard Content --}}
        <section class="dashboard-content-grid">

            <div class="dashboard-panel">
                <div class="panel-heading">
                    <div>
                        <h2>Recent patients</h2>
                        <p>Latest patient records added to the clinic</p>
                    </div>

                    <a href="#" class="panel-link">View all</a>
                </div>

                <div class="table-responsive">
                    <table class="table dashboard-table align-middle">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Phone</th>
                                <th>Last Visit</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td><strong>Muhammad Ali</strong></td>
                                <td>050 123 4567</td>
                                <td>Today, 10:30 AM</td>
                                <td><span class="status-badge active-badge">Active</span></td>
                            </tr>

                            <tr>
                                <td><strong>Fatima Bibi</strong></td>
                                <td>050 234 5678</td>
                                <td>Today, 09:15 AM</td>
                                <td><span class="status-badge active-badge">Active</span></td>
                            </tr>

                            <tr>
                                <td><strong>Ahmed Raza</strong></td>
                                <td>050 345 6789</td>
                                <td>Yesterday</td>
                                <td><span class="status-badge active-badge">Active</span></td>
                            </tr>

                            <tr>
                                <td><strong>Ayesha Khan</strong></td>
                                <td>050 456 7890</td>
                                <td>Yesterday</td>
                                <td><span class="status-badge due-badge">Payment due</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="panel-heading">
                    <div>
                        <h2>Quick actions</h2>
                        <p>Common clinic tasks</p>
                    </div>
                </div>

                <div class="quick-actions">
                    <a href="#" class="quick-action">
                        <span class="quick-action-icon">♙</span>
                        <span>
                            <strong>Add patient</strong>
                            <small>Create a new patient record</small>
                        </span>
                        <span>›</span>
                    </a>

                    <a href="#" class="quick-action">
                        <span class="quick-action-icon">✎</span>
                        <span>
                            <strong>Create prescription</strong>
                            <small>Prepare a patient nuskha</small>
                        </span>
                        <span>›</span>
                    </a>

                    <a href="#" class="quick-action">
                        <span class="quick-action-icon">₨</span>
                        <span>
                            <strong>Record payment</strong>
                            <small>Add cash or bank payment</small>
                        </span>
                        <span>›</span>
                    </a>
                </div>
            </div>

        </section>

    </main>
</div>
@endsection