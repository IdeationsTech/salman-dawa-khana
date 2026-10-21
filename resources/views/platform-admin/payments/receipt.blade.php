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

    <title>{{ $receiptNumber }}</title>

    <style>
        /*
        |--------------------------------------------------------------------------
        | RECEIPT — Independent Document Styles
        |--------------------------------------------------------------------------
        */

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 28px 16px;
            background: #e9edf2;
            color: #202632;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            line-height: 1.6;
            color-scheme: light;
        }

        .receipt-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            max-width: 794px;
            margin: 0 auto 18px;
        }

        .receipt-toolbar a {
            color: #9a3412;
            font-weight: 600;
            text-decoration: none;
        }

        .receipt-toolbar button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 18px;
            border: 0;
            border-radius: 8px;
            background: #c2410c;
            color: #ffffff;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }

        .receipt-toolbar svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .receipt-sheet {
            max-width: 794px;
            margin: 0 auto;
            padding: 42px;
            border-top: 6px solid #c2410c;
            background: #ffffff;
            box-shadow: 0 8px 30px rgb(17 19 24 / 8%);
        }

        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            padding-bottom: 26px;
            border-bottom: 1px solid #dce1e7;
        }

        .brand-name {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .brand-caption {
            margin: 5px 0 0;
            color: #667085;
            font-size: 12px;
        }

        .receipt-heading {
            text-align: right;
        }

        .receipt-heading h1 {
            margin: 0;
            color: #9a3412;
            font-size: 24px;
            font-weight: 700;
            line-height: 1.25;
            letter-spacing: -0.5px;
        }

        .receipt-number {
            margin: 7px 0 0;
            color: #667085;
            font-size: 13px;
            letter-spacing: 1px;
        }

        .receipt-meta {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            margin: 22px 0 30px;
        }

        .meta-label,
        .section-label {
            display: block;
            margin: 0 0 5px;
            color: #667085;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .meta-value {
            font-weight: 600;
        }

        .status-label {
            display: inline-block;
            padding: 4px 11px;
            border: 1px solid #dce1e7;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }

        .customer-section {
            margin-bottom: 28px;
            overflow-wrap: anywhere;
        }

        .customer-section h2 {
            margin: 0 0 7px;
            font-size: 19px;
            font-weight: 700;
        }

        .customer-section p:not(.section-label) {
            margin: 2px 0;
            color: #475467;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .receipt-table th {
            padding: 12px 14px;
            border-bottom: 2px solid #c2410c;
            background: #f4f5f7;
            color: #475467;
            font-size: 10px;
            letter-spacing: 0.7px;
            text-align: left;
            text-transform: uppercase;
        }

        .receipt-table td {
            padding: 19px 14px;
            border-bottom: 1px solid #dce1e7;
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        .receipt-table th:last-child,
        .receipt-table td:last-child {
            width: 155px;
            text-align: right;
        }

        .subscription-name {
            display: block;
            margin-bottom: 7px;
            font-size: 14px;
            font-weight: 700;
        }

        .subscription-detail {
            display: block;
            margin-top: 3px;
            color: #667085;
            font-size: 12px;
        }

        .item-amount {
            font-weight: 600;
            white-space: nowrap;
        }

        .receipt-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin: 18px 0 28px;
            padding: 18px 16px;
            border: 1px solid #e7d4c9;
            border-radius: 8px;
            background: #fff8f3;
        }

        .receipt-total span {
            color: #475467;
            font-size: 12px;
            font-weight: 600;
        }

        .receipt-total strong {
            color: #9a3412;
            font-size: 25px;
            line-height: 1.2;
            white-space: nowrap;
        }

        .payment-details {
            margin: 0;
        }

        .payment-details > div {
            display: grid;
            grid-template-columns: 145px minmax(0, 1fr);
            gap: 18px;
            padding: 7px 0;
        }

        .payment-details dt {
            color: #667085;
        }

        .payment-details dd {
            margin: 0;
            font-weight: 500;
            overflow-wrap: anywhere;
        }

        .receipt-notes {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid #dce1e7;
        }

        .receipt-notes p:last-child {
            margin: 0;
            color: #475467;
            white-space: pre-line;
            overflow-wrap: anywhere;
        }

        .receipt-footer {
            margin-top: 32px;
            padding-top: 18px;
            border-top: 1px solid #dce1e7;
            color: #667085;
            font-size: 11px;
        }

        @media screen and (max-width: 600px) {
            .receipt-sheet {
                padding: 24px;
            }

            .receipt-header {
                flex-direction: column;
            }

            .receipt-heading {
                text-align: left;
            }

            .receipt-total {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | RECEIPT — A4 Print Layout
        |--------------------------------------------------------------------------
        */

        @page {
            size: A4 portrait;
            margin: 14mm;
        }

        @media print {
            html,
            body {
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .receipt-toolbar {
                display: none !important;
            }

            .receipt-sheet {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 8mm 6mm;
                box-shadow: none;
            }

            .receipt-header,
            .receipt-table tr,
            .receipt-total,
            .payment-details > div {
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="receipt-toolbar">
        <a href="{{ route('platform-admin.payments.show', $payment->license_payment_id) }}">
            ← Back to payment
        </a>

        <button type="button" onclick="window.print()">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 9V3h12v6M6 17H4a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2"/>
                <path d="M6 14h12v7H6zM18 12h.01"/>
            </svg>
            {{ __('Print / Save PDF') }}
        </button>
    </div>

    <main class="receipt-sheet">
        <header class="receipt-header">
            <div>
                <p class="brand-name">{{ __('Platform Console') }}</p>
                <p class="brand-caption">
                    {{ __('Clinic software subscriptions') }}
                </p>
            </div>

            <div class="receipt-heading">
                <h1>
                    {{ $complimentary
                        ? 'Complimentary Record'
                        : 'Payment Receipt' }}
                </h1>

                <p class="receipt-number">
                    {{ $receiptNumber }}
                </p>
            </div>
        </header>

        <div class="receipt-meta">
            <div>
                <span class="meta-label">{{ __('Record date') }}</span>
                <span class="meta-value">
                    {{ \Illuminate\Support\Carbon::parse($payment->created_at)->format('d M Y') }}
                </span>
            </div>

            <div>
                <span class="meta-label">{{ __('Status') }}</span>
                <span class="status-label">
                    {{ \Illuminate\Support\Str::headline($payment->status) }}
                </span>
            </div>
        </div>

        <section class="customer-section">
            <p class="section-label">{{ __('Issued to') }}</p>
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

        <table class="receipt-table">
            <thead>
                <tr>
                    <th>{{ __('Subscription details') }}</th>
                    <th>{{ __('Amount') }}</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>
                        <strong class="subscription-name">
                            {{ $payment->plan_name }} {{ __('subscription') }}
                        </strong>

                        <span class="subscription-detail">
                            {{ __('License #') }}{{ $payment->clinic_license_id }}
                        </span>

                        <span class="subscription-detail">
                            {{ __('Starts') }}:
                            {{ \Illuminate\Support\Carbon::parse($payment->license_starts_at)->format('d M Y') }}
                        </span>

                        <span class="subscription-detail">
                            {{ __('Expires') }}:
                            {{ $payment->license_ends_at
                                ? \Illuminate\Support\Carbon::parse($payment->license_ends_at)->format('d M Y')
                                : 'No expiry - Lifetime' }}
                        </span>
                    </td>

                    <td class="item-amount">
                        {{ $payment->currency }}
                        {{ number_format((float) $payment->amount, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="receipt-total">
            <span>
                {{ $complimentary
                    ? 'Complimentary - Amount received'
                    : __('Amount received') }}
            </span>

            <strong>
                {{ $payment->currency }}
                {{ number_format((float) $payment->amount, 2) }}
            </strong>
        </div>

        <dl class="payment-details">
            <div>
                <dt>{{ __('Payment method') }}</dt>
                <dd>
                    {{ \Illuminate\Support\Str::headline($payment->payment_method) }}
                </dd>
            </div>

            <div>
                <dt>{{ $complimentary ? 'Grant date' : __('Payment date') }}</dt>
                <dd>
                    {{ \Illuminate\Support\Carbon::parse($payment->paid_at)->format('d M Y') }}
                </dd>
            </div>

            <div>
                <dt>{{ __('Reference') }}</dt>
                <dd>{{ $payment->reference_no ?? '—' }}</dd>
            </div>

            <div>
                <dt>{{ __('Recorded by') }}</dt>
                <dd>{{ $payment->recorded_by_name ?? '—' }}</dd>
            </div>

            @if($payment->verified_by_name)
                <div>
                    <dt>{{ __('Verified by') }}</dt>
                    <dd>{{ $payment->verified_by_name }}</dd>
                </div>
            @endif
        </dl>

        @if($payment->notes)
            <section class="receipt-notes">
                <p class="section-label">{{ __('Notes') }}</p>
                <p>{{ $payment->notes }}</p>
            </section>
        @endif

        <footer class="receipt-footer">
            {{ $complimentary
                ? 'Complimentary license granted. No payment was received.'
                : 'Payment received and verified for the subscription shown above.' }}
        </footer>
    </main>

    <script>
        window.addEventListener('load', async () => {
            if (document.fonts) {
                await document.fonts.ready;
            }

            window.print();
        }, { once: true });
    </script>
</body>
</html>