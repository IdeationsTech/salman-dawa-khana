@extends('layouts.app')

@section('title', 'Payment Receipt — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Payment Receipt Sidebar --}}
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

            <a href="/payments" class="sidebar-link active">
                <span>₨</span><span>Payments</span>
            </a>

            <a href="/expenses" class="sidebar-link">
                <span>◈</span><span>Expenses</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    <main class="dashboard-main">

        <div class="payment-detail-toolbar no-print">
            <a href="/payments" class="back-link">
                ← Back to payments
            </a>

            <div class="payment-toolbar-actions">
                <button type="button" class="btn btn-outline-secondary">
                    Download PDF
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    onclick="window.print()"
                >
                    Print receipt
                </button>
            </div>
        </div>

        <article class="payment-receipt">

            {{-- Receipt Header --}}
            <header class="receipt-header">
                <div class="receipt-brand">
                    <div class="receipt-brand-mark">+</div>

                    <div>
                        <h1>Salman Dawa Khana</h1>
                        <p>Patient Care &amp; Herbal Wellness</p>
                    </div>
                </div>

                <div class="receipt-meta">
                    <span>PAYMENT RECEIPT</span>
                    <strong>PAY-00621</strong>
                    <small>21 September 2026</small>
                </div>
            </header>

            <div class="receipt-divider"></div>

            {{-- Receipt Status --}}
            <div class="receipt-status-row">
                <div>
                    <span>Payment status</span>
                    <strong class="receipt-paid-status">Paid</strong>
                </div>

                <div>
                    <span>Payment method</span>
                    <strong>Cash</strong>
                </div>

                <div>
                    <span>Received by</span>
                    <strong>Dr. Ahmed Khan</strong>
                </div>
            </div>

            {{-- Patient Information --}}
            <section class="receipt-section">
                <div class="receipt-section-heading">
                    <h2>Patient information</h2>
                </div>

                <div class="receipt-info-grid">
                    <div>
                        <span>Patient name</span>
                        <strong>Muhammad Ali</strong>
                    </div>

                    <div>
                        <span>Patient ID</span>
                        <strong>P-0001</strong>
                    </div>

                    <div>
                        <span>Phone number</span>
                        <strong>050 123 4567</strong>
                    </div>

                    <div>
                        <span>Related bill</span>
                        <strong>B-00482</strong>
                    </div>
                </div>
            </section>

            {{-- Payment Breakdown --}}
            <section class="receipt-section">
                <div class="receipt-section-heading">
                    <h2>Payment breakdown</h2>
                </div>

                <div class="receipt-breakdown">
                    <div>
                        <span>Previous due</span>
                        <strong>PKR 1,000</strong>
                    </div>

                    <div>
                        <span>Current bill</span>
                        <strong>PKR 2,500</strong>
                    </div>

                    <div class="receipt-total-row">
                        <span>Total payable</span>
                        <strong>PKR 3,500</strong>
                    </div>

                    <div class="receipt-paid-row">
                        <span>Amount received</span>
                        <strong>PKR 2,000</strong>
                    </div>

                    <div class="receipt-balance-row">
                        <span>Remaining balance</span>
                        <strong>PKR 1,500</strong>
                    </div>
                </div>
            </section>

            {{-- Notes --}}
            <section class="receipt-notes">
                <span>Notes</span>
                <p>Partial payment received. Remaining amount due on next visit.</p>
            </section>

            <footer class="receipt-footer">
                <p>Thank you for visiting Salman Dawa Khana.</p>
                <small>This is a computer-generated payment receipt.</small>
            </footer>

        </article>

    </main>
</div>
@endsection