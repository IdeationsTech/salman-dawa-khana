@extends('layouts.app')

@section('title', 'Add Expense — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Expense Sidebar --}}
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">+</div>
            <span>Salman Dawa Khana</span>
        </div>

        <nav class="sidebar-nav">
            <a href="/dashboard" class="sidebar-link">
                <span>▦</span>
                <span>Dashboard</span>
            </a>

            <a href="/patients" class="sidebar-link">
                <span>♙</span>
                <span>Patients</span>
            </a>

            <a href="/visits" class="sidebar-link">
                <span>▣</span>
                <span>Visits</span>
            </a>

            <a href="/prescriptions" class="sidebar-link">
                <span>✎</span>
                <span>Prescriptions</span>
            </a>

            <a href="/payments" class="sidebar-link">
                <span>₨</span>
                <span>Payments</span>
            </a>

            <a href="/expenses" class="sidebar-link active">
                <span>◈</span>
                <span>Expenses</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    {{-- Expense Form Main Content --}}
    <main class="dashboard-main">

        <div class="form-page-header">
            <a href="/expenses" class="back-link">
                ← Back to expenses
            </a>

            <h1>Add expense</h1>
            <p>Record a clinic operating expense.</p>
        </div>

        <form action="#" method="POST">
            @csrf

            {{-- Expense Information --}}
            <section class="expense-form-panel">
                <div class="expense-form-heading">
                    <div class="form-section-number">01</div>

                    <div>
                        <h2>Expense information</h2>
                        <p>Enter the details of this expense.</p>
                    </div>
                </div>

                <div class="expense-form-grid">
                    <div class="form-field expense-field-wide">
                        <label for="description" class="form-label">
                            Description <span>*</span>
                        </label>

                        <input
                            type="text"
                            id="description"
                            name="description"
                            class="form-control"
                            placeholder="e.g. Electricity bill for September"
                        >
                    </div>

                    <div class="form-field">
                        <label for="category" class="form-label">
                            Category <span>*</span>
                        </label>

                        <select id="category" name="category" class="form-select">
                            <option selected>Select category</option>
                            <option>Utilities</option>
                            <option>Staff</option>
                            <option>Rent</option>
                            <option>Supplies</option>
                            <option>Transport</option>
                            <option>Other</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="expense_date" class="form-label">
                            Expense date <span>*</span>
                        </label>

                        <input
                            type="date"
                            id="expense_date"
                            name="expense_date"
                            class="form-control"
                            value="2026-09-21"
                        >
                    </div>
                </div>
            </section>

            {{-- Payment Details --}}
            <section class="expense-form-panel">
                <div class="expense-form-heading">
                    <div class="form-section-number">02</div>

                    <div>
                        <h2>Payment details</h2>
                        <p>Enter the amount and payment method.</p>
                    </div>
                </div>

                <div class="expense-form-grid">
                    <div class="form-field">
                        <label for="amount" class="form-label">
                            Amount <span>*</span>
                        </label>

                        <div class="expense-amount-input">
                            <span>PKR</span>

                            <input
                                type="number"
                                id="amount"
                                name="amount"
                                class="form-control"
                                placeholder="0.00"
                            >
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="payment_method" class="form-label">
                            Payment method <span>*</span>
                        </label>

                        <select
                            id="payment_method"
                            name="payment_method"
                            class="form-select"
                        >
                            <option selected>Cash</option>
                            <option>Bank transfer</option>
                            <option>Card</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="vendor" class="form-label">
                            Vendor / paid to
                        </label>

                        <input
                            type="text"
                            id="vendor"
                            name="vendor"
                            class="form-control"
                            placeholder="e.g. DEWA"
                        >
                    </div>

                    <div class="form-field">
                        <label for="reference" class="form-label">
                            Reference number
                        </label>

                        <input
                            type="text"
                            id="reference"
                            name="reference"
                            class="form-control"
                            placeholder="Receipt or transaction number"
                        >
                    </div>
                </div>
            </section>

            {{-- Notes --}}
            <section class="expense-form-panel">
                <div class="expense-form-heading">
                    <div class="form-section-number">03</div>

                    <div>
                        <h2>Notes</h2>
                        <p>Add any additional information.</p>
                    </div>
                </div>

                <div class="form-field">
                    <label for="notes" class="form-label">
                        Expense notes
                    </label>

                    <textarea
                        id="notes"
                        name="notes"
                        class="form-control"
                        rows="4"
                        placeholder="Write any notes about this expense..."
                    ></textarea>
                </div>
            </section>

            <div class="expense-form-actions">
                <a href="/expenses" class="btn btn-light">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    Save expense
                </button>
            </div>
        </form>

    </main>
</div>
@endsection