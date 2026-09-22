@extends('layouts.app')

@section('title', 'Users & Roles — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Users Sidebar --}}
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

            <a href="/settings" class="sidebar-link">
                <span>⚙</span><span>Settings</span>
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

    {{-- Users Main Content --}}
    <main class="dashboard-main">

        <header class="page-header">
            <div>
                <p class="dashboard-date">Access management</p>
                <h1>Users &amp; Roles</h1>
                <p class="page-subtitle">
                    Manage clinic staff and their system permissions.
                </p>
            </div>

            <a href="#" class="btn btn-primary">
                + Add User
            </a>
        </header>

        {{-- User Summary --}}
        <section class="users-summary-grid">
            <article class="user-summary-card">
                <span>Total users</span>
                <strong>4</strong>
                <small>Clinic staff accounts</small>
            </article>

            <article class="user-summary-card">
                <span>Active users</span>
                <strong>4</strong>
                <small class="stat-success">All accounts active</small>
            </article>

            <article class="user-summary-card">
                <span>Available roles</span>
                <strong>4</strong>
                <small>Owner, Doctor, Receptionist, Accountant</small>
            </article>

            <article class="user-summary-card">
                <span>Last activity</span>
                <strong>2 min ago</strong>
                <small>Dr. Ahmed Khan</small>
            </article>
        </section>

        {{-- Users Table --}}
        <section class="users-panel">

            <div class="users-toolbar">
                <div>
                    <h2>Clinic users</h2>
                    <p>View and manage staff access.</p>
                </div>

                <div class="users-toolbar-actions">
                    <select class="form-select">
                        <option>All roles</option>
                        <option>Owner</option>
                        <option>Doctor</option>
                        <option>Receptionist</option>
                        <option>Accountant</option>
                    </select>

                    <select class="form-select">
                        <option>All statuses</option>
                        <option>Active</option>
                        <option>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="user-search">
                <span>⌕</span>
                <input
                    type="search"
                    placeholder="Search by name or email"
                >
            </div>

            <div class="table-responsive">
                <table class="table users-table align-middle">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Phone</th>
                            <th>Last login</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>
                                <div class="user-table-identity">
                                    <div class="user-table-avatar">AK</div>
                                    <div>
                                        <strong>Dr. Ahmed Khan</strong>
                                        <small>ahmed@example.com</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge owner-role">Owner</span>
                            </td>
                            <td>+971 50 123 4567</td>
                            <td>2 minutes ago</td>
                            <td>
                                <span class="status-badge active-badge">Active</span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="table-action">Manage</a>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="user-table-identity">
                                    <div class="user-table-avatar doctor-avatar">SA</div>
                                    <div>
                                        <strong>Dr. Sara Ahmed</strong>
                                        <small>sara@example.com</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge doctor-role">Doctor</span>
                            </td>
                            <td>+971 50 222 3344</td>
                            <td>Today, 09:10 AM</td>
                            <td>
                                <span class="status-badge active-badge">Active</span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="table-action">Manage</a>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="user-table-identity">
                                    <div class="user-table-avatar reception-avatar">FN</div>
                                    <div>
                                        <strong>Fatima Noor</strong>
                                        <small>fatima@example.com</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge reception-role">
                                    Receptionist
                                </span>
                            </td>
                            <td>+971 50 333 4455</td>
                            <td>Today, 08:45 AM</td>
                            <td>
                                <span class="status-badge active-badge">Active</span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="table-action">Manage</a>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="user-table-identity">
                                    <div class="user-table-avatar accountant-avatar">MK</div>
                                    <div>
                                        <strong>Mohammad Khan</strong>
                                        <small>mohammad@example.com</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge accountant-role">
                                    Accountant
                                </span>
                            </td>
                            <td>+971 50 444 5566</td>
                            <td>Yesterday</td>
                            <td>
                                <span class="status-badge active-badge">Active</span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="table-action">Manage</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="patients-pagination">
                <span>Showing 1 to 4 of 4 users</span>
            </div>

        </section>

    </main>
</div>
@endsection