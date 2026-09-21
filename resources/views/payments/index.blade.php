@extends('layouts.app')

@section('title', 'Payments — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Payments Sidebar --}}
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

            <a href="/payments" class="sidebar-link active">
                <span>₨</span>
                <span>Payments</span>
            </a>

            <a href="#" class="sidebar-link">
                <span>◈</span>
                <span>Expenses</span>
            </a>

            <div class="sidebar-divider"></div>

            <a href="#" class="sidebar-link">
                <span>◌</span>
                <span>Reports</span>
            </a>

            <a href="#" class="sidebar-link">
                <span>⚙</span>
                <span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    {{-- Payments Main Content --}}
    <main class="dashboard-main">

        <header class="page-header">
            <div>
                <p class="dashboard-date">Clinic finances</p>
                <h1>Payments</h1>
                <p class="page-subtitle">
                    Track patient payments, income and outstanding balances.
                </p>
            </div>

            <a href="#" class="btn btn-primary">
                + Record Payment
            </a>
        </header>

        {{-- Payment Summary --}}
        <section class="payment-summary-grid">
            <article class="payment-summary-card">
                <span>Today's income</span>
                <strong>PKR 86,300</strong>
                <small class="stat-success">+18% from yesterday</small>
            </article>

            <article class="payment-summary-card">
                <span>This month's income</span>
                <strong>PKR 685,750</strong>
                <small>September 2026</small>
            </article>

            <article class="payment-summary-card">
                <span>Cash received</span>
                <strong>PKR 52,800</strong>
                <small>61% of today's income</small>
            </article>

            <article class="payment-summary-card">
                <span>Outstanding dues</span>
                <strong>PKR 42,500</strong>
                <small class="stat-warning">12 patients due</small>
            </article>
        </section>

        {{-- Payment Table --}}
        <section class="payments-panel">

            <div class="payments-toolbar">
                <div>
                    <h2>Payment records</h2>
                    <p>Recent patient payments and transactions.</p>
                </div>

                <div class="payments-toolbar-actions">
                    <select class="form-select">
                        <option>All methods</option>
                        <option>Cash</option>
                        <option>Bank transfer</option>
                        <option>Card</option>
                    </select>

                    <select class="form-select">
                        <option>This month</option>
                        <option>Today</option>
                        <option>This week</option>
                        <option>All time</option>
                    </select>
                </div>
            </div>

            <div class="payment-search">
                <span>⌕</span>
                <input
                    type="search"
                    placeholder="Search by patient, payment ID or reference"
                >
            </div>

            <div class="table-responsive">
                <table class="table payments-table align-middle">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Patient</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Reference</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>PAY-00621</td>
                            <td>
                                <strong>Muhammad Ali</strong>
                                <small>P-0001</small>
                            </td>
                            <td>21 Sep 2026<br><small>10:45 AM</small></td>
                            <td>
                                <span class="payment-method cash-method">
                                    Cash
                                </span>
                            </td>
                            <td class="payment-amount">PKR 2,500</td>
                            <td>—</td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>

                        <tr>
                            <td>PAY-00620</td>
                            <td>
                                <strong>Fatima Bibi</strong>
                                <small>P-0002</small>
                            </td>
                            <td>21 Sep 2026<br><small>09:30 AM</small></td>
                            <td>
                                <span class="payment-method bank-method">
                                    Bank
                                </span>
                            </td>
                            <td class="payment-amount">PKR 2,500</td>
                            <td>BT-982145</td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>

                        <tr>
                            <td>PAY-00619</td>
                            <td>
                                <strong>Ahmed Raza</strong>
                                <small>P-0003</small>
                            </td>
                            <td>20 Sep 2026<br><small>04:35 PM</small></td>
                            <td>
                                <span class="payment-method cash-method">
                                    Cash
                                </span>
                            </td>
                            <td class="payment-amount">PKR 2,500</td>
                            <td>—</td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>

                        <tr>
                            <td>PAY-00618</td>
                            <td>
                                <strong>Ayesha Khan</strong>
                                <small>P-0004</small>
                            </td>
                            <td>20 Sep 2026<br><small>02:25 PM</small></td>
                            <td>
                                <span class="payment-method card-method">
                                    Card
                                </span>
                            </td>
                            <td class="payment-amount">PKR 1,500</td>
                            <td>POS-4431</td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>

                        <tr>
                            <td>PAY-00617</td>
                            <td>
                                <strong>Bilal Hussain</strong>
                                <small>P-0005</small>
                            </td>
                            <td>19 Sep 2026<br><small>12:00 PM</small></td>
                            <td>
                                <span class="payment-method cash-method">
                                    Cash
                                </span>
                            </td>
                            <td class="payment-amount">PKR 2,500</td>
                            <td>—</td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="patients-pagination">
                <span>Showing 1 to 5 of 621 payments</span>

                <div>
                    <button class="pagination-button">‹</button>
                    <button class="pagination-button active">1</button>
                    <button class="pagination-button">2</button>
                    <button class="pagination-button">3</button>
                    <button class="pagination-button">›</button>
                </div>
            </div>

        </section>

    </main>
</div>
@endsection