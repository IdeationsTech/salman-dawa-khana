@extends('layouts.app')

@section('title', __('Users'))

@section('content')
<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">{{ __('Team management') }}</p>
                <h1>{{ __('Users') }}</h1>
                <p class="page-subtitle">
                    {{ __('Manage clinic users, roles and access.') }}
                </p>
            </div>

            @clinicCan('users.create')<a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle" aria-hidden="true"></i>
                {{ __('Add user') }}
            </a>@endclinicCan
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="status">
                {{ __(session('success')) }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ __($error) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="users-summary-grid">
            <article class="user-summary-card">
                <span>{{ __('Total users') }}</span>
                <strong>{{ number_format($totalUsers) }}</strong>
                <small>{{ __('Clinic team members') }}</small>
            </article>

            <article class="user-summary-card">
                <span>{{ __('Active users') }}</span>
                <strong>{{ number_format($activeUsers) }}</strong>
                <small>{{ __('Currently active accounts') }}</small>
            </article>

            <article class="user-summary-card">
                <span>{{ __('Doctors / Hakeems') }}</span>
                <strong>{{ number_format($doctorUsers) }}</strong>
                <small>{{ __('Users with Doctor role') }}</small>
            </article>

            <article class="user-summary-card">
                <span>{{ __('Staff roles') }}</span>
                <strong>{{ number_format($roleCount) }}</strong>
                <small>{{ __('Available access roles') }}</small>
            </article>
        </section>

        <section class="users-panel">
            <div class="users-toolbar">
                <div>
                    <h2>{{ __('Clinic users') }}</h2>
                    <p>{{ __('Search across all users in your clinic.') }}</p>
                </div>

                <div class="users-toolbar-actions">
                    <select
                        name="status"
                        class="form-select"
                        form="user-filter-form"
                        aria-label="{{ __('User status') }}"
                    >
                        <option value="all" @selected(request('status', 'all') === 'all')>
                            {{ __('All statuses') }}
                        </option>

                        <option value="active" @selected(request('status') === 'active')>
                            {{ __('Active') }}
                        </option>

                        <option value="inactive" @selected(request('status') === 'inactive')>
                            {{ __('Inactive') }}
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
                        placeholder="{{ __('Search by name, email, phone or user ID') }}"
                        maxlength="160"
                        aria-label="{{ __('Search users') }}"
                    >

                    <button type="submit" class="btn btn-primary btn-sm">
                        {{ __('Search') }}
                    </button>

                    <a href="{{ route('users.index') }}" class="btn btn-light btn-sm">
                        {{ __('Clear') }}
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table users-table align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Email / Phone') }}</th>
                            <th>{{ __('Role') }}</th>
                            <th>{{ __('Joined') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Action') }}</th>
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
                                            <small>{{ __('User #') }}{{ $user->user_id }}</small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    {{ $user->email }}
                                    <small class="d-block text-muted">
                                        <bdi class="phone-number" dir="ltr">{{ $user->phone ?: __('Phone not recorded') }}</bdi>
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
                                        <span class="status-badge active-badge">{{ __('Active') }}</span>
                                    @else
                                        <span class="status-badge due-badge">{{ __('Inactive') }}</span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a href="{{ route('users.show', $user) }}" class="table-action">
                                        {{ __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    {{ __('No users match these filters.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="patients-pagination">
                <span>
                    {{ __('Showing') }} {{ $users->firstItem() ?? 0 }}
                    {{ __('to') }} {{ $users->lastItem() ?? 0 }}
                    {{ __('of') }} {{ $users->total() }} {{ __('users') }}
                </span>
                <div>{{ $users->links() }}</div>
            </div>
        </section>
    </main>
</div>
@endsection