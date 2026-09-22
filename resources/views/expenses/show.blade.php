@extends('layouts.app')

@section('title', 'Expense Details — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Expense Detail Sidebar --}}
    <aside class="app-sidebar no-print">
        <div class="sidebar-brand">
            <div class="sidebar-logo">+</div>
            <span>Salman Dawa Khana</span>
        </div>

        <nav class="sidebar-nav">
            <a href="/dashboard" class="sidebar-link">
                <span>▦</span><span>Dashboard</span>
            </a>

            <a href="/patients" class="sidebar-link">
                <span>♙</span><span>Patients</span>
            </a>

            <a href="/visits" class="sidebar-link">
                <span>▣</span><span>Visits</span>
            </a>

            <a href="/prescriptions" class="sidebar-link">
                <span>✎</span><span>Prescriptions</span>
            </a>

            <a href="/payments" class="sidebar-link">
                <span>₨</span><span>Payments</span>
            </a>

            <a href="/expenses" class="sidebar-link active">
                <span>◈</span><span>Expenses</span>
            </a>

            <div class="sidebar-divider"></div>

            <a href="/reports" class="sidebar-link">
                <span>◌</span><span>Reports</span>
            </a>

            <a href="/settings" class="sidebar-link">
                <span>⚙</span><span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    <main class="dashboard-main">

        <div class="expense-detail-toolbar no-print">
            <a href="/expenses" class="back-link">
                ← Back to expenses
            </a>

            <div class="expense-toolbar-actions">
                <button type="button" class="btn btn-outline-secondary">
                    Edit expense
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    onclick="window.print()"
                >
                    Print details
                </button>
            </div>
        </div>

        <article class="expense-detail-document">

            {{-- Expense Header --}}
            <header class="expense-detail-header">
                <div class="expense-detail-title">
                    <div class="expense-detail-icon">◈</div>

                    <div>
                        <span>EXPENSE RECORD</span>
                        <h1>Electricity bill</h1>
                        <p>Expense ID: EXP-00218</p>
                    </div>
                </div>

                <div class="expense-detail-date">
                    <span>Expense date</span>
                    <strong>21 September 2026</strong>
                </div>
            </header>

            <div class="expense-detail-divider"></div>

            {{-- Expense Summary --}}
            <section class="expense-detail-summary">
                <div>
                    <span>Amount</span>
                    <strong class="expense-detail-amount">PKR 8,500</strong>
                </div>

                <div>
                    <span>Category</span>
                    <strong>
                        <span class="expense-category utility-category">
                            Utilities
                        </span>
                    </strong>
                </div>

                <div>
                    <span>Payment method</span>
                    <strong>Bank transfer</strong>
                </div>

                <div>
                    <span>Status</span>
                    <strong class="expense-paid-status">Paid</strong>
                </div>
            </section>

            {{-- Expense Information --}}
            <section class="expense-detail-section">
                <div class="expense-section-heading">
                    <h2>Expense information</h2>
                </div>

                <div class="expense-info-grid">
                    <div>
                        <span>Description</span>
                        <strong>Electricity bill for September</strong>
                    </div>

                    <div>
                        <span>Vendor / paid to</span>
                        <strong>DEWA</strong>
                    </div>

                    <div>
                        <span>Reference number</span>
                        <strong>DEWA-SEP-2026-8842</strong>
                    </div>

                    <div>
                        <span>Recorded by</span>
                        <strong>Mohammad Khan</strong>
                    </div>
                </div>
            </section>

            {{-- Notes --}}
            <section class="expense-detail-notes">
                <span>Notes</span>
                <p>
                    Monthly electricity bill for the clinic premises.
                    Payment completed through bank transfer.
                </p>
            </section>

            <footer class="expense-detail-footer">
                <p>Salman Dawa Khana · Clinic expense record</p>
                <small>This is a computer-generated expense detail.</small>
            </footer>

        </article>

    </main>
</div>
@endsection