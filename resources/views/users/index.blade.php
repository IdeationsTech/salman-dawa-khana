@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="app-shell">
    @include('layouts._sidebar')

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
                <i class="bi bi-plus-circle" aria-hidden="true"></i>
                Add user
            </a>
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="users-summary-grid">
            <article class="user-summary-card">
                <span>Total users</span>
                <strong>{{ number_format($totalUsers) }}</strong>
                <small>Clinic team members</small>
            </article>

            <article class="user-summary-card">
                <span>Active users</span>
                <strong>{{ number_format($activeUsers) }}</strong>
                <small>Currently active accounts</small>
            </article>

            <article class="user-summary-card">
                <span>Doctors / Hakeems</span>
                <strong>{{ number_format($doctorUsers) }}</strong>
                <small>Users with Doctor role</small>
            </article>

            <article class="user-summary-card">
                <span>Staff roles</span>
                <strong>{{ number_format($roleCount) }}</strong>
                <small>Available access roles</small>
            </article>
        </section>

        <section class="users-panel">
            <div class="users-toolbar">
                <div>
                    <h2>Clinic users</h2>
                    <p>Search across all users in your clinic.</p>
                </div>

                <div class="users-toolbar-actions">
                    <select
                        name="status"
                        class="form-select"
                        form="user-filter-form"
                        aria-label="User status"
                    >
                        <option value="all" @selected(request('status', 'all') === 'all')>
                            All statuses
                        </option>

                        <option value="active" @selected(request('status') === 'active')>
                            Active
                        </option>

                        <option value="inactive" @selected(request('status') === 'inactive')>
                            Inactive
                        </option>
                    </select>
                </div>
            </div>

            <form
                method="GET"
                action="{{ route('users.index') }}"
                id="user-filter-form"
                data-server-user-filters
            >
                <div class="user-search">
                    <span>
                        <i class="bi bi-search" aria-hidden="true"></i>
                    </span>

                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by name, email, phone or user ID"
                        maxlength="160"
                        aria-label="Search users"
                    >

                    <button type="submit" class="btn btn-primary btn-sm">
                        Search
                    </button>

                    <a href="{{ route('users.index') }}" class="btn btn-light btn-sm">
                        Clear
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table users-table align-middle">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email / Phone</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($users as $user)
                            @php
                                $role = strtolower($user->role?->name ?? 'staff');

                                $avatarClass = match ($role) {
                                    'doctor' => 'doctor-avatar',
                                    'receptionist' => 'reception-avatar',
                                    default => '',
                                };

                                $roleClass = match ($role) {
                                    'owner' => 'owner-role',
                                    'doctor' => 'doctor-role',
                                    'receptionist' => 'reception-role',
                                    default => '',
                                };
                            @endphp

                            <tr>
                                <td>
                                    <div class="user-table-identity">
                                        <span class="user-table-avatar {{ $avatarClass }}">
                                            {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                        </span>

                                        <div>
                                            <strong>{{ $user->name }}</strong>
                                            <small>User #{{ $user->user_id }}</small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    {{ $user->email }}
                                    <small class="d-block text-muted">
                                        {{ $user->phone ?: 'Phone not recorded' }}
                                    </small>
                                </td>

                                <td>
                                    <span class="role-badge {{ $roleClass }}">
                                        {{ $user->role?->name ?? 'Staff' }}
                                    </span>
                                </td>

                                <td>{{ $user->created_at?->format('d M Y') ?? '—' }}</td>

                                <td>
                                    @if ($user->is_active)
                                        <span class="status-badge active-badge">Active</span>
                                    @else
                                        <span class="status-badge due-badge">Inactive</span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a href="{{ route('users.show', $user) }}" class="table-action">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    No users match these filters.
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
                <div>{{ $users->links() }}</div>
            </div>
        </section>
    </main>
</div>
@endsection