@extends('platform-admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
    @php
        $dashboardNow = now();

        $statCards = [
            [
                'label' => 'Total clinics',
                'value' => $stats['total_clinics'],
                'note' => 'All registered clinics',
                'icon' => 'clinic',
            ],
            [
                'label' => 'Pending approvals',
                'value' => $stats['pending_approvals'],
                'note' => 'Awaiting your review',
                'icon' => 'clock',
            ],
            [
                'label' => 'Active licenses',
                'value' => $stats['active_licenses'],
                'note' => 'Currently valid clinic licenses',
                'icon' => 'shield',
            ],
            [
                'label' => 'Pending payments',
                'value' => $stats['pending_payments'],
                'note' => 'Awaiting payment verification',
                'icon' => 'payments',
            ],
        ];
    @endphp

    {{-- DASHBOARD — Heading --}}
    <div class="pa-page-heading">
        <div>
            <h1 class="pa-page-title">
                Platform overview
            </h1>

            <p class="pa-page-description">
                Monitor clinic registrations, licenses and subscription payments.
            </p>
        </div>

        <span class="pa-page-date">
            {{ $dashboardNow->format('D, d M Y') }}
        </span>
    </div>

    {{-- DASHBOARD — Statistics --}}
    <section class="pa-stats" aria-label="Platform statistics">
        @foreach($statCards as $card)
            <article class="pa-stat">
                <div class="pa-stat-top">
                    <h2 class="pa-stat-label">
                        {{ $card['label'] }}
                    </h2>

                    <span class="pa-stat-icon">
                        <svg class="pa-icon" aria-hidden="true">
                            <use href="#pa-icon-{{ $card['icon'] }}"></use>
                        </svg>
                    </span>
                </div>

                <p class="pa-stat-value">
                    {{ number_format($card['value']) }}
                </p>

                <p class="pa-stat-note">
                    {{ $card['note'] }}
                </p>
            </article>
        @endforeach
    </section>

    {{-- DASHBOARD — Latest Clinics --}}
    <section
        class="pa-panel"
        id="recent-clinics"
        aria-labelledby="recent-clinics-title"
    >
        <header class="pa-panel-header">
            <div>
                <h2 class="pa-panel-title" id="recent-clinics-title">
                    Latest clinics
                </h2>

                <p class="pa-panel-description">
                    The eight most recently registered clinics.
                </p>
            </div>
        </header>

        @if($recentClinics->isEmpty())
            <div class="pa-empty">
                <p class="pa-empty-title">
                    No clinics registered yet
                </p>

                <p class="pa-empty-description">
                    New clinic registrations will appear here.
                </p>
            </div>
        @else
            <div class="pa-table-wrap">
                <table class="pa-table">
                    <thead>
                        <tr>
                            <th scope="col">Clinic</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Approval</th>
                            <th scope="col">License</th>
                            <th scope="col">Registered</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($recentClinics as $clinic)
                            @php
                                $licenseLabel = 'Not issued';

                                if ($clinic->clinic_license_id) {
                                    if ($clinic->license_status !== 'active') {
                                        $licenseLabel = \Illuminate\Support\Str::headline(
                                            $clinic->license_status
                                        );
                                    } elseif ($clinic->onboarding_status !== 'approved') {
                                        $licenseLabel = 'Clinic not approved';
                                    } elseif (
                                        \Illuminate\Support\Carbon::parse($clinic->starts_at)
                                            ->gt($dashboardNow)
                                    ) {
                                        $licenseLabel = 'Scheduled';
                                    } elseif (
                                        $clinic->ends_at !== null &&
                                        \Illuminate\Support\Carbon::parse($clinic->ends_at)
                                            ->lte($dashboardNow)
                                    ) {
                                        $licenseLabel = 'Expired';
                                    } elseif (
                                        $clinic->ends_at === null &&
                                        $clinic->term_type !== 'lifetime'
                                    ) {
                                        $licenseLabel = 'Expiry missing';
                                    } else {
                                        $licenseLabel = 'Active';
                                    }
                                }
                            @endphp

                            <tr>
                                <td>
                                    <span class="pa-cell-title">
                                        {{ $clinic->name }}
                                    </span>

                                    <span class="pa-cell-subtitle">
                                        Clinic #{{ $clinic->clinic_id }}
                                    </span>
                                </td>

                                <td>
                                    <span class="pa-cell-title">
                                        {{ $clinic->contact_name ?? 'Not linked' }}
                                    </span>

                                    @if($clinic->customer_email)
                                        <span class="pa-cell-subtitle">
                                            {{ $clinic->customer_email }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <span @class([
                                        'pa-badge',
                                        'pa-badge-attention' =>
                                            $clinic->onboarding_status === 'pending_review',
                                    ])>
                                        {{ \Illuminate\Support\Str::headline($clinic->onboarding_status) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="pa-cell-title">
                                        {{ $clinic->plan_name ?? 'No plan' }}
                                    </span>

                                    <span class="pa-cell-subtitle">
                                        {{ $licenseLabel }}
                                    </span>
                                </td>

                                <td class="pa-nowrap">
                                    {{ \Illuminate\Support\Carbon::parse($clinic->created_at)->format('d M Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- DASHBOARD — Latest SaaS Payments --}}
    <section
        class="pa-panel"
        id="recent-payments"
        aria-labelledby="recent-payments-title"
    >
        <header class="pa-panel-header">
            <div>
                <h2 class="pa-panel-title" id="recent-payments-title">
                    Latest subscription payments
                </h2>

                <p class="pa-panel-description">
                    The six latest payments recorded for clinic licenses.
                </p>
            </div>
        </header>

        @if($recentPayments->isEmpty())
            <div class="pa-empty">
                <p class="pa-empty-title">
                    No subscription payments recorded
                </p>

                <p class="pa-empty-description">
                    Payments will appear here once they are recorded.
                </p>
            </div>
        @else
            <div class="pa-table-wrap">
                <table class="pa-table">
                    <thead>
                        <tr>
                            <th scope="col">Payment</th>
                            <th scope="col">Clinic</th>
                            <th scope="col">Method</th>
                            <th scope="col">Paid on</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="pa-amount">Amount</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($recentPayments as $payment)
                            <tr>
                                <td>
                                    <span class="pa-cell-title">
                                        #{{ $payment->license_payment_id }}
                                    </span>

                                    @if($payment->reference_no)
                                        <span class="pa-cell-subtitle">
                                            {{ $payment->reference_no }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <span class="pa-cell-title">
                                        {{ $payment->clinic_name ?? 'Clinic unavailable' }}
                                    </span>
                                </td>

                                <td>
                                    {{ \Illuminate\Support\Str::headline($payment->payment_method) }}
                                </td>

                                <td class="pa-nowrap">
                                    {{ \Illuminate\Support\Carbon::parse($payment->paid_at)->format('d M Y') }}
                                </td>

                                <td>
                                    <span @class([
                                        'pa-badge',
                                        'pa-badge-attention' =>
                                            $payment->status === 'pending_verification',
                                    ])>
                                        {{ \Illuminate\Support\Str::headline($payment->status) }}
                                    </span>
                                </td>

                                <td class="pa-amount">
                                    {{ $payment->currency }}
                                    {{ number_format((float) $payment->amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection