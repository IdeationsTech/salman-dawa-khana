@extends('layouts.app')

@section('title', 'Expense Details — EXP-' . $expense->expense_id)

@section('content')
@php
    $clinic = $expense->clinic;
    $currency = strtoupper($clinic?->currency ?: 'AED');

    $method = strtolower(trim($expense->payment_method ?? ''));

    $methodLabel = match ($method) {
        'cash' => 'Cash',
        'bank', 'bank transfer', 'bank_transfer' => 'Bank transfer',
        'card' => 'Card',
        default => $expense->payment_method ?: 'Not recorded',
    };

    $categoryClass = match (strtolower($expense->category)) {
        'utilities', 'utility' => 'utility-category',
        'supplies', 'supply' => 'supply-category',
        'rent' => 'rent-category',
        default => 'other-category',
    };
@endphp

<div class="app-shell">

    {{-- EXPENSE DETAIL — Sidebar --}}
    <aside class="app-sidebar no-print">
        <div class="sidebar-brand">
            <div class="sidebar-logo">+</div>

            <span>{{ $clinic?->name ?? 'Salman Dawa Khana' }}</span>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="sidebar-link">
                <span>▦</span><span>Dashboard</span>
            </a>

            <a href="{{ route('patients.index') }}" class="sidebar-link">
                <span>♙</span><span>Patients</span>
            </a>

            <a href="{{ route('visits.index') }}" class="sidebar-link">
                <span>▣</span><span>Visits</span>
            </a>

            <a href="{{ route('prescriptions.index') }}" class="sidebar-link">
                <span>✎</span><span>Prescriptions</span>
            </a>

            <a href="{{ route('payments.index') }}" class="sidebar-link">
                <span>▤</span><span>Payments</span>
            </a>

            <a href="{{ route('expenses.index') }}" class="sidebar-link active">
                <span>◈</span><span>Expenses</span>
            </a>

            <div class="sidebar-divider"></div>

            <a href="{{ route('reports.index') }}" class="sidebar-link">
                <span>◌</span><span>Reports</span>
            </a>

            <a href="{{ route('settings.index') }}" class="sidebar-link">
                <span>⚙</span><span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    <main class="dashboard-main">
        @if(session('success'))
            <div class="alert alert-success no-print" role="alert">
                {{ session('success') }}
            </div>
        @endif

        {{-- EXPENSE DETAIL — Actions --}}
        <div class="expense-detail-toolbar no-print">
            <a href="{{ route('expenses.index') }}" class="back-link">
                ← Back to expenses
            </a>

            <div class="expense-toolbar-actions">
                <a
                    href="{{ route('expenses.edit', $expense) }}"
                    class="btn btn-outline-secondary"
                >
                    Edit expense
                </a>

                <button
                    type="button"
                    class="btn btn-primary"
                    data-print-page
                >
                    Print details
                </button>
            </div>
        </div>

        <article class="expense-detail-document">

            {{-- EXPENSE DETAIL — Header --}}
            <header class="expense-detail-header">
                <div class="expense-detail-title">
                    <div class="expense-detail-icon">◈</div>

                    <div>
                        <span>EXPENSE RECORD</span>
                        <h1>{{ $expense->category }}</h1>
                        <p>Expense ID: EXP-{{ $expense->expense_id }}</p>
                    </div>
                </div>

                <div class="expense-detail-date">
                    <span>Expense date</span>

                    <strong>
                        {{ $expense->expense_date?->format('d F Y') ?? 'Not recorded' }}
                    </strong>
                </div>
            </header>

            <div class="expense-detail-divider"></div>

            {{-- EXPENSE DETAIL — Summary --}}
            <section class="expense-detail-summary">
                <div>
                    <span>Amount</span>

                    <strong class="expense-detail-amount">
                        {{ $currency }}
                        {{ number_format($expense->amount, 2) }}
                    </strong>
                </div>

                <div>
                    <span>Category</span>

                    <strong>
                        <span class="expense-category {{ $categoryClass }}">
                            {{ $expense->category }}
                        </span>
                    </strong>
                </div>

                <div>
                    <span>Payment method</span>
                    <strong>{{ $methodLabel }}</strong>
                </div>

                <div>
                    <span>Recorded by</span>
                    <strong>{{ $expense->recordedBy?->name ?? 'Not recorded' }}</strong>
                </div>
            </section>

            {{-- EXPENSE DETAIL — Information --}}
            <section class="expense-detail-section">
                <div class="expense-section-heading">
                    <h2>Expense information</h2>
                </div>

                <div class="expense-info-grid">
                    <div>
                        <span>Description</span>

                        <strong>
                            {{ $expense->description ?: 'Not recorded' }}
                        </strong>
                    </div>

                    <div>
                        <span>Clinic</span>

                        <strong>
                            {{ $clinic?->name ?? 'Not recorded' }}
                        </strong>
                    </div>

                    <div>
                        <span>Expense date</span>

                        <strong>
                            {{ $expense->expense_date?->format('d M Y') ?? 'Not recorded' }}
                        </strong>
                    </div>

                    <div>
                        <span>Payment method</span>
                        <strong>{{ $methodLabel }}</strong>
                    </div>
                </div>
            </section>

            <footer class="expense-detail-footer">
                <p>
                    {{ $clinic?->name ?? 'Salman Dawa Khana' }}
                    · Clinic expense record
                </p>

                <small>This is a computer-generated expense detail.</small>
            </footer>

        </article>
    </main>
</div>
@endsection