@extends('layouts.app')

@section('title', 'Prescriptions — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Prescriptions Sidebar --}}
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

            <a href="/prescriptions" class="sidebar-link active">
                <span>✎</span>
                <span>Prescriptions</span>
            </a>

            <a href="#" class="sidebar-link">
                <span>₨</span>
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

    {{-- Prescriptions Main Content --}}
    <main class="dashboard-main">

        <header class="page-header">
            <div>
                <p class="dashboard-date">Patient care</p>
                <h1>Prescriptions</h1>
                <p class="page-subtitle">
                    Manage patient nuskhas and prescription records.
                </p>
            </div>

            <a href="#" class="btn btn-primary">
                + Create Prescription
            </a>
        </header>

        {{-- Prescription Summary --}}
        <section class="prescription-summary-grid">
            <article class="prescription-summary-card">
                <span>Total prescriptions</span>
                <strong>1,086</strong>
                <small>All clinic records</small>
            </article>

            <article class="prescription-summary-card">
                <span>Created this month</span>
                <strong>164</strong>
                <small class="stat-success">+14% from last month</small>
            </article>

            <article class="prescription-summary-card">
                <span>Today's prescriptions</span>
                <strong>18</strong>
                <small>Across today's visits</small>
            </article>

            <article class="prescription-summary-card">
                <span>Saved templates</span>
                <strong>20</strong>
                <small>Reusable nuskha templates</small>
            </article>
        </section>

        {{-- Prescription Table --}}
        <section class="prescriptions-panel">

            <div class="prescriptions-toolbar">
                <div>
                    <h2>Prescription records</h2>
                    <p>View and manage patient prescription history.</p>
                </div>

                <div class="prescriptions-toolbar-actions">
                    <select class="form-select">
                        <option>All dates</option>
                        <option>Today</option>
                        <option>This week</option>
                        <option>This month</option>
                    </select>

                    <select class="form-select">
                        <option>All statuses</option>
                        <option>Issued</option>
                        <option>Draft</option>
                    </select>
                </div>
            </div>

            <div class="prescription-search">
                <span>⌕</span>
                <input
                    type="search"
                    placeholder="Search by patient or prescription ID"
                >
            </div>

            <div class="table-responsive">
                <table class="table prescriptions-table align-middle">
                    <thead>
                        <tr>
                            <th>Prescription ID</th>
                            <th>Patient</th>
                            <th>Date</th>
                            <th>Prepared by</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>RX-00842</td>
                            <td>
                                <strong>Muhammad Ali</strong>
                                <small>P-0001</small>
                            </td>
                            <td>21 Sep 2026</td>
                            <td>Dr. Ahmed Khan</td>
                            <td>4 items</td>
                            <td>
                                <span class="status-badge active-badge">
                                    Issued
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>

                        <tr>
                            <td>RX-00841</td>
                            <td>
                                <strong>Fatima Bibi</strong>
                                <small>P-0002</small>
                            </td>
                            <td>21 Sep 2026</td>
                            <td>Dr. Ahmed Khan</td>
                            <td>3 items</td>
                            <td>
                                <span class="status-badge active-badge">
                                    Issued
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>

                        <tr>
                            <td>RX-00840</td>
                            <td>
                                <strong>Ahmed Raza</strong>
                                <small>P-0003</small>
                            </td>
                            <td>20 Sep 2026</td>
                            <td>Dr. Ahmed Khan</td>
                            <td>5 items</td>
                            <td>
                                <span class="status-badge active-badge">
                                    Issued
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>

                        <tr>
                            <td>RX-00839</td>
                            <td>
                                <strong>Ayesha Khan</strong>
                                <small>P-0004</small>
                            </td>
                            <td>20 Sep 2026</td>
                            <td>Dr. Ahmed Khan</td>
                            <td>2 items</td>
                            <td>
                                <span class="status-badge draft-badge">
                                    Draft
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="table-action">Continue</a>
                            </td>
                        </tr>

                        <tr>
                            <td>RX-00838</td>
                            <td>
                                <strong>Bilal Hussain</strong>
                                <small>P-0005</small>
                            </td>
                            <td>19 Sep 2026</td>
                            <td>Dr. Ahmed Khan</td>
                            <td>4 items</td>
                            <td>
                                <span class="status-badge active-badge">
                                    Issued
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="patients-pagination">
                <span>Showing 1 to 5 of 1,086 prescriptions</span>

                <div>
                    <button class="pagination-button">‹</button>
                    <button class="pagination-button active">1</button>
                    <button class="pagination-button">2</button>
                    <button class="pagination-button">3</button>
                    <button class="pagination-button">›</button>
                </div>
            </div>

        </section>

    </main>
</div>
@endsection