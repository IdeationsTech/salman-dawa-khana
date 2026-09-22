@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="page-header">
    <div>
        <span class="eyebrow">FINANCE MANAGEMENT</span>
        <h1>Payments</h1>
        <p>View and manage patient payments.</p>
    </div>

    <a href="{{ route('payments.create') }}" class="btn btn-primary">
        + Record Payment
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card payments-card">
    <div class="card-header">
        <form method="GET" action="{{ route('payments.index') }}" class="payments-toolbar">
            <div class="search-box">
                <span>⌕</span>

                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search payment or reference"
                >
            </div>

            <select name="payment_method" class="form-select">
                <option value="all">All Methods</option>

                @foreach($paymentMethods as $method)
                    <option
                        value="{{ $method }}"
                        {{ request('payment_method') === $method ? 'selected' : '' }}
                    >
                        {{ ucwords(str_replace('_', ' ', $method)) }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-outline-primary">
                Filter
            </button>

            <a href="{{ route('payments.index') }}" class="btn btn-light">
                Clear
            </a>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table payments-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Payment ID</th>
                    <th>Patient ID</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Amount</th>
                    <th>Action</th>
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

                        <td>
                            <span class="payment-method">
                                {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}
                            </span>
                        </td>

                        <td>{{ $payment->reference ?? '—' }}</td>

                        <td>
                            <strong>
                                PKR {{ number_format($payment->amount, 2) }}
                            </strong>
                        </td>

                        <td>
                            <a
                                href="{{ route('payments.show', $payment) }}"
                                class="btn btn-sm btn-outline-primary"
                            >
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            No payments found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
        <div class="card-footer">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection