@extends('layouts.app')

@section('title', isset($expense) ? 'Edit Expense' : 'Add Expense')

@section('content')
@php
    $editing = isset($expense);

    $existingCategory = $editing
        ? strtolower(trim($expense->category))
        : '';

    $defaultCategory = [
        'transport' => 'Travel',
        'others' => 'Other',
    ][$existingCategory] ?? '';

    if ($defaultCategory === '') {
        foreach ($categories as $label) {
            if (strtolower($label) === $existingCategory) {
                $defaultCategory = $label;
                break;
            }
        }
    }

    $selectedCategory = old('category', $defaultCategory);

    $defaultMethod = $editing
        ? strtolower(trim($expense->payment_method))
        : '';

    if (in_array($defaultMethod, ['bank transfer', 'bank_transfer'], true)) {
        $defaultMethod = 'bank';
    }

    $selectedMethod = old('payment_method', $defaultMethod);

    $expenseDate = old(
        'expense_date',
        $editing
            ? $expense->expense_date?->format('Y-m-d')
            : \App\Support\ClinicClock::now()->format('Y-m-d')
    );
@endphp

<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <div class="form-page-header">
            <a href="{{ route('expenses.index') }}" class="back-link">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Back to expenses
            </a>

            <h1>{{ $editing ? 'Edit expense' : 'Add expense' }}</h1>
            <p>Record a clinic expense.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Please correct the following:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($editing && !$currency)
            <div class="alert alert-warning" role="status">
                This expense has no confirmed currency.
                Editing does not assign or convert its currency.
            </div>
        @endif

        <form
            method="POST"
            action="{{ $editing
                ? route('expenses.update', $expense)
                : route('expenses.store') }}"
        >
            @csrf

            @if ($editing)
                @method('PUT')
            @endif

            <section class="expense-form-panel">
                <div class="expense-form-heading">
                    <div class="form-section-number">01</div>
                    <div>
                        <h2>Expense information</h2>
                        <p>All fields marked * are required.</p>
                    </div>
                </div>

                <div class="expense-form-grid">
                    <div class="form-field expense-field-wide">
                        <label for="description" class="form-label">
                            Description *
                        </label>

                        <input
                            type="text"
                            id="description"
                            name="description"
                            class="form-control @error('description') is-invalid @enderror"
                            value="{{ old('description', $expense->description ?? '') }}"
                            maxlength="255"
                            placeholder="Describe what this expense was for"
                            required
                        >

                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="category" class="form-label">Category *</label>

                        <select
                            id="category"
                            name="category"
                            class="form-select @error('category') is-invalid @enderror"
                            required
                        >
                            <option value="">Select category</option>
                            @foreach ($categories as $category)
                                <option
                                    value="{{ $category }}"
                                    @selected($selectedCategory === $category)
                                >
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>

                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <small class="text-muted d-block mt-2">
                            Medicines: medicine buying costs.
                            Medicine Packaging: bottles, jars, packets,
                            labels and packing material.
                        </small>
                    </div>

                    <div class="form-field">
                        <label for="expense_date" class="form-label">
                            Expense date *
                        </label>

                        <input
                            type="date"
                            id="expense_date"
                            name="expense_date"
                            class="form-control @error('expense_date') is-invalid @enderror"
                            value="{{ $expenseDate }}"
                            min="1000-01-01"
                            max="9999-12-31"
                            required
                        >

                        @error('expense_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            <section class="expense-form-panel">
                <div class="expense-form-heading">
                    <div class="form-section-number">02</div>
                    <div>
                        <h2>Payment details</h2>
                        <p>
                            Currency: {{ $currency ?: 'Not recorded' }}.
                            @if ($editing)
                                Original expense currency is preserved.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="expense-form-grid">
                    <div class="form-field">
                        <label for="amount" class="form-label">Amount *</label>

                        <div class="expense-amount-input">
                            <span>{{ $currency ?: '—' }}</span>

                            <input
                                type="number"
                                id="amount"
                                name="amount"
                                class="form-control @error('amount') is-invalid @enderror"
                                value="{{ old('amount', $expense->amount ?? '') }}"
                                placeholder="0.00"
                                min="0.01"
                                max="9999999999.99"
                                step="0.01"
                                inputmode="decimal"
                                required
                            >
                        </div>

                        @error('amount')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="payment_method" class="form-label">
                            Payment method *
                        </label>

                        <select
                            id="payment_method"
                            name="payment_method"
                            class="form-select @error('payment_method') is-invalid @enderror"
                            required
                        >
                            <option value="">Select payment method</option>

                            @foreach ([
                                'cash' => 'Cash',
                                'bank' => 'Bank transfer',
                                'card' => 'Card',
                            ] as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected($selectedMethod === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            <div class="expense-form-actions">
                <a href="{{ route('expenses.index') }}" class="btn btn-light">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    {{ $editing ? 'Update expense' : 'Save expense' }}
                </button>
            </div>
        </form>
    </main>
</div>
@endsection