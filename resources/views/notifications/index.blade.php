@extends('layouts.app')

@section('title', 'Notifications — ' . $clinic->name)

@section('content')
@php
    $navigation = [
        ['route' => 'dashboard', 'icon' => 'bi-speedometer2', 'label' => __('Dashboard')],
        ['route' => 'patients.index', 'icon' => 'bi-person-vcard', 'label' => __('Patients')],
        ['route' => 'visits.index', 'icon' => 'bi-calendar-check', 'label' => __('Visits')],
        ['route' => 'prescriptions.index', 'icon' => 'bi-prescription', 'label' => __('Prescriptions')],
        ['route' => 'payments.index', 'icon' => 'bi-cash-coin', 'label' => __('Payments')],
        ['route' => 'expenses.index', 'icon' => 'bi-receipt', 'label' => __('Expenses')],
        ['route' => 'reports.index', 'icon' => 'bi-bar-chart-line', 'label' => __('Reports')],
        ['route' => 'notifications.index', 'icon' => 'bi-bell', 'label' => __('Notifications')],
        ['route' => 'settings.index', 'icon' => 'bi-gear', 'label' => __('Settings')],
    ];
@endphp

<div class="app-shell">

    {{-- Notifications List — Sidebar --}}
    @include('layouts._sidebar')
    
    {{-- Notifications List — Main Content --}}
    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">{{ __('Clinic activity') }}</p>
                <h1>{{ __('Notifications') }}</h1>

                <p class="page-subtitle">
                    {{ number_format($unreadCount) }} {{ __('unread') }}
                    {{ $unreadCount === 1 ? 'notification' : 'notifications' }}
                </p>
            </div>

            <a
                href="{{ route('settings.notifications') }}"
                class="btn btn-outline-secondary"
            >
                <i class="bi bi-gear app-icon" aria-hidden="true"></i>
                {{ __('Preferences') }}
            </a>
        </header>

        @if(session('success'))
            <div class="alert alert-success" role="status">
                {{ __(session('success')) }}
            </div>
        @endif

        <section class="settings-panel">

            {{-- Notifications List — Filters and Read Action --}}
            <div class="clinic-alert-toolbar">
                <form
                    method="GET"
                    action="{{ route('notifications.index') }}"
                    class="clinic-alert-filter"
                >
                    <label for="notification_status" class="visually-hidden">
                        {{ __('Notification status') }}
                    </label>

                    <select
                        id="notification_status"
                        name="status"
                        class="form-select"
                    >
                        <option value="all" @selected($status === 'all')>
                            {{ __('All notifications') }}
                        </option>

                        <option value="unread" @selected($status === 'unread')>
                            {{ __('Unread') }}
                        </option>

                        <option value="read" @selected($status === 'read')>
                            {{ __('Read') }}
                        </option>
                    </select>

                    <button type="submit" class="btn btn-outline-secondary">
                        {{ __('Apply') }}
                    </button>
                </form>

                <form
                    method="POST"
                    action="{{ route('notifications.read-all') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-primary"
                        @disabled($unreadCount === 0)
                    >
                        <i class="bi bi-check2-all app-icon" aria-hidden="true"></i>
                        {{ __('Mark all as read') }}
                    </button>
                </form>
            </div>

            {{-- Notifications List — Alert Cards --}}
            <div class="clinic-alert-list">
                @forelse($notifications as $notification)
                    @php
                        $isUnread = $notification->read_at === null;

                        $icon = match($notification->type) {
                            'patient_registered' => 'bi-person-plus',
                            'payment_received' => 'bi-cash-coin',
                            default => 'bi-bell',
                        };

                        // Only known application destinations are rendered.
                        $allowedRoutes = [
                            'patients.show',
                            'payments.show',
                            'payments.bills.show',
                        ];

                        $destination = null;

                        if (
                            in_array($notification->route_name, $allowedRoutes, true) &&
                            \Illuminate\Support\Facades\Route::has($notification->route_name)
                        ) {
                            $parameters = json_decode(
                                $notification->route_parameters ?? '{}',
                                true
                            );

                            if (is_array($parameters)) {
                                $destination = route(
                                    $notification->route_name,
                                    $parameters
                                );
                            }
                        }
                    @endphp

                    <article class="clinic-alert {{ $isUnread ? 'is-unread' : '' }}">
                        <div class="clinic-alert-icon">
                            <i class="bi {{ $icon }}" aria-hidden="true"></i>
                        </div>

                        <div class="clinic-alert-content">
                            <div class="clinic-alert-heading">
                                <h2>{{ $notification->title }}</h2>

                                <span class="clinic-alert-status">
                                    {{ $isUnread ? __('Unread') : __('Read') }}
                                </span>
                            </div>

                            <p>{{ $notification->message }}</p>

                            <time datetime="{{ \Carbon\Carbon::parse($notification->created_at)->toIso8601String() }}">
                                {{ \Carbon\Carbon::parse($notification->created_at)
                                    ->timezone('Asia/Dubai')
                                    ->format('d M Y, h:i A') }}
                                — UAE time
                            </time>

                            <div class="clinic-alert-actions">
                                @if($destination)
                                    <a
                                        href="{{ $destination }}"
                                        class="btn btn-sm btn-outline-secondary"
                                    >
                                        {{ __('View record') }}
                                    </a>
                                @endif

                                @if($isUnread)
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'notifications.read',
                                            $notification->clinic_notification_id
                                        ) }}"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-secondary"
                                        >
                                            {{ __('Mark as read') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="clinic-alert-empty">
                        <i class="bi bi-bell" aria-hidden="true"></i>
                        <h2>{{ __('No notifications') }}</h2>
                        <p>{{ __('No alerts match this filter.') }}</p>
                    </div>
                @endforelse
            </div>

            <div class="patients-pagination">
                <span>
                    {{ __('Showing') }} {{ $notifications->firstItem() ?? 0 }}
                    {{ __('to') }} {{ $notifications->lastItem() ?? 0 }}
                    {{ __('of') }} {{ $notifications->total() }}
                </span>

                {{ $notifications->links('pagination::bootstrap-5') }}
            </div>
        </section>
    </main>
</div>
@endsection