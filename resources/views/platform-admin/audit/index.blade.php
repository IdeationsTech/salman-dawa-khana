@extends('platform-admin.layouts.app')

@section('title', 'Audit Log')
@section('page_title', 'Audit Log')

@section('content')
    <div class="pa-page-heading">
        <div>
            <h1 class="pa-page-title">{{ __('Audit log') }}</h1>
            <p class="pa-page-description">
                {{ __('Recorded changes to clinics, subscriptions and payments.') }}
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="pa-notice pa-notice-error" role="alert">
            <strong>{{ __('Please correct the following:') }}</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ __($error) }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="pa-panel">
        <header class="pa-panel-header">
            <div>
                <h2 class="pa-panel-title">{{ __('Activity history') }}</h2>
                <p class="pa-panel-description">
                    {{ $logs->total() }} {{ __('matching events') }}
                </p>
            </div>
        </header>

        <div class="pa-panel-body">
            <form
                method="GET"
                action="{{ route('platform-admin.audit.index') }}"
                class="pa-form"
            >
                <div class="pa-form-grid">
                    <div class="pa-field">
                        <label for="audit-search">{{ __('Search') }}</label>
                        <input
                            id="audit-search"
                            type="search"
                            name="search"
                            class="pa-input"
                            maxlength="160"
                            placeholder="{{ __('Clinic, admin, action or event ID') }}"
                            value="{{ $filters['search'] }}"
                        >
                    </div>

                    <div class="pa-field">
                        <label for="audit-action">{{ __('Action') }}</label>
                        <select
                            id="audit-action"
                            name="action"
                            class="pa-input"
                        >
                            <option value="">{{ __('All actions') }}</option>

                            @foreach($actions as $action)
                                <option
                                    value="{{ $action }}"
                                    @selected($filters['action'] === $action)
                                >
                                    {{ \Illuminate\Support\Str::headline($action) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pa-field">
                        <label for="audit-admin">{{ __('Administrator') }}</label>
                        <select
                            id="audit-admin"
                            name="admin_id"
                            class="pa-input"
                        >
                            <option value="">{{ __('All administrators') }}</option>

                            @foreach($admins as $admin)
                                <option
                                    value="{{ $admin->platform_admin_id }}"
                                    @selected(
                                        (string) $filters['admin_id']
                                        === (string) $admin->platform_admin_id
                                    )
                                >
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pa-field">
                        <label for="audit-from">{{ __('From') }}</label>
                        <input
                            id="audit-from"
                            type="date"
                            name="from"
                            class="pa-input"
                            value="{{ $filters['from'] }}"
                        >
                    </div>

                    <div class="pa-field">
                        <label for="audit-to">{{ __('To') }}</label>
                        <input
                            id="audit-to"
                            type="date"
                            name="to"
                            class="pa-input"
                            value="{{ $filters['to'] }}"
                        >
                    </div>
                </div>

                <div class="pa-audit-actions">
                    <button
                        type="submit"
                        class="pa-button pa-button-primary"
                    >
                        {{ __('Apply filters') }}
                    </button>

                    <a
                        href="{{ route('platform-admin.audit.index') }}"
                        class="pa-button"
                    >
                        {{ __('Clear') }}
                    </a>
                </div>
            </form>
        </div>

        @if($logs->isEmpty())
            <div class="pa-empty">
                <p class="pa-empty-title">{{ __('No activity found') }}</p>
                <p class="pa-empty-description">
                    {{ __('Try changing the search or filters.') }}
                </p>
            </div>
        @else
            <div class="pa-table-wrap">
                <table class="pa-table">
                    <thead>
                        <tr>
                            <th>{{ __('Event') }}</th>
                            <th>{{ __('Action') }}</th>
                            <th>{{ __('Administrator') }}</th>
                            <th>{{ __('Clinic') }}</th>
                            <th>{{ __('Date & time') }}</th>
                            <th>{{ __('Details') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>#{{ $log->platform_audit_log_id }}</td>

                                <td>
                                    <span class="pa-cell-title">
                                        {{ \Illuminate\Support\Str::headline($log->action) }}
                                    </span>
                                    <span class="pa-cell-subtitle">
                                        {{ \Illuminate\Support\Str::headline($log->entity_type) }}
                                        @if($log->entity_id)
                                            #{{ $log->entity_id }}
                                        @endif
                                    </span>
                                </td>

                                <td>
                                    {{ $log->admin_name ?? 'System / unavailable' }}
                                </td>

                                <td>
                                    @if($log->clinic_name)
                                        <a
                                            href="{{ route('platform-admin.clinics.show', $log->clinic_id) }}"
                                            class="pa-link"
                                        >
                                            {{ $log->clinic_name }}
                                        </a>
                                    @else
                                        <span class="pa-cell-subtitle">
                                            {{ __('Platform activity') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="pa-nowrap">
                                    {{ \Illuminate\Support\Carbon::parse($log->created_at)->format('d M Y, h:i A') }}
                                </td>

                                <td>
                                    <a
                                        href="{{ route('platform-admin.audit.show', $log->platform_audit_log_id) }}"
                                        class="pa-link"
                                    >
                                        {{ __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="pa-panel-body">
                    {{ $logs->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @endif
    </section>
@endsection