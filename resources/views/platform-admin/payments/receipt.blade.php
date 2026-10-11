<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $complimentary =
            $payment->status === 'complimentary'
            || $payment->payment_method === 'complimentary';

        $receiptNumber = 'LP-' . str_pad(
            (string) $payment->license_payment_id,
            6,
            '0',
            STR_PAD_LEFT
        );
    @endphp

    <title>{{ $receiptNumber }} | License Receipt</title>

    @vite(['resources/css/app.css'])

    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
    </style>
</head>

<body class="pa-license-receipt-page">
    <div class="pa-receipt-toolbar">
        <a href="{{ route('platform-admin.payments.show', $payment->license_payment_id) }}">
            Back to payment
        </a>

        <button type="button" onclick="window.print()">
            Print / Save PDF
        </button>
    </div>

    <main class="pa-license-receipt">
        <header class="pa-receipt-header">
            <div>
                <p class="pa-receipt-brand">Platform Console</p>
                <p class="pa-receipt-caption">Clinic software subscriptions</p>
            </div>

            <div class="pa-receipt-heading">
                <h1>
                    {{ $complimentary ? 'Complimentary Record' : 'Payment Receipt' }}
                </h1>
                <p>{{ $receiptNumber }}</p>
            </div>
        </header>

        <div class="pa-receipt-meta">
            <div>
                <span>Record date</span>
                <strong>
                    {{ \Illuminate\Support\Carbon::parse($payment->created_at)->format('d M Y') }}
                </strong>
            </div>
            <div>
                <span>Status</span>
                <strong>
                    {{ \Illuminate\Support\Str::headline($payment->status) }}
                </strong>
            </div>
        </div>

        <section class="pa-receipt-customer">
            <p class="pa-receipt-label">Issued to</p>
            <h2>{{ $payment->clinic_name }}</h2>

            @if($payment->contact_name)
                <p>{{ $payment->contact_name }}</p>
            @endif

            @if($payment->customer_email)
                <p>{{ $payment->customer_email }}</p>
            @endif

            @if($payment->clinic_phone)
                <p>{{ $payment->clinic_phone }}</p>
            @endif

            @if($payment->clinic_address)
                <p>{{ $payment->clinic_address }}</p>
            @endif
        </section>

        <table class="pa-receipt-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="pa-receipt-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $payment->plan_name }} subscription</strong>
                        <span>License #{{ $payment->clinic_license_id }}</span>
                        <span>
                            Starts:
                            {{ \Illuminate\Support\Carbon::parse($payment->license_starts_at)->format('d M Y') }}
                        </span>
                        <span>
                            Expires:
                            {{ $payment->license_ends_at
                                ? \Illuminate\Support\Carbon::parse($payment->license_ends_at)->format('d M Y')
                                : 'No expiry — Lifetime' }}
                        </span>
                    </td>
                    <td class="pa-receipt-right">
                        {{ $payment->currency }}
                        {{ number_format((float) $payment->amount, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="pa-receipt-total">
            <span>{{ $complimentary ? 'Amount received — Complimentary' : 'Amount received' }}</span>
            <strong>
                {{ $payment->currency }}
                {{ number_format((float) $payment->amount, 2) }}
            </strong>
        </div>

        <dl class="pa-receipt-payment">
            <div>
                <dt>Payment method</dt>
                <dd>{{ \Illuminate\Support\Str::headline($payment->payment_method) }}</dd>
            </div>

            <div>
                <dt>{{ $complimentary ? 'Grant date' : 'Payment date' }}</dt>
                <dd>
                    {{ \Illuminate\Support\Carbon::parse($payment->paid_at)->format('d M Y') }}
                </dd>
            </div>

            <div>
                <dt>Reference</dt>
                <dd>{{ $payment->reference_no ?? '—' }}</dd>
            </div>

            <div>
                <dt>Recorded by</dt>
                <dd>{{ $payment->recorded_by_name ?? '—' }}</dd>
            </div>

            @if($payment->verified_by_name)
                <div>
                    <dt>Verified by</dt>
                    <dd>{{ $payment->verified_by_name }}</dd>
                </div>
            @endif
        </dl>

        @if($payment->notes)
            <section class="pa-receipt-note">
                <p class="pa-receipt-label">Notes</p>
                <p>{{ $payment->notes }}</p>
            </section>
        @endif

        <footer class="pa-receipt-footer">
            {{ $complimentary
                ? 'Complimentary license granted. No payment was received.'
                : 'Payment received and verified for the subscription shown above.' }}
        </footer>
    </main>
</body>
</html>