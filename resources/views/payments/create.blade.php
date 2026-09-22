@extends('layouts.app')

@section('title', 'Record Payment — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Payment Sidebar --}}
    <aside class="app-sidebar">
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

    {{-- Payment Form Main Content --}}
    <main class="dashboard-main">

        <div class="form-page-header">
            <a href="/payments" class="back-link">
                ← Back to payments
            </a>

            <h1>Record payment</h1>
            <p>Record a payment received from a patient.</p>
        </div>

        <form action="#" method="POST">
            @csrf

            {{-- Patient and Bill --}}
            <section class="payment-form-panel">
                <div class="payment-form-heading">
                    <div class="form-section-number">01</div>

                    <div>
                        <h2>Patient and bill</h2>
                        <p>Select the patient and related bill.</p>
                    </div>
                </div>

                <div class="payment-form-grid">
                    <div class="form-field">
                        <label for="patient" class="form-label">
                            Patient <span>*</span>
                        </label>

                        <select id="patient" name="patient" class="form-select">
                            <option selected>Select patient</option>
                            <option>Muhammad Ali — P-0001</option>
                            <option>Fatima Bibi — P-0002</option>
                            <option>Ahmed Raza — P-0003</option>
                            <option>Ayesha Khan — P-0004</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="bill" class="form-label">
                            Outstanding bill
                        </label>

                        <select id="bill" name="bill" class="form-select">
                            <option selected>
                                Bill #B-00482 — 21 Sep 2026
                            </option>
                            <option>
                                Bill #B-00461 — 05 Sep 2026
                            </option>
                        </select>
                    </div>
                </div>
            </section>

            {{-- Balance Summary --}}
            <section class="payment-form-panel">
                <div class="payment-form-heading">
                    <div class="form-section-number">02</div>

                    <div>
                        <h2>Balance summary</h2>
                        <p>Review the patient's current financial position.</p>
                    </div>
                </div>

                <div class="balance-summary-grid">
                    <div>
                        <span>Previous due</span>
                        <strong>PKR 1,000</strong>
                    </div>

                    <div>
                        <span>Current bill</span>
                        <strong>PKR 2,500</strong>
                    </div>

                    <div>
                        <span>Total payable</span>
                        <strong data-total-payable>PKR 3,500</strong>
                    </div>
                </div>
            </section>

            {{-- Payment Details --}}
            <section class="payment-form-panel">
                <div class="payment-form-heading">
                    <div class="form-section-number">03</div>

                    <div>
                        <h2>Payment details</h2>
                        <p>Enter the amount and payment method.</p>
                    </div>
                </div>

                <div class="payment-form-grid">
                    <div class="form-field">
                        <label for="amount" class="form-label">
                            Amount received <span>*</span>
                        </label>

                        <div class="amount-input">
                            <span>PKR</span>

                            <input
                                type="number"
                                id="amount"
                                name="amount"
                                class="form-control"
                                value="2000"
                                min="0"
                                data-payment-amount
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
                        <label for="payment_date" class="form-label">
                            Payment date
                        </label>

                        <input
                            type="date"
                            id="payment_date"
                            name="payment_date"
                            class="form-control"
                            value="2026-09-22"
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
                            placeholder="Required for bank/card"
                        >
                    </div>

                    <div class="form-field payment-field-wide">
                        <label for="notes" class="form-label">
                            Notes
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            class="form-control"
                            rows="3"
                            placeholder="Add any payment notes..."
                        ></textarea>
                    </div>
                </div>

                <div class="remaining-balance-box">
                    <span>Remaining balance after this payment</span>

                    <strong data-remaining-balance>
                        PKR 1,500
                    </strong>
                </div>
            </section>

            {{-- Form Actions --}}
            <div class="payment-form-actions">
                <a href="/payments" class="btn btn-light">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    Save payment
                </button>
            </div>
        </form>

    </main>
</div>
@endsection