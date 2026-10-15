@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">Clinic finances</p>
                <h1>Payments</h1>
                <p class="page-subtitle">
                    Patient receipts, nuskha order collections and refunds.
                </p>
            </div>

            <a href="{{ route('payments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle" aria-hidden="true"></i>
                Record payment
            </a>
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($unconfirmedCurrencyCount > 0)
            <div class="alert alert-warning" role="status">
                {{ $unconfirmedCurrencyCount }} patient payments have no
                confirmed currency and are excluded from summary totals.
            </div>
        @endif

        <section class="payment-summary-grid">
            <article class="payment-summary-card">
                <span>Today's patient receipts</span>
                <strong>
                    {{ $currency }}
                    {{ number_format((float) $todaySummary['patient'], $moneyDecimals) }}
                </strong>
                <small>Patient payments received today</small>
            </article>

            <article class="payment-summary-card">
                <span>Today's order receipts</span>
                <strong>
                    {{ $currency }}
                    {{ number_format((float) $todaySummary['orders'], $moneyDecimals) }}
                </strong>
                <small>Nuskha collections before refunds</small>
            </article>

            <article class="payment-summary-card">
                <span>Today's refunds</span>
                <strong>
                    {{ $currency }}
                    {{ number_format((float) $todaySummary['refunds'], $moneyDecimals) }}
                </strong>
                <small class="stat-warning">Deducted from received income</small>
            </article>

            <article class="payment-summary-card">
                <span>Today's net receipts</span>
                <strong>
                    {{ $currency }}
                    {{ number_format((float) $todaySummary['net'], $moneyDecimals) }}
                </strong>
                <small class="stat-success">
                    Patient + order receipts − refunds
                </small>
            </article>
        </section>

        <section class="payment-summary-grid">
            <article class="payment-summary-card">
                <span>This month's net receipts</span>
                <strong>
                    {{ $currency }}
                    {{ number_format((float) $monthSummary['net'], $moneyDecimals) }}
                </strong>
                <small>{{ $monthLabel }} · After refunds</small>
            </article>

            <article class="payment-summary-card">
                <span>All-time net receipts</span>
                <strong>
                    {{ $currency }}
                    {{ number_format((float) $allTimeSummary['net'], $moneyDecimals) }}
                </strong>
                <small>Patient and order receipts after refunds</small>
            </article>

            <article class="payment-summary-card">
                <span>Outstanding patient bills</span>
                <strong>
                    {{ $currency }}
                    {{ number_format((float) $outstandingBalance, $moneyDecimals) }}
                </strong>
                <small class="stat-warning">
                    Remaining patient bill balances
                </small>
            </article>

            <article class="payment-summary-card">
                <span>Unallocated patient receipts</span>
                <strong>
                    {{ $currency }}
                    {{ number_format((float) $unallocatedReceived, $moneyDecimals) }}
                </strong>
                <small>Included in receipts; not assigned to bills</small>
            </article>
        </section>

        <p class="text-muted small">
            Summary cards show this clinic's {{ $currency }} records.
            These are received amounts after refunds, before expenses.
            Filters below apply to the patient payment table.
            <a href="{{ route('orders.index') }}">View nuskha orders</a>.
        </p>

        <section class="payments-panel">
            <div class="payments-toolbar">
                <div>
                    <h2>Patient payment records</h2>
                    <p>Records retain their original currencies.</p>
                </div>

                <div class="payments-toolbar-actions">
                    <select
                        name="method"
                        class="form-select"
                        form="payment-filter-form"
                        aria-label="Payment method"
                    >
                        @foreach ([
                            'all' => 'All methods',
                            'cash' => 'Cash',
                            'bank' => 'Bank transfer',
                            'card' => 'Card',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected($method === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <select
                        name="date_range"
                        class="form-select"
                        form="payment-filter-form"
                        aria-label="Payment date range"
                    >
                        @foreach ([
                            'all time' => 'All time',
                            'today' => 'Today',
                            'this week' => 'This week',
                            'this month' => 'This month',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected($dateRange === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <form
                method="GET"
                action="{{ route('payments.index') }}"
                id="payment-filter-form"
                data-server-payment-filters
            >
                <div class="payment-search">
                    <span>
                        <i class="bi bi-search" aria-hidden="true"></i>
                    </span>

                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        maxlength="160"
                        aria-label="Search payments"
                        placeholder="Patient name, phone, patient code, payment ID or reference"
                    >

                    <button type="submit" class="btn btn-primary btn-sm">
                        Search
                    </button>

                    <a
                        href="{{ route('payments.index') }}"
                        class="btn btn-light btn-sm"
                    >
                        Clear
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table payments-table align-middle">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Patient</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Reference</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($payments as $payment)
                            @php
                                $rowMethod = strtolower(trim($payment->method));

                                $isBank = in_array(
                                    $rowMethod,
                                    ['bank', 'bank transfer', 'bank_transfer'],
                                    true
                                );

                                $methodLabel = $isBank
                                    ? 'Bank transfer'
                                    : ucfirst($rowMethod);

                                $methodClass = $isBank
                                    ? 'bank-method'
                                    : ($rowMethod === 'cash'
                                        ? 'cash-method'
                                        : 'card-method');
                            @endphp

                            <tr>
                                <td>PAY-{{ $payment->payment_id }}</td>

                                <td>
                                    <strong>
                                        {{ $payment->patient?->full_name ?? 'Patient unavailable' }}
                                    </strong>
                                    <small>
                                        {{ $payment->patient?->patient_code ?? '—' }}
                                    </small>
                                    <small>
                                        {{ $payment->patient?->phone ?? '—' }}
                                    </small>
                                </td>

                                <td>
                                    {{ $payment->payment_date->format('d M Y') }}
                                    <small>
                                        {{ $payment->payment_date->format('h:i A') }}
                                    </small>
                                </td>

                                <td>
                                    <span class="payment-method {{ $methodClass }}">
                                        {{ $methodLabel }}
                                    </span>

                                    @if (!$payment->bill_id)
                                        <small>Unallocated</small>
                                    @endif
                                </td>

                                <td class="payment-amount">
                                    @if ($payment->currency)
                                        {{ strtoupper($payment->currency) }}
                                        {{ number_format((float) $payment->amount, $moneyDecimals) }}
                                    @else
                                        {{ number_format((float) $payment->amount, $moneyDecimals) }}
                                        <small class="d-block text-muted">
                                            Currency not recorded
                                        </small>
                                    @endif
                                </td>

                                <td>{{ $payment->reference_no ?: '—' }}</td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('payments.show', $payment) }}"
                                        class="table-action"
                                    >
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    No patient payments match your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="patients-pagination">
                <span>
                    Showing {{ $payments->firstItem() ?? 0 }}
                    to {{ $payments->lastItem() ?? 0 }}
                    of {{ $payments->total() }} patient payments
                </span>

                <div>{{ $payments->links() }}</div>
            </div>
        </section>
    </main>
</div>
@endsection