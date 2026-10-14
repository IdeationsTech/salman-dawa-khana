@extends('layouts.app')

@section('title', 'Expense Details — EXP-' . $expense->expense_id)

@section('content')
@php
    $clinic = $expense->clinic;

    $currency = $expense->currency
        ? strtoupper($expense->currency)
        : null;

    $decimalSetting = \App\Models\Setting::query()
        ->where('clinic_id', $expense->clinic_id)
        ->where('setting_key', 'decimal_places')
        ->value('setting_value');

    $moneyDecimals = (string) $decimalSetting === '0' ? 0 : 2;

    $methodLabel = match (
        strtolower(trim($expense->payment_method ?? ''))
    ) {
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
    @include('layouts._sidebar')

    <main class="dashboard-main">
        @if (session('success'))
            <div class="alert alert-success no-print" role="status">
                {{ session('success') }}
            </div>
        @endif

        <div class="expense-detail-toolbar no-print">
            <a href="{{ route('expenses.index') }}" class="back-link">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Back to expenses
            </a>

            <div class="expense-toolbar-actions">
                <a
                    href="{{ route('expenses.edit', $expense) }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                    Edit expense
                </a>

                <button type="button" class="btn btn-primary" data-print-page>
                    <i class="bi bi-printer" aria-hidden="true"></i>
                    Print details
                </button>
            </div>
        </div>

        <article class="expense-detail-document">
            <header class="expense-detail-header">
                <div class="expense-detail-title">
                    <div class="expense-detail-icon">
                        <i class="bi bi-receipt app-icon" aria-hidden="true"></i>
                    </div>

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

            <section class="expense-detail-summary">
                <div>
                    <span>Amount</span>
                    <strong class="expense-detail-amount">
                        {{ $currency }}
                        {{ number_format($expense->amount, $moneyDecimals) }}
                    </strong>
                    @if (!$currency)
                        <small>Currency not recorded</small>
                    @endif
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
                    <strong>
                        {{ $expense->recordedBy?->name ?? 'Not recorded' }}
                    </strong>
                </div>
            </section>

            <section class="expense-detail-section">
                <div class="expense-section-heading">
                    <h2>Expense information</h2>
                </div>

                <div class="expense-info-grid">
                    <div>
                        <span>Description</span>
                        <p class="mb-0">
                            {{ $expense->description ?: 'Not recorded' }}
                        </p>
                    </div>

                    <div>
                        <span>Clinic</span>
                        <strong>{{ $clinic?->name ?? 'Not recorded' }}</strong>
                    </div>

                    <div>
                        <span>Expense date</span>
                        <strong>
                            {{ $expense->expense_date?->format('d M Y') ?? 'Not recorded' }}
                        </strong>
                    </div>

                    <div>
                        <span>Original currency</span>
                        <strong>{{ $currency ?: 'Not recorded' }}</strong>
                    </div>
                </div>
            </section>

            <footer class="expense-detail-footer">
                <p>{{ $clinic?->name }} · Clinic expense record</p>
                <small>This is a computer-generated expense detail.</small>
            </footer>
        </article>
    </main>
</div>
@endsection