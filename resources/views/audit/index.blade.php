@extends('layouts.app')

@section('title', 'Activity History')

@section('content')
<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">Clinic administration</p>
                <h1>Activity history</h1>
                <p class="page-subtitle">
                    Review record changes made by your clinic team.
                </p>
            </div>
        </header>

        <section class="orders-panel">
            <form method="GET" action="{{ route('audit.index') }}" class="orders-filter-grid">
                <label class="form-field orders-search-field">
                    <span class="form-label">Search</span>
                    <input type="search" name="search" class="form-control" value="{{ request('search') }}" maxlength="160" placeholder="User, action or record type">
                </label>

                <label class="form-field">
                    <span class="form-label">Action</span>
                    <select name="action" class="form-select">
                        <option value="">All actions</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>
                                {{ \Illuminate\Support\Str::headline($action) }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="form-field">
                    <span class="form-label">From date</span>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </label>

                <label class="form-field">
                    <span class="form-label">To date</span>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </label>

                <div class="orders-filter-actions">
                    <button type="submit" class="btn btn-primary">Apply</button>
                    <a href="{{ route('audit.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table orders-table">
                    <thead>
                        <tr><th>Event</th><th>User</th><th>Action</th><th>Record</th><th>Date/time</th><th>Details</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>#{{ $log->audit_log_id }}</td>
                                <td>{{ $log->actor_name ?: 'User #' . $log->user_id }}</td>
                                <td>{{ \Illuminate\Support\Str::headline($log->action) }}</td>
                                <td>
                                    {{ \Illuminate\Support\Str::headline($log->entity_type) }}
                                    {{ $log->entity_id ? '#' . $log->entity_id : '' }}
                                </td>
                                <td>{{ \Illuminate\Support\Carbon::parse($log->created_at)->format('d M Y, h:i A') }}</td>
                                <td>
                                    <details class="clinic-audit-details">
                                        <summary>View</summary>
                                        <pre>{{ json_encode(json_decode($log->metadata ?? '{}', true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4">No activity recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="patients-pagination">
                <span>
                    Showing {{ $logs->firstItem() ?? 0 }}
                    to {{ $logs->lastItem() ?? 0 }}
                    of {{ $logs->total() }} events
                </span>
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </section>
    </main>
</div>
@endsection