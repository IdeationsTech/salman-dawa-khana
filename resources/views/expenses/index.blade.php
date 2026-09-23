@extends('layouts.app')

@section('title', 'Expenses — Salman Dawa Khana')

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

            <a href="{{ route('payments.index') }}" class="sidebar-link">
                <span>₨</span>
                <span>Payments</span>
            </a>

            <a href="{{ route('expenses.index') }}" class="sidebar-link active">
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

                <h1>Expenses</h1>

                <p class="page-subtitle">
                    Record and monitor clinic operating expenses.
                </p>
            </div>

            <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                + Add Expense
            </a>
        </header>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <section class="expense-summary-grid">
            <article class="expense-summary-card">
                <span>Today's expenses</span>

                <strong>
                    PKR {{ number_format($todayExpenses ?? 0, 2) }}
                </strong>

                <small>
                    {{ $todayExpenseCount ?? 0 }} transactions
                </small>
            </article>

            <article class="expense-summary-card">
                <span>This month's expenses</span>

                <strong>
                    PKR {{ number_format($monthExpenses ?? 0, 2) }}
                </strong>

                <small>
                    {{ now()->format('F Y') }}
                </small>
            </article>

            <article class="expense-summary-card">
                <span>Highest category</span>

                <strong>
                    {{ $highestExpenseCategory ?? '—' }}
                </strong>

                <small>
                    PKR {{ number_format($highestCategoryAmount ?? 0, 2) }}
                </small>
            </article>

            <article class="expense-summary-card">
                <span>Net income</span>

                <strong>
                    PKR {{ number_format($netIncome ?? 0, 2) }}
                </strong>

                <small class="stat-success">
                    After expenses
                </small>
            </article>
        </section>

        <section class="expenses-panel">

            <div class="expenses-toolbar">
                <div>
                    <h2>Expense records</h2>

                    <p>
                        Recent clinic expenses and operating costs.
                    </p>
                </div>

                <div class="expenses-toolbar-actions">

                    <select
                        name="category"
                        class="form-select"
                        form="expense-filter-form"
                    >
                        <option value="all">
                            All categories
                        </option>

                        @foreach($categories as $category)
                            <option
                                value="{{ $category }}"
                                {{ request('category') === $category ? 'selected' : '' }}
                            >
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>

                    <select
                        name="date_range"
                        class="form-select"
                        form="expense-filter-form"
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
                action="{{ route('expenses.index') }}"
                id="expense-filter-form"
            >
                <div class="expense-search">
                    <span>⌕</span>

                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by description, vendor or expense ID"
                    >
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
                        @forelse($expenses as $expense)
                            @php
                                $categoryClass = match (strtolower($expense->category)) {
                                    'utilities', 'utility' => 'utility-category',
                                    'supplies', 'supply' => 'supply-category',
                                    'rent' => 'rent-category',
                                    default => 'other-category',
                                };
                            @endphp

                            <tr>
                                <td>
                                    {{ $expense->expense_id }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $expense->description ?? 'Expense record' }}
                                    </strong>

                                    <small>
                                        {{ $expense->notes ?? 'Clinic expense' }}
                                    </small>
                                </td>

                                <td>
                                    <span class="expense-category {{ $categoryClass }}">
                                        {{ $expense->category }}
                                    </span>
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}
                                </td>

                                <td>
                                    {{ $expense->payment_method ?? '—' }}
                                </td>

                                <td class="expense-amount">
                                    PKR {{ number_format($expense->amount, 2) }}
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
                                <td colspan="7" class="text-center">
                                    No expenses found.
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

                <div>
                    {{ $expenses->links() }}
                </div>
            </div>

        </section>

    </main>
</div>
@endsection