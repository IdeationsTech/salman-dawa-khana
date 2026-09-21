@extends('layouts.app')

@section('title', 'Visits — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Visits Sidebar --}}
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

            <a href="/visits" class="sidebar-link active">
                <span>▣</span>
                <span>Visits</span>
            </a>

            <a href="#" class="sidebar-link">
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

    {{-- Visits Main Content --}}
    <main class="dashboard-main">

        <header class="page-header">
            <div>
                <p class="dashboard-date">Clinic management</p>
                <h1>Visits</h1>
                <p class="page-subtitle">
                    Track patient visits and consultation records.
                </p>
            </div>

            <a href="#" class="btn btn-primary">
                + New Visit
            </a>
        </header>

        {{-- Visit Summary --}}
        <section class="visit-summary-grid">
            <article class="visit-summary-card">
                <span>Today's visits</span>
                <strong>18</strong>
                <small class="stat-success">+6 from yesterday</small>
            </article>

            <article class="visit-summary-card">
                <span>This month's visits</span>
                <strong>386</strong>
                <small>September 2026</small>
            </article>

            <article class="visit-summary-card">
                <span>Completed visits</span>
                <strong>372</strong>
                <small class="stat-success">96% completion rate</small>
            </article>

            <article class="visit-summary-card">
                <span>Follow-ups</span>
                <strong>24</strong>
                <small class="stat-warning">Require attention</small>
            </article>
        </section>

        {{-- Visit Table --}}
        <section class="visits-panel">

            <div class="visits-toolbar">
                <div>
                    <h2>Visit records</h2>
                    <p>View and manage patient visit history.</p>
                </div>

                <div class="visits-toolbar-actions">
                    <select class="form-select">
                        <option>All dates</option>
                        <option>Today</option>
                        <option>This week</option>
                        <option>This month</option>
                    </select>

                    <select class="form-select">
                        <option>All statuses</option>
                        <option>Completed</option>
                        <option>Follow-up</option>
                    </select>
                </div>
            </div>

            <div class="visit-search">
                <span>⌕</span>
                <input
                    type="search"
                    placeholder="Search by patient name or visit ID"
                >
            </div>

            <div class="table-responsive">
                <table class="table visits-table align-middle">
                    <thead>
                        <tr>
                            <th>Visit ID</th>
                            <th>Patient</th>
                            <th>Date &amp; Time</th>
                            <th>Visit Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>V-00482</td>
                            <td>
                                <strong>Muhammad Ali</strong>
                                <small>P-0001</small>
                            </td>
                            <td>21 Sep 2026<br><small>10:30 AM</small></td>
                            <td>Follow-up</td>
                            <td>PKR 2,500</td>
                            <td>
                                <span class="status-badge active-badge">
                                    Completed
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>

                        <tr>
                            <td>V-00481</td>
                            <td>
                                <strong>Fatima Bibi</strong>
                                <small>P-0002</small>
                            </td>
                            <td>21 Sep 2026<br><small>09:15 AM</small></td>
                            <td>General visit</td>
                            <td>PKR 2,500</td>
                            <td>
                                <span class="status-badge active-badge">
                                    Completed
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>

                        <tr>
                            <td>V-00480</td>
                            <td>
                                <strong>Ahmed Raza</strong>
                                <small>P-0003</small>
                            </td>
                            <td>20 Sep 2026<br><small>04:20 PM</small></td>
                            <td>General visit</td>
                            <td>PKR 2,500</td>
                            <td>
                                <span class="status-badge active-badge">
                                    Completed
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>

                        <tr>
                            <td>V-00479</td>
                            <td>
                                <strong>Ayesha Khan</strong>
                                <small>P-0004</small>
                            </td>
                            <td>20 Sep 2026<br><small>02:10 PM</small></td>
                            <td>Follow-up</td>
                            <td>PKR 2,500</td>
                            <td>
                                <span class="status-badge due-badge">
                                    Follow-up
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>

                        <tr>
                            <td>V-00478</td>
                            <td>
                                <strong>Bilal Hussain</strong>
                                <small>P-0005</small>
                            </td>
                            <td>19 Sep 2026<br><small>11:45 AM</small></td>
                            <td>General visit</td>
                            <td>PKR 2,500</td>
                            <td>
                                <span class="status-badge active-badge">
                                    Completed
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
                <span>Showing 1 to 5 of 386 visits</span>

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