@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="page-header">
    <div>
        <span class="eyebrow">BUSINESS REPORTING</span>
        <h1>Reports</h1>
        <p>Review clinic activity, income and expenses.</p>
    </div>

    <button type="button" class="btn btn-outline-primary" data-print-page>
        Print Report
    </button>
</div>

<div class="card reports-filter-card">
    <form method="GET" action="{{ route('reports.index') }}" class="reports-toolbar">
        <div>
            <label for="report_type" class="form-label">Report Type</label>

            <select name="report_type" id="report_type" class="form-select">
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

        <div>
            <label for="from" class="form-label">From Date</label>

            <input
                type="date"
                name="from"
                id="from"
                class="form-control"
                value="{{ $from->format('Y-m-d') }}"
            >
        </div>

        <div>
            <label for="to" class="form-label">To Date</label>

            <input
                type="date"
                name="to"
                id="to"
                class="form-control"
                value="{{ $to->format('Y-m-d') }}"
            >
        </div>

        <button type="submit" class="btn btn-primary">
            Generate Report
        </button>
    </form>
</div>

<div class="report-summary-grid">
    <div class="summary-card">
        <span>Total Patients</span>
        <strong>{{ number_format($totalPatients) }}</strong>
    </div>

    <div class="summary-card">
        <span>Total Visits</span>
        <strong>{{ number_format($totalVisits) }}</strong>
    </div>

    <div class="summary-card">
        <span>Total Income</span>
        <strong>PKR {{ number_format($totalIncome, 2) }}</strong>
    </div>

    <div class="summary-card">
        <span>Total Expenses</span>
        <strong>PKR {{ number_format($totalExpenses, 2) }}</strong>
    </div>

    <div class="summary-card summary-card-highlight">
        <span>Net Income</span>
        <strong>PKR {{ number_format($netIncome, 2) }}</strong>
    </div>
</div>

@if($reportType === 'summary' || $reportType === 'payments')
    <div class="card report-table-card">
        <div class="card-header">
            <h2>Payments Report</h2>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Payment ID</th>
                        <th>Patient ID</th>
                        <th>Method</th>
                        <th>Amount</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>
                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                            </td>

                            <td>{{ $payment->payment_id }}</td>
                            <td>{{ $payment->patient_id ?? '—' }}</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td>PKR {{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                No payment records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@if($reportType === 'summary' || $reportType === 'expenses')
    <div class="card report-table-card">
        <div class="card-header">
            <h2>Expenses Report</h2>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>
                                {{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}
                            </td>

                            <td>{{ $expense->category }}</td>
                            <td>{{ $expense->description ?? '—' }}</td>
                            <td>PKR {{ number_format($expense->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">
                                No expense records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection