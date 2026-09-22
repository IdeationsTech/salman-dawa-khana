@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
<div class="page-header">
    <div>
        <span class="eyebrow">FINANCE MANAGEMENT</span>
        <h1>Expenses</h1>
        <p>Track clinic expenses and outgoing payments.</p>
    </div>

    <a href="{{ route('expenses.create') }}" class="btn btn-primary">
        + Add Expense
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card expenses-card">
    <div class="card-header">
        <form method="GET" action="{{ route('expenses.index') }}" class="expenses-toolbar">
            <div class="search-box">
                <span>⌕</span>

                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search expenses"
                >
            </div>

            <select name="category" class="form-select">
                <option value="all">All Categories</option>

                @foreach($categories as $category)
                    <option
                        value="{{ $category }}"
                        {{ request('category') === $category ? 'selected' : '' }}
                    >
                        {{ $category }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-outline-primary">
                Filter
            </button>

            <a href="{{ route('expenses.index') }}" class="btn btn-light">
                Clear
            </a>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table expenses-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Payment Method</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($expenses as $expense)
                    <tr>
                        <td>
                            {{ optional($expense->expense_date)->format('d M Y')
                                ?? \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}
                        </td>

                        <td>
                            <span class="category-badge">
                                {{ $expense->category }}
                            </span>
                        </td>

                        <td>{{ $expense->description ?? '—' }}</td>

                        <td>{{ $expense->payment_method ?? '—' }}</td>

                        <td>
                            <strong>
                                PKR {{ number_format($expense->amount, 2) }}
                            </strong>
                        </td>

                        <td>
                            <a
                                href="{{ route('expenses.show', $expense) }}"
                                class="btn btn-sm btn-outline-primary"
                            >
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            No expenses found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($expenses->hasPages())
        <div class="card-footer">
            {{ $expenses->links() }}
        </div>
    @endif
</div>
@endsection