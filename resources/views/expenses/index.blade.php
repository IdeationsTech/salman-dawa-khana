@extends('layouts.app')

@section('title', 'Expenses — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Expenses Sidebar --}}
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

    {{-- Expenses Main Content --}}
    <main class="dashboard-main">

        <header class="page-header">
            <div>
                <p class="dashboard-date">Clinic finances</p>
                <h1>Expenses</h1>
                <p class="page-subtitle">
                    Record and monitor clinic operating expenses.
                </p>
            </div>

            <a href="/expenses/create" class="btn btn-primary">
                + Add Expense
            </a>
        </header>

        {{-- Expense Summary --}}
        <section class="expense-summary-grid">
            <article class="expense-summary-card">
                <span>Today's expenses</span>
                <strong>PKR 12,850</strong>
                <small>5 transactions</small>
            </article>

            <article class="expense-summary-card">
                <span>This month's expenses</span>
                <strong>PKR 186,400</strong>
                <small>September 2026</small>
            </article>

            <article class="expense-summary-card">
                <span>Highest category</span>
                <strong>Utilities</strong>
                <small>PKR 58,000 this month</small>
            </article>

            <article class="expense-summary-card">
                <span>Net income</span>
                <strong>PKR 499,350</strong>
                <small class="stat-success">After expenses</small>
            </article>
        </section>

        {{-- Expense Table --}}
        <section class="expenses-panel">

            <div class="expenses-toolbar">
                <div>
                    <h2>Expense records</h2>
                    <p>Recent clinic expenses and operating costs.</p>
                </div>

                <div class="expenses-toolbar-actions">
                    <select class="form-select">
                        <option>All categories</option>
                        <option>Utilities</option>
                        <option>Staff</option>
                        <option>Rent</option>
                        <option>Supplies</option>
                        <option>Other</option>
                    </select>

                    <select class="form-select">
                        <option>This month</option>
                        <option>Today</option>
                        <option>This week</option>
                        <option>All time</option>
                    </select>
                </div>
            </div>

            <div class="expense-search">
                <span>⌕</span>
                <input
                    type="search"
                    placeholder="Search by description, vendor or expense ID"
                >
            </div>

            <div class="table-responsive">
                <table class="table expenses-table align-middle">
                    <thead>
                        <tr>
                            <th>Expense ID</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>EXP-00218</td>
                            <td>
                                <strong>Electricity bill</strong>
                                <small>DEWA — September</small>
                            </td>
                            <td>
                                <span class="expense-category utility-category">
                                    Utilities
                                </span>
                            </td>
                            <td>21 Sep 2026</td>
                            <td>Bank</td>
                            <td class="expense-amount">PKR 8,500</td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>

                        <tr>
                            <td>EXP-00217</td>
                            <td>
                                <strong>Clinic supplies</strong>
                                <small>Paper and stationery</small>
                            </td>
                            <td>
                                <span class="expense-category supply-category">
                                    Supplies
                                </span>
                            </td>
                            <td>20 Sep 2026</td>
                            <td>Cash</td>
                            <td class="expense-amount">PKR 1,850</td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>

                        <tr>
                            <td>EXP-00216</td>
                            <td>
                                <strong>Internet bill</strong>
                                <small>Monthly internet service</small>
                            </td>
                            <td>
                                <span class="expense-category utility-category">
                                    Utilities
                                </span>
                            </td>
                            <td>19 Sep 2026</td>
                            <td>Bank</td>
                            <td class="expense-amount">PKR 3,200</td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>

                        <tr>
                            <td>EXP-00215</td>
                            <td>
                                <strong>Staff refreshments</strong>
                                <small>Weekly refreshments</small>
                            </td>
                            <td>
                                <span class="expense-category other-category">
                                    Other
                                </span>
                            </td>
                            <td>18 Sep 2026</td>
                            <td>Cash</td>
                            <td class="expense-amount">PKR 2,300</td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>

                        <tr>
                            <td>EXP-00214</td>
                            <td>
                                <strong>Clinic rent</strong>
                                <small>September monthly rent</small>
                            </td>
                            <td>
                                <span class="expense-category rent-category">
                                    Rent
                                </span>
                            </td>
                            <td>15 Sep 2026</td>
                            <td>Bank</td>
                            <td class="expense-amount">PKR 45,000</td>
                            <td class="text-end">
                                <a href="#" class="table-action">View</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="patients-pagination">
                <span>Showing 1 to 5 of 50 expenses</span>

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