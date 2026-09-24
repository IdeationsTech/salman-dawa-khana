@extends('layouts.app')

@section('title', 'Payments — Salman Dawa Khana')

@section('content')
<div class="app-shell">
    @include('payments._sidebar')

    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">Clinic finances</p>
                <h1>Payments</h1>

                <p class="page-subtitle">
                    Track patient payments and outstanding bills.
                </p>
            </div>

            <a href="{{ route('payments.create') }}" class="btn btn-primary">
                + Record Payment
            </a>
        </header>

        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- PAYMENTS — Clinic Summary --}}
        <section class="payment-summary-grid">
            <article class="payment-summary-card">
                <span>Today's receipts</span>
                <strong>{{ $currency }} {{ number_format($todayReceived, 2) }}</strong>
                <small class="stat-success">Payments received today</small>
            </article>

            <article class="payment-summary-card">
                <span>This month's receipts</span>
                <strong>{{ $currency }} {{ number_format($monthReceived, 2) }}</strong>
                <small>{{ $monthLabel }}</small>
            </article>

            <article class="payment-summary-card">
                <span>Total received</span>
                <strong>{{ $currency }} {{ number_format($totalReceived, 2) }}</strong>
                <small>All recorded payments</small>
            </article>

            <article class="payment-summary-card">
                <span>Outstanding bills</span>
                <strong>
                    {{ $currency }} {{ number_format($outstandingBalance, 2) }}
                </strong>
                <small class="stat-warning">Excludes unallocated payments</small>
            </article>
        </section>

        <p class="text-muted small">
            Unallocated payments:
            <strong>
                {{ $currency }} {{ number_format($unallocatedReceived, 2) }}
            </strong>.
            Included in total received, but not applied to bills.
            Summary cards show clinic totals regardless of the filters below.
        </p>

        {{-- PAYMENTS — Search and Filters --}}
        <section class="payments-panel">
            <div class="payments-toolbar">
                <div>
                    <h2>Payment records</h2>
                    <p>Search all recorded payments.</p>
                </div>

                <div class="payments-toolbar-actions">
                    <select
                        name="method"
                        class="form-select"
                        form="payment-filter-form"
                        aria-label="Payment method"
                    >
                        <option value="all" @selected($method === 'all')>
                            All methods
                        </option>

                        <option value="cash" @selected($method === 'cash')>
                            Cash
                        </option>

                        <option value="bank" @selected($method === 'bank')>
                            Bank transfer
                        </option>

                        <option value="card" @selected($method === 'card')>
                            Card
                        </option>
                    </select>

                    <select
                        name="date_range"
                        class="form-select"
                        form="payment-filter-form"
                        aria-label="Payment date range"
                    >
                        <option value="all time" @selected($dateRange === 'all time')>
                            All time
                        </option>

                        <option value="today" @selected($dateRange === 'today')>
                            Today
                        </option>

                        <option value="this week" @selected($dateRange === 'this week')>
                            This week
                        </option>

                        <option value="this month" @selected($dateRange === 'this month')>
                            This month
                        </option>
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
                    <span aria-hidden="true">⌕</span>

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

            {{-- PAYMENTS — Records Table --}}
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
                        @forelse($payments as $payment)
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

                                    @if(!$payment->bill_id)
                                        <small>Unallocated</small>
                                    @endif
                                </td>

                                <td class="payment-amount">
                                    {{ $currency }}
                                    {{ number_format($payment->amount, 2) }}
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
                                    No payments match your filters.
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
                    of {{ $payments->total() }} payments
                </span>

                <div>
                    {{ $payments->links() }}
                </div>
            </div>
        </section>
    </main>
</div>
@endsection