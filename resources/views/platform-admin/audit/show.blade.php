@extends('platform-admin.layouts.app')

@section('title', 'Audit Event')
@section('page_title', 'Audit Event')

@section('content')
    <div class="pa-page-heading">
        <div>
            <a
                href="{{ route('platform-admin.audit.index') }}"
                class="pa-back-link"
            >
                ← Back to audit log
            </a>

            <h1 class="pa-page-title">
                {{ \Illuminate\Support\Str::headline($log->action) }}
            </h1>

            <p class="pa-page-description">
                Event #{{ $log->platform_audit_log_id }}
                · {{ \Illuminate\Support\Carbon::parse($log->created_at)->format('d M Y, h:i A') }}
            </p>
        </div>
    </div>

    <section class="pa-panel">
        <header class="pa-panel-header">
            <h2 class="pa-panel-title">Event information</h2>
        </header>

        <dl class="pa-detail-list pa-detail-list-wide">
            <div>
                <dt>Administrator</dt>
                <dd>{{ $log->admin_name ?? 'System / unavailable' }}</dd>
            </div>

            <div>
                <dt>Clinic</dt>
                <dd>{{ $log->clinic_name ?? 'Platform activity' }}</dd>
            </div>

            <div>
                <dt>Record type</dt>
                <dd>
                    {{ \Illuminate\Support\Str::headline($log->entity_type) }}
                </dd>
            </div>

            <div>
                <dt>Record ID</dt>
                <dd>{{ $log->entity_id ?? 'Not recorded' }}</dd>
            </div>

            <div>
                <dt>IP address</dt>
                <dd>{{ $log->ip_address ?? 'Not recorded' }}</dd>
            </div>

            <div>
                <dt>Action</dt>
                <dd>{{ $log->action }}</dd>
            </div>
        </dl>
    </section>

    <div class="pa-detail-grid">
        <section class="pa-panel">
            <header class="pa-panel-header">
                <h2 class="pa-panel-title">Before change</h2>
            </header>

            @if(empty($oldValues))
                <div class="pa-empty">
                    <p class="pa-empty-title">No previous values recorded</p>
                </div>
            @else
                <dl class="pa-detail-list">
                    @foreach($oldValues as $field => $value)
                        <div>
                            <dt>
                                {{ \Illuminate\Support\Str::headline($field) }}
                            </dt>
                            <dd class="pa-audit-value">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </section>

        <section class="pa-panel">
            <header class="pa-panel-header">
                <h2 class="pa-panel-title">After change</h2>
            </header>

            @if(empty($newValues))
                <div class="pa-empty">
                    <p class="pa-empty-title">No new values recorded</p>
                </div>
            @else
                <dl class="pa-detail-list">
                    @foreach($newValues as $field => $value)
                        <div>
                            <dt>
                                {{ \Illuminate\Support\Str::headline($field) }}
                            </dt>
                            <dd class="pa-audit-value">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </section>
    </div>
@endsection