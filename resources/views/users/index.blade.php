@extends('layouts.app')

@section('title', 'Users — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">+</div>
            <span>Salman Dawa Khana</span>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="sidebar-link">
                <span>▦</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('patients.index') }}" class="sidebar-link">
                <span>♙</span>
                <span>Patients</span>
            </a>

            <a href="{{ route('visits.index') }}" class="sidebar-link">
                <span>▣</span>
                <span>Visits</span>
            </a>

            <a href="{{ route('prescriptions.index') }}" class="sidebar-link">
                <span>✎</span>
                <span>Prescriptions</span>
            </a>

            <a href="{{ route('payments.index') }}" class="sidebar-link">
                <span>₨</span>
                <span>Payments</span>
            </a>

            <a href="{{ route('expenses.index') }}" class="sidebar-link">
                <span>◈</span>
                <span>Expenses</span>
            </a>

            <div class="sidebar-divider"></div>

            <a href="{{ route('reports.index') }}" class="sidebar-link">
                <span>◌</span>
                <span>Reports</span>
            </a>

            <a href="{{ route('settings.index') }}" class="sidebar-link">
                <span>⚙</span>
                <span>Settings</span>
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
                <p class="dashboard-date">Team management</p>

                <h1>Users</h1>

                <p class="page-subtitle">
                    Manage clinic users, roles and access.
                </p>
            </div>

            <a href="{{ route('users.create') }}" class="btn btn-primary">
                + Add User
            </a>
        </header>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <section class="users-summary-grid">
            <article class="user-summary-card">
                <span>Total users</span>

                <strong>
                    {{ number_format($totalUsers ?? $users->total()) }}
                </strong>

                <small>Clinic team members</small>
            </article>

            <article class="user-summary-card">
                <span>Active users</span>

                <strong>
                    {{ number_format($activeUsers ?? 0) }}
                </strong>

                <small>Currently active accounts</small>
            </article>

            <article class="user-summary-card">
                <span>Doctors</span>

                <strong>
                    {{ number_format($doctorUsers ?? 0) }}
                </strong>

                <small>Users with Doctor role</small>
            </article>

            <article class="user-summary-card">
                <span>Staff roles</span>

                <strong>
                    {{ number_format($roleCount ?? 0) }}
                </strong>

                <small>Available access roles</small>
            </article>
        </section>

        <section class="users-panel">

            <div class="users-toolbar">
                <div>
                    <h2>Clinic users</h2>

                    <p>
                        View and manage user accounts and access roles.
                    </p>
                </div>

                <div class="users-toolbar-actions">
                    <select
                        name="status"
                        class="form-select"
                        form="user-filter-form"
                    >
                        <option
                            value="all"
                            {{ request('status', 'all') === 'all' ? 'selected' : '' }}
                        >
                            All statuses
                        </option>

                        <option
                            value="active"
                            {{ request('status') === 'active' ? 'selected' : '' }}
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            {{ request('status') === 'inactive' ? 'selected' : '' }}
                        >
                            Inactive
                        </option>
                    </select>
                </div>
            </div>

            <form
                method="GET"
                action="{{ route('users.index') }}"
                id="user-filter-form"
            >
                <div class="user-search">
                    <span>⌕</span>

                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by name or email"
                    >
                </div>
            </form>

            <div class="table-responsive">
                <table class="table users-table align-middle">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($users as $user)
                            @php
                                $role = strtolower(
                                    $user->role->name ?? 'staff'
                                );

                                $avatarClass = match ($role) {
                                    'doctor' => 'doctor-avatar',
                                    'receptionist' => 'reception-avatar',
                                    'accountant' => 'accountant-avatar',
                                    default => '',
                                };

                                $roleClass = match ($role) {
                                    'owner' => 'owner-role',
                                    'doctor' => 'doctor-role',
                                    'receptionist' => 'reception-role',
                                    'accountant' => 'accountant-role',
                                    default => 'owner-role',
                                };
                            @endphp

                            <tr>
                                <td>
                                    <div class="user-table-identity">
                                        <span class="user-table-avatar {{ $avatarClass }}">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>

                                        <div>
                                            <strong>{{ $user->name }}</strong>

                                            <small>
                                                User #{{ $user->user_id }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    {{ $user->email }}
                                </td>

                                <td>
                                    <span class="role-badge {{ $roleClass }}">
                                        {{ $user->role->name ?? 'Staff' }}
                                    </span>
                                </td>

                                <td>
                                    {{ optional($user->created_at)->format('d M Y') }}
                                </td>

                                <td>
                                    @if($user->is_active)
                                        <span class="status-badge active-badge">
                                            Active
                                        </span>
                                    @else
                                        <span class="status-badge due-badge">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('users.show', $user) }}"
                                        class="table-action"
                                    >
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="patients-pagination">
                <span>
                    Showing {{ $users->firstItem() ?? 0 }}
                    to {{ $users->lastItem() ?? 0 }}
                    of {{ $users->total() }} users
                </span>

                <div>
                    {{ $users->links() }}
                </div>
            </div>

        </section>

    </main>
</div>
@endsection