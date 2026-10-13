@extends('layouts.app')

@section('title', 'Reports — ' . $clinic->name)

@section('content')
@php
    $money = fn ($value, $code = null) =>
        ($code ?? $currency) . ' '
        . number_format((float) $value, $decimalPlaces);
@endphp

<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <header class="page-header no-print">
            <div>
                <p class="dashboard-date">Clinic performance</p>
                <h1>Reports</h1>
                <p class="page-subtitle">
                    Patient and nuskha-order collections, expenses and activity.
                </p>
            </div>

            <button type="button" class="btn btn-primary" data-print-page>
                <i class="bi bi-printer app-icon" aria-hidden="true"></i>
                Print / Save PDF
            </button>
        </header>

        @if($errors->any())
            <div class="alert alert-danger no-print">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Reports — Actual Date and Type Filters --}}
        <section class="orders-panel mb-4 no-print">
            <form method="GET" action="{{ route('reports.index') }}" class="orders-filter-grid">
                <label class="form-field">
                    <span class="form-label">Report type</span>
                    <select name="report_type" class="form-select">
                        @foreach([
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

        <section class="orders-panel mb-4">
            <h2 class="h5">{{ $clinic->name }}</h2>
            <p class="text-muted">
                {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}
            </p>

            <div class="payment-summary-grid">
                <article class="payment-summary-card">
                    <span>Patient collections</span>
                    <strong>{{ $money($patientIncome) }}</strong>
                    <small>Received patient payments</small>
                </article>

                <article class="payment-summary-card">
                    <span>Nuskha order collections</span>
                    <strong>{{ $money($orderIncome) }}</strong>
                    <small>Received in {{ $currency }}</small>
                </article>

                <article class="payment-summary-card">
                    <span>Total collections</span>
                    <strong>{{ $money($totalIncome) }}</strong>
                    <small>Patient + order payments</small>
                </article>

                <article class="payment-summary-card">
                    <span>Expenses</span>
                    <strong>{{ $money($totalExpenses) }}</strong>
                    <small>Recorded clinic expenses</small>
                </article>
            </div>

            <div class="balance-summary-grid">
                <div>
                    <span>Collections minus expenses</span>
                    <strong>{{ $money($netIncome) }}</strong>
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
                Unpaid order totals are not counted as collections.
            </small>

            @if($currencyTotals->keys()->contains(fn ($code) => $code !== $currency))
                <div class="mt-3">
                    <strong>Collections by currency</strong>
                    @foreach($currencyTotals as $code => $amount)
                        <p class="mb-1">{{ $money($amount, $code) }}</p>
                    @endforeach
                    <small class="text-muted">
                        Different currencies are shown separately, without conversion.
                    </small>
                </div>
            @endif
        </section>

        {{-- Reports — Patient and Order Collections --}}
        @if(in_array($reportType, ['summary', 'payments'], true))
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
                            @forelse($collections as $record)
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
                                    <td>
                                        {{ $record['method'] === 'bank'
                                            ? 'Bank transfer'
                                            : ucfirst($record['method']) }}
                                    </td>
                                    <td class="text-end text-nowrap">
                                        {{ $money($record['amount'], $record['currency']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6">No collections in this date range.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        {{-- Reports — Expenses --}}
        @if(in_array($reportType, ['summary', 'expenses'], true))
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
                            @forelse($expenses as $expense)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('expenses.show', $expense) }}">
                                            {{ $expense->description }}
                                        </a>
                                    </td>
                                    <td>{{ $expense->category }}</td>
                                    <td class="text-end text-nowrap">{{ $money($expense->amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4">No expenses in this date range.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </main>
</div>
@endsection