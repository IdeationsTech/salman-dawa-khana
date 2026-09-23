@extends('layouts.app')

@section('title', 'Reports — Salman Dawa Khana')

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

            <a href="{{ route('expenses.index') }}" class="sidebar-link">
                <span>◈</span>
                <span>Expenses</span>
            </a>

            <div class="sidebar-divider"></div>

            <a href="{{ route('reports.index') }}" class="sidebar-link active">
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

        <header class="page-header report-page-header">
            <div>
                <p class="dashboard-date">Business reporting</p>

                <h1>Reports</h1>

                <p class="page-subtitle">
                    Review clinic activity, income and expenses.
                </p>
            </div>

            <div class="report-header-actions">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-print-page
                >
                    Print Report
                </button>
            </div>
        </header>

        <form
            method="GET"
            action="{{ route('reports.index') }}"
            class="report-filter-panel"
        >
            <div class="report-filter-group">
                <label for="report_type">Report type</label>

                <select
                    name="report_type"
                    id="report_type"
                    class="form-select"
                >
                    <option
                        value="summary"
                        {{ $reportType === 'summary' ? 'selected' : '' }}
                    >
                        Summary
                    </option>

                    <option
                        value="payments"
                        {{ $reportType === 'payments' ? 'selected' : '' }}
                    >
                        Payments
                    </option>

                    <option
                        value="expenses"
                        {{ $reportType === 'expenses' ? 'selected' : '' }}
                    >
                        Expenses
                    </option>
                </select>
            </div>

            <div class="report-filter-date">
                <label for="from">From date</label>

                <input
                    type="date"
                    name="from"
                    id="from"
                    class="form-control"
                    value="{{ $from->format('Y-m-d') }}"
                >
            </div>

            <div class="report-filter-date">
                <label for="to">To date</label>

                <input
                    type="date"
                    name="to"
                    id="to"
                    class="form-control"
                    value="{{ $to->format('Y-m-d') }}"
                >
            </div>

            <div class="report-filter-group">
                <label>Period</label>

                <span class="report-period-label">
                    {{ $from->format('d M Y') }}
                    –
                    {{ $to->format('d M Y') }}
                </span>
            </div>

            <button type="submit" class="btn btn-primary report-apply-button">
                Generate Report
            </button>
        </form>

        <section class="report-summary-grid">

            <article class="report-summary-card">
                <div class="report-card-icon">♙</div>

                <div>
                    <span>Total patients</span>
                    <strong>{{ number_format($totalPatients ?? 0) }}</strong>
                    <small>Registered in selected period</small>
                </div>
            </article>

            <article class="report-summary-card">
                <div class="report-card-icon">▣</div>

                <div>
                    <span>Total visits</span>
                    <strong>{{ number_format($totalVisits ?? 0) }}</strong>
                    <small>Patient visits recorded</small>
                </div>
            </article>

            <article class="report-summary-card">
                <div class="report-card-icon">₨</div>

                <div>
                    <span>Total income</span>
                    <strong>
                        PKR {{ number_format($totalIncome ?? 0, 2) }}
                    </strong>
                    <small>Payments received</small>
                </div>
            </article>

            <article class="report-summary-card">
                <div class="report-card-icon">◈</div>

                <div>
                    <span>Total expenses</span>
                    <strong>
                        PKR {{ number_format($totalExpenses ?? 0, 2) }}
                    </strong>
                    <small>Operating expenses</small>
                </div>
            </article>
        </section>

        <div class="report-content-grid">

            <section class="report-panel">
                <div class="report-panel-heading">
                    <div>
                        <h2>Income vs expenses</h2>
                        <p>Financial activity for the selected period.</p>
                    </div>

                    <span class="report-period-label">
                        Selected period
                    </span>
                </div>

                <div class="report-chart-placeholder">
                    <div class="chart-y-labels">
                        <span>100k</span>
                        <span>75k</span>
                        <span>50k</span>
                        <span>25k</span>
                        <span>0</span>
                    </div>

                    <div class="chart-area">
                        <span class="chart-grid-line"></span>
                        <span class="chart-grid-line"></span>
                        <span class="chart-grid-line"></span>
                        <span class="chart-grid-line"></span>

                        <div class="chart-bars">
                            <div class="chart-group">
                                <div class="chart-bar income-bar" style="height: 78%;"></div>
                                <div class="chart-bar expense-bar" style="height: 35%;"></div>
                                <small>Week 1</small>
                            </div>

                            <div class="chart-group">
                                <div class="chart-bar income-bar" style="height: 65%;"></div>
                                <div class="chart-bar expense-bar" style="height: 30%;"></div>
                                <small>Week 2</small>
                            </div>

                            <div class="chart-group">
                                <div class="chart-bar income-bar" style="height: 88%;"></div>
                                <div class="chart-bar expense-bar" style="height: 42%;"></div>
                                <small>Week 3</small>
                            </div>

                            <div class="chart-group">
                                <div class="chart-bar income-bar" style="height: 72%;"></div>
                                <div class="chart-bar expense-bar" style="height: 28%;"></div>
                                <small>Week 4</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="chart-legend">
                    <span>
                        <i class="legend-dot income-dot"></i>
                        Income
                    </span>

                    <span>
                        <i class="legend-dot expense-dot"></i>
                        Expenses
                    </span>
                </div>
            </section>

            <section class="report-panel">
                <div class="report-panel-heading">
                    <div>
                        <h2>Expense breakdown</h2>
                        <p>Expenses by category.</p>
                    </div>
                </div>

                <div class="expense-breakdown">
                    <div>
                        <div class="breakdown-label">
                            <span>Rent</span>
                            <strong>35%</strong>
                        </div>

                        <div class="breakdown-track">
                            <div class="breakdown-fill rent-fill" style="width: 35%;"></div>
                        </div>
                    </div>

                    <div>
                        <div class="breakdown-label">
                            <span>Utilities</span>
                            <strong>28%</strong>
                        </div>

                        <div class="breakdown-track">
                            <div class="breakdown-fill utility-fill" style="width: 28%;"></div>
                        </div>
                    </div>

                    <div>
                        <div class="breakdown-label">
                            <span>Supplies</span>
                            <strong>22%</strong>
                        </div>

                        <div class="breakdown-track">
                            <div class="breakdown-fill supply-fill" style="width: 22%;"></div>
                        </div>
                    </div>

                    <div>
                        <div class="breakdown-label">
                            <span>Other</span>
                            <strong>15%</strong>
                        </div>

                        <div class="breakdown-track">
                            <div class="breakdown-fill other-fill" style="width: 15%;"></div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <section class="report-panel recent-report-panel">
            <div class="report-panel-heading">
                <div>
                    <h2>Recent financial activity</h2>
                    <p>Latest income and expense records.</p>
                </div>
            </div>

            <div class="report-activity-list">
                @forelse($payments->take(5) as $payment)
                    <div class="report-activity-row">
                        <div class="activity-icon income-activity">₨</div>

                        <div>
                            <strong>Payment received</strong>

                            <small>
                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                                · Patient {{ $payment->patient_id ?? '—' }}
                            </small>
                        </div>

                        <span class="activity-income">
                            + PKR {{ number_format($payment->amount, 2) }}
                        </span>
                    </div>
                @empty
                    <div class="report-activity-row">
                        <div>
                            <strong>No payment activity</strong>
                            <small>No records found for this period.</small>
                        </div>
                    </div>
                @endforelse

                @foreach($expenses->take(5) as $expense)
                    <div class="report-activity-row">
                        <div class="activity-icon expense-activity">◈</div>

                        <div>
                            <strong>{{ $expense->description ?? 'Expense recorded' }}</strong>

                            <small>
                                {{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}
                                · {{ $expense->category }}
                            </small>
                        </div>

                        <span class="activity-expense">
                            - PKR {{ number_format($expense->amount, 2) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </section>

    </main>
</div>
@endsection