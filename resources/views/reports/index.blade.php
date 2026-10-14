@extends('layouts.app')

@section('title', 'Reports — ' . $clinic->name)

@section('content')
@php
    $money = fn ($value, $code) =>
        ($code ? strtoupper($code) : 'Currency not recorded')
        . ' ' . number_format((float) $value, $decimalPlaces);

    $methodLabel = fn ($value) => match (strtolower(trim($value ?? ''))) {
        'bank', 'bank transfer', 'bank_transfer' => 'Bank transfer',
        'cash' => 'Cash',
        'card' => 'Card',
        default => ucfirst($value ?? ''),
    };
@endphp

<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <header class="page-header no-print">
            <div>
                <p class="dashboard-date">Clinic performance</p>
                <h1>Reports</h1>
                <p class="page-subtitle">
                    Patient and order collections, expenses and activity.
                </p>
            </div>

            <button type="button" class="btn btn-primary" data-print-page>
                <i class="bi bi-printer app-icon" aria-hidden="true"></i>
                Print / Save PDF
            </button>
        </header>

        @if ($errors->any())
            <div class="alert alert-danger no-print" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="orders-panel mb-4 no-print">
            <form
                method="GET"
                action="{{ route('reports.index') }}"
                class="orders-filter-grid"
            >
                <label class="form-field">
                    <span class="form-label">Report type</span>
                    <select name="report_type" class="form-select">
                        @foreach ([
                            'summary' => 'Summary',
                            'payments' => 'Collections',
                            'expenses' => 'Expenses',
                        ] as $value => $label)
                            <option value="{{ $value }}" @selected($reportType === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="form-field">
                    <span class="form-label">From date</span>
                    <input
                        type="date"
                        name="from"
                        class="form-control"
                        value="{{ $from->format('Y-m-d') }}"
                        required
                    >
                </label>

                <label class="form-field">
                    <span class="form-label">To date</span>
                    <input
                        type="date"
                        name="to"
                        class="form-control"
                        value="{{ $to->format('Y-m-d') }}"
                        required
                    >
                </label>

                <div class="orders-filter-actions">
                    <button type="submit" class="btn btn-primary">Apply</button>
                    <a href="{{ route('reports.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
        </section>

        @if ($unconfirmedCurrencyCount > 0)
            <div class="alert alert-warning" role="status">
                {{ $unconfirmedCurrencyCount }} records have no confirmed currency.
                They appear below but are excluded from financial totals.
            </div>
        @endif

        <section class="orders-panel mb-4">
            <h2 class="h5">{{ $clinic->name }}</h2>
            <p class="text-muted">
                {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}
            </p>

            <p class="text-muted small">
                Summary cards show {{ $currency }} only.
            </p>

            <div class="payment-summary-grid">
                <article class="payment-summary-card">
                    <span>Patient collections</span>
                    <strong>{{ $money($patientIncome, $currency) }}</strong>
                    <small>Received patient payments</small>
                </article>

                <article class="payment-summary-card">
                    <span>Nuskha order collections</span>
                    <strong>{{ $money($orderIncome, $currency) }}</strong>
                    <small>Received order payments</small>
                </article>

                <article class="payment-summary-card">
                    <span>Total collections</span>
                    <strong>{{ $money($totalIncome, $currency) }}</strong>
                    <small>Patient + order payments</small>
                </article>

                <article class="payment-summary-card">
                    <span>Expenses</span>
                    <strong>{{ $money($totalExpenses, $currency) }}</strong>
                    <small>Recorded expenses in {{ $currency }}</small>
                </article>
            </div>

            <div class="balance-summary-grid">
                <div>
                    <span>Collections minus expenses</span>
                    <strong>{{ $money($netIncome, $currency) }}</strong>
                </div>
                <div>
                    <span>New patients</span>
                    <strong>{{ number_format($totalPatients) }}</strong>
                </div>
                <div>
                    <span>Visits</span>
                    <strong>{{ number_format($totalVisits) }}</strong>
                </div>
            </div>

            <small class="text-muted d-block mt-3">
                Collections minus expenses is cash flow, not accounting profit.
                Unpaid bills and orders are not counted as collections.
            </small>

            <h3 class="h6 mt-4">Totals by original currency</h3>

            <div class="table-responsive">
                <table class="table orders-table align-middle">
                    <thead>
                        <tr>
                            <th>Currency</th>
                            <th class="text-end">Received</th>
                            <th class="text-end">Expenses</th>
                            <th class="text-end">Net cash flow</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($currencySummary as $summary)
                            <tr>
                                <td>{{ $summary['currency'] }}</td>
                                <td class="text-end">
                                    {{ $money($summary['received'], $summary['currency']) }}
                                </td>
                                <td class="text-end">
                                    {{ $money($summary['expenses'], $summary['currency']) }}
                                </td>
                                <td class="text-end">
                                    {{ $money($summary['net'], $summary['currency']) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <small class="text-muted">
                Currencies are reported separately without conversion.
            </small>
        </section>

        @if (in_array($reportType, ['summary', 'payments'], true))
            <section class="orders-panel mb-4">
                <h2 class="h5 mb-3">Received collections</h2>

                <div class="table-responsive">
                    <table class="table orders-table align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Source</th>
                                <th>Customer/patient</th>
                                <th>Reference</th>
                                <th>Method</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($collections as $record)
                                <tr>
                                    <td>
                                        {{ \Carbon\Carbon::parse($record['date'])->format('d M Y, h:i A') }}
                                    </td>
                                    <td>{{ $record['source'] }}</td>
                                    <td>{{ $record['name'] }}</td>
                                    <td>
                                        <a href="{{ $record['url'] }}">
                                            {{ $record['reference'] }}
                                        </a>
                                    </td>
                                    <td>{{ $methodLabel($record['method']) }}</td>
                                    <td class="text-end">
                                        {{ $money($record['amount'], $record['currency']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">No collections in this date range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if (in_array($reportType, ['summary', 'expenses'], true))
            <section class="orders-panel">
                <h2 class="h5 mb-3">Expense records</h2>

                <div class="table-responsive">
                    <table class="table orders-table align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($expenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('expenses.show', $expense) }}">
                                            {{ $expense->description }}
                                        </a>
                                    </td>
                                    <td>{{ $expense->category }}</td>
                                    <td class="text-end">
                                        {{ $money($expense->amount, $expense->currency) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">No expenses in this date range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </main>
</div>
@endsection