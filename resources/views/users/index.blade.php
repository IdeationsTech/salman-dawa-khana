@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="page-header">
    <div>
        <span class="eyebrow">TEAM MANAGEMENT</span>
        <h1>Users</h1>
        <p>Manage clinic users, roles and access.</p>
    </div>

    <a href="{{ route('users.create') }}" class="btn btn-primary">
        + Add User
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card users-card">
    <div class="card-header">
        <form method="GET" action="{{ route('users.index') }}" class="users-toolbar">
            <div class="search-box">
                <span>⌕</span>

                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by name or email"
                >
            </div>

            <select name="status" class="form-select">
                <option value="all">All Statuses</option>

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

            <button type="submit" class="btn btn-outline-primary">
                Filter
            </button>

            <a href="{{ route('users.index') }}" class="btn btn-light">
                Clear
            </a>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table users-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="user-name">
                                <span class="avatar-circle">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>

                                <strong>{{ $user->name }}</strong>
                            </div>
                        </td>

                        <td>{{ $user->email }}</td>

                        <td>
                            {{ $user->role->name ?? '—' }}
                        </td>

                        <td>{{ $user->phone ?? '—' }}</td>

                        <td>
                            @if($user->is_active)
                                <span class="status-badge status-active">
                                    Active
                                </span>
                            @else
                                <span class="status-badge status-inactive">
                                    Inactive
                                </span>
                            @endif
                        </td>

                        <td>
                            <a
                                href="{{ route('users.show', $user) }}"
                                class="btn btn-sm btn-outline-primary"
                            >
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            No users found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="card-footer">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection