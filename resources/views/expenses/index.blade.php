@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
@php
    $selectedCategory = request('category') ?: 'all';
    $selectedDateRange = request('date_range') ?: 'all time';
@endphp

<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">Clinic finances</p>
                <h1>Expenses</h1>
                <p class="page-subtitle">
                    Record and monitor clinic operating expenses.
                </p>
            </div>

            <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle" aria-hidden="true"></i>
                Add expense
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
                <a href="{{ route('expenses.index') }}" class="alert-link">
                    Reset filters
                </a>
            </div>
        @endif

        @if ($unconfirmedCurrencyCount > 0)
            <div class="alert alert-warning" role="status">
                {{ $unconfirmedCurrencyCount }} expense records have no confirmed
                currency. They are excluded from summary totals.
            </div>
        @endif

        <section class="expense-summary-grid">
            <article class="expense-summary-card">
                <span>Today's expenses</span>
                <strong>
                    {{ $currency }}
                    {{ number_format($todayExpenses, $moneyDecimals) }}
                </strong>
                <small>{{ $todayExpenseCount }} transactions</small>
            </article>

            <article class="expense-summary-card">
                <span>This month's expenses</span>
                <strong>
                    {{ $currency }}
                    {{ number_format($monthExpenses, $moneyDecimals) }}
                </strong>
                <small>{{ now('Asia/Dubai')->format('F Y') }}</small>
            </article>

            <article class="expense-summary-card">
                <span>Highest category</span>
                <strong>{{ $highestExpenseCategory ?? '—' }}</strong>
                <small>
                    {{ $currency }}
                    {{ number_format($highestCategoryAmount, $moneyDecimals) }}
                </small>
            </article>

            <article class="expense-summary-card">
                <span>Net income</span>
                <strong>
                    {{ $currency }}
                    {{ number_format($netIncome, $moneyDecimals) }}
                </strong>
                <small>Received payments minus expenses this month</small>
            </article>
        </section>

        <p class="text-muted small">
            Summary cards include only {{ $currency }} records.
            The list includes records in their original currencies.
        </p>

        <section class="expenses-panel">
            <div class="expenses-toolbar">
                <div>
                    <h2>Expense records</h2>
                    <p>Search and filter all clinic expense records.</p>
                </div>

                <div class="expenses-toolbar-actions">
                    <select
                        name="category"
                        class="form-select"
                        form="expense-filter-form"
                        aria-label="Expense category"
                    >
                        <option value="all" @selected($selectedCategory === 'all')>
                            All categories
                        </option>

                        @foreach ($categories as $category)
                            <option
                                value="{{ $category }}"
                                @selected($selectedCategory === $category)
                            >
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>

                    <select
                        name="date_range"
                        class="form-select"
                        form="expense-filter-form"
                        aria-label="Expense date range"
                    >
                        @foreach ([
                            'all time' => 'All time',
                            'today' => 'Today',
                            'this week' => 'This week',
                            'this month' => 'This month',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected($selectedDateRange === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <form
                method="GET"
                action="{{ route('expenses.index') }}"
                id="expense-filter-form"
                data-server-filters
            >
                <div class="expense-search">
                    <span>
                        <i class="bi bi-search" aria-hidden="true"></i>
                    </span>

                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        maxlength="160"
                        aria-label="Search expenses"
                        placeholder="Search by description, category or expense ID"
                    >

                    <button type="submit" class="btn btn-primary btn-sm">
                        Search
                    </button>

                    <a href="{{ route('expenses.index') }}" class="btn btn-light btn-sm">
                        Clear
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table expenses-table align-middle">
                    <thead>
                        <tr>
                            <th>Expense ID</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($expenses as $expense)
                            @php
                                $categoryClass = match (strtolower($expense->category)) {
                                    'utilities', 'utility' => 'utility-category',
                                    'supplies', 'supply' => 'supply-category',
                                    'rent' => 'rent-category',
                                    default => 'other-category',
                                };

                                $methodLabel = match (
                                    strtolower(trim($expense->payment_method ?? ''))
                                ) {
                                    'bank', 'bank transfer', 'bank_transfer' => 'Bank transfer',
                                    'cash' => 'Cash',
                                    'card' => 'Card',
                                    default => $expense->payment_method ?: '—',
                                };
                            @endphp

                            <tr>
                                <td>{{ $expense->expense_id }}</td>

                                <td>{{ $expense->description ?: 'Not recorded' }}</td>

                                <td>
                                    <span class="expense-category {{ $categoryClass }}">
                                        {{ $expense->category }}
                                    </span>
                                </td>

                                <td>
                                    {{ $expense->expense_date?->format('d M Y') ?? '—' }}
                                </td>

                                <td>{{ $methodLabel }}</td>

                                <td class="expense-amount">
                                    @if ($expense->currency)
                                        {{ strtoupper($expense->currency) }}
                                        {{ number_format($expense->amount, $moneyDecimals) }}
                                    @else
                                        {{ number_format($expense->amount, $moneyDecimals) }}
                                        <small class="d-block text-muted">
                                            Currency not recorded
                                        </small>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('expenses.show', $expense) }}"
                                        class="table-action"
                                    >
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    No expenses match your search and filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="patients-pagination">
                <span>
                    Showing {{ $expenses->firstItem() ?? 0 }}
                    to {{ $expenses->lastItem() ?? 0 }}
                    of {{ $expenses->total() }} expenses
                </span>
                <div>{{ $expenses->links() }}</div>
            </div>
        </section>
    </main>
</div>
@endsection