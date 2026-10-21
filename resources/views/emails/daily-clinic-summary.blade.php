<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Daily clinic summary') }}</title>
</head>

<body style="margin:0;padding:0;background:#f4f7fa;font-family:Arial,sans-serif;color:#203b41;">
    <table
        role="presentation"
        width="100%"
        cellpadding="0"
        cellspacing="0"
        style="background:#f4f7fa;"
    >
        <tr>
            <td align="center" style="padding:28px 12px;">
                <table
                    role="presentation"
                    width="600"
                    cellpadding="0"
                    cellspacing="0"
                    style="width:100%;max-width:600px;background:#ffffff;border:1px solid #dcebe6;"
                >
                    {{-- Email — Clinic Header --}}
                    <tr>
                        <td style="padding:28px;border-top:5px solid #0b7774;">
                            <p style="margin:0 0 10px;color:#0b7774;font-size:11px;letter-spacing:1px;text-transform:uppercase;">
                                {{ __('Daily clinic summary') }}
                            </p>

                            <h1 style="margin:0 0 8px;font-size:24px;font-weight:600;">
                                {{ $summary['clinic_name'] }}
                            </h1>

                            <p style="margin:0;color:#617773;font-size:13px;">
                                {{ $summary['date_label'] }} · UAE time
                            </p>
                        </td>
                    </tr>

                    {{-- Email — Daily Activity --}}
                    <tr>
                        <td style="padding:0 28px 24px;">
                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                style="width:100%;border-collapse:collapse;font-size:14px;"
                            >
                                <tr>
                                    <td style="padding:14px 12px;background:#f7faf8;border-bottom:1px solid #dcebe6;">
                                        {{ __('Patient visits') }}
                                    </td>

                                    <td align="right" style="padding:14px 12px;background:#f7faf8;border-bottom:1px solid #dcebe6;font-weight:600;">
                                        {{ number_format($summary['visits']) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:14px 12px;border-bottom:1px solid #dcebe6;">
                                        {{ __('Payments received') }}
                                    </td>

                                    <td align="right" style="padding:14px 12px;border-bottom:1px solid #dcebe6;font-weight:600;">
                                        {{ $summary['currency'] }}
                                        {{ number_format(
                                            $summary['received'],
                                            $summary['decimal_places']
                                        ) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:14px 12px;border-bottom:1px solid #dcebe6;">
                                        {{ __('Expenses recorded') }}
                                    </td>

                                    <td align="right" style="padding:14px 12px;border-bottom:1px solid #dcebe6;font-weight:600;">
                                        {{ $summary['currency'] }}
                                        {{ number_format(
                                            $summary['expenses'],
                                            $summary['decimal_places']
                                        ) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:16px 12px;background:#0b7774;color:#ffffff;">
                                        {{ __('Collections minus expenses') }}
                                    </td>

                                    <td align="right" style="padding:16px 12px;background:#0b7774;color:#ffffff;font-weight:600;">
                                        {{ $summary['currency'] }}
                                        {{ number_format(
                                            $summary['net'],
                                            $summary['decimal_places']
                                        ) }}
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:14px 0 0;color:#617773;font-size:12px;line-height:1.6;">
                                {{ __('This summary uses the visit, payment and expense dates recorded for the selected day. Collections minus expenses is a cash-flow figure, not an accounting profit calculation.') }}
                            </p>
                        </td>
                    </tr>

                    {{-- Email — Dashboard Link --}}
                    <tr>
                        <td style="padding:0 28px 28px;">
                            <a
                                href="{{ $summary['dashboard_url'] }}"
                                style="display:inline-block;padding:12px 18px;background:#0b7774;color:#ffffff;text-decoration:none;border-radius:6px;font-size:13px;"
                            >
                                {{ __('Open clinic dashboard') }}
                            </a>
                        </td>
                    </tr>

                    {{-- Email — Footer --}}
                    <tr>
                        <td style="padding:18px 28px;border-top:1px solid #dcebe6;color:#617773;font-size:11px;line-height:1.6;">
                            {{ __('You receive this email because Daily clinic summary is enabled in your clinic’s notification preferences.') }}

                            <br>

                            <a
                                href="{{ $summary['preferences_url'] }}"
                                style="color:#0b7774;"
                            >
                                {{ __('Manage notification preferences') }}
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>