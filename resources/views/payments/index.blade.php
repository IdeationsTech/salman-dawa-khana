@extends('layouts.app')

@section('title', 'Payments — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">+</div>
            <span>Salman Dawa Khana</span>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="sidebar-link">
                <span>▦</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('patients.index') }}" class="sidebar-link">
                <span>♙</span>
                <span>Patients</span>
            </a>

            <a href="{{ route('visits.index') }}" class="sidebar-link">
                <span>▣</span>
                <span>Visits</span>
            </a>

            <a href="{{ route('prescriptions.index') }}" class="sidebar-link">
                <span>✎</span>
                <span>Prescriptions</span>
            </a>

            <a href="{{ route('payments.index') }}" class="sidebar-link active">
                <span>₨</span>
                <span>Payments</span>
            </a>

            <a href="{{ route('expenses.index') }}" class="sidebar-link">
                <span>◈</span>
                <span>Expenses</span>
            </a>

            <div class="sidebar-divider"></div>

            <a href="{{ route('reports.index') }}" class="sidebar-link">
                <span>◌</span>
                <span>Reports</span>
            </a>

            <a href="{{ route('settings.index') }}" class="sidebar-link">
                <span>⚙</span>
                <span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    <main class="dashboard-main">

        <header class="page-header">
            <div>
                <p class="dashboard-date">Clinic finances</p>

                <h1>Payments</h1>

                <p class="page-subtitle">
                    Track patient payments, income and outstanding balances.
                </p>
            </div>

            <a href="{{ route('payments.create') }}" class="btn btn-primary">
                + Record Payment
            </a>
        </header>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <section class="payment-summary-grid">
            <article class="payment-summary-card">
                <span>Today's income</span>

                <strong>
                    PKR {{ number_format($todayReceived ?? 0, 2) }}
                </strong>

                <small class="stat-success">
                    Payments received today
                </small>
            </article>

            <article class="payment-summary-card">
                <span>This month's income</span>

                <strong>
                    PKR {{ number_format($monthReceived ?? 0, 2) }}
                </strong>

                <small>
                    {{ now()->format('F Y') }}
                </small>
            </article>

            <article class="payment-summary-card">
                <span>Total received</span>

                <strong>
                    PKR {{ number_format($totalReceived ?? 0, 2) }}
                </strong>

                <small>
                    All clinic payments
                </small>
            </article>

            <article class="payment-summary-card">
                <span>Outstanding dues</span>

                <strong>
                    PKR {{ number_format($outstandingBalance ?? 0, 2) }}
                </strong>

                <small class="stat-warning">
                    Patient balances
                </small>
            </article>
        </section>

        <section class="payments-panel">

            <div class="payments-toolbar">
                <div>
                    <h2>Payment records</h2>

                    <p>
                        Recent patient payments and transactions.
                    </p>
                </div>

                <div class="payments-toolbar-actions">

                    <select
                        name="method"
                        class="form-select"
                        form="payment-filter-form"
                    >
                        <option value="all">
                            All methods
                        </option>

                        @foreach($paymentMethods as $method)
                            <option
                                value="{{ $method }}"
                                {{ request('method') === $method ? 'selected' : '' }}
                            >
                                {{ ucfirst($method) }}
                            </option>
                        @endforeach
                    </select>

                    <select
                        name="date_range"
                        class="form-select"
                        form="payment-filter-form"
                    >
                        <option value="this month">
                            This month
                        </option>

                        <option
                            value="today"
                            {{ request('date_range') === 'today' ? 'selected' : '' }}
                        >
                            Today
                        </option>

                        <option
                            value="this week"
                            {{ request('date_range') === 'this week' ? 'selected' : '' }}
                        >
                            This week
                        </option>

                        <option
                            value="all time"
                            {{ request('date_range') === 'all time' ? 'selected' : '' }}
                        >
                            All time
                        </option>
                    </select>
                </div>
            </div>

            <form
                method="GET"
                action="{{ route('payments.index') }}"
                id="payment-filter-form"
            >
                <div class="payment-search">
                    <span>⌕</span>

                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by patient, payment ID or reference"
                    >
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
                        @forelse($payments as $payment)
                            @php
                                $method = strtolower($payment->method);

                                $methodClass = match ($method) {
                                    'cash' => 'cash-method',
                                    'bank', 'bank transfer' => 'bank-method',
                                    'card' => 'card-method',
                                    default => 'bank-method',
                                };
                            @endphp

                            <tr>
                                <td>
                                    {{ $payment->payment_id }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $payment->patient->full_name ?? 'Patient #' . $payment->patient_id }}
                                    </strong>

                                    <small>
                                        P-{{ str_pad($payment->patient_id, 4, '0', STR_PAD_LEFT) }}
                                    </small>
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}

                                    <br>

                                    <small>
                                        {{ \Carbon\Carbon::parse($payment->payment_date)->format('h:i A') }}
                                    </small>
                                </td>

                                <td>
                                    <span class="payment-method {{ $methodClass }}">
                                        {{ ucfirst($payment->method) }}
                                    </span>
                                </td>

                                <td class="payment-amount">
                                    PKR {{ number_format($payment->amount, 2) }}
                                </td>

                                <td>
                                    {{ $payment->reference_no ?? '—' }}
                                </td>

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
                                <td colspan="7" class="text-center">
                                    No payments found.
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