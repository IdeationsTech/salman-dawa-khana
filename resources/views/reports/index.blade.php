@extends('layouts.app')

@section('title', 'Reports — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Reports Sidebar --}}
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

            <a href="/expenses" class="sidebar-link">
                <span>◈</span>
                <span>Expenses</span>
            </a>

            <div class="sidebar-divider"></div>

            <a href="/reports" class="sidebar-link active">
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

    {{-- Reports Main Content --}}
    <main class="dashboard-main">

        <header class="page-header report-page-header">
            <div>
                <p class="dashboard-date">Business overview</p>
                <h1>Reports</h1>
                <p class="page-subtitle">
                    Review clinic performance and financial activity.
                </p>
            </div>

            <div class="report-header-actions">
                <button class="btn btn-outline-secondary">
                    Print report
                </button>

                <button class="btn btn-primary">
                    Export report
                </button>
            </div>
        </header>

        {{-- Report Filters --}}
        <section class="report-filter-panel">
            <div class="report-filter-group">
                <label for="date_range">Date range</label>

                <select id="date_range" class="form-select">
                    <option>This month</option>
                    <option>Today</option>
                    <option>This week</option>
                    <option>Last month</option>
                    <option>Custom range</option>
                </select>
            </div>

            <div class="report-filter-group">
                <label for="report_type">Report type</label>

                <select id="report_type" class="form-select">
                    <option>Financial overview</option>
                    <option>Patient activity</option>
                    <option>Visit summary</option>
                    <option>Payment report</option>
                </select>
            </div>

            <div class="report-filter-date">
                <label for="from_date">From</label>
                <input
                    type="date"
                    id="from_date"
                    class="form-control"
                    value="2026-09-01"
                >
            </div>

            <div class="report-filter-date">
                <label for="to_date">To</label>
                <input
                    type="date"
                    id="to_date"
                    class="form-control"
                    value="2026-09-21"
                >
            </div>

            <button class="btn btn-primary report-apply-button">
                Apply
            </button>
        </section>

        {{-- Report Summary --}}
        <section class="report-summary-grid">
            <article class="report-summary-card income-report-card">
                <div class="report-card-icon">₨</div>
                <div>
                    <span>Total income</span>
                    <strong>PKR 685,750</strong>
                    <small class="stat-success">+16.8% vs last month</small>
                </div>
            </article>

            <article class="report-summary-card expense-report-card">
                <div class="report-card-icon">◈</div>
                <div>
                    <span>Total expenses</span>
                    <strong>PKR 186,400</strong>
                    <small>Operating expenses</small>
                </div>
            </article>

            <article class="report-summary-card net-report-card">
                <div class="report-card-icon">↗</div>
                <div>
                    <span>Net income</span>
                    <strong>PKR 499,350</strong>
                    <small class="stat-success">72.8% margin</small>
                </div>
            </article>

            <article class="report-summary-card patients-report-card">
                <div class="report-card-icon">♙</div>
                <div>
                    <span>Patient visits</span>
                    <strong>386</strong>
                    <small>42 new patients</small>
                </div>
            </article>
        </section>

        {{-- Report Panels --}}
        <section class="report-content-grid">

            <div class="report-panel">
                <div class="report-panel-heading">
                    <div>
                        <h2>Income and expenses</h2>
                        <p>Monthly financial comparison</p>
                    </div>

                    <span class="report-period-label">
                        September 2026
                    </span>
                </div>

                <div class="report-chart-placeholder">
                    <div class="chart-y-labels">
                        <span>800k</span>
                        <span>600k</span>
                        <span>400k</span>
                        <span>200k</span>
                        <span>0</span>
                    </div>

                    <div class="chart-area">
                        <div class="chart-grid-line"></div>
                        <div class="chart-grid-line"></div>
                        <div class="chart-grid-line"></div>
                        <div class="chart-grid-line"></div>

                        <div class="chart-bars">
                            <div class="chart-group">
                                <div class="chart-bar income-bar" style="height: 74%;"></div>
                                <div class="chart-bar expense-bar" style="height: 30%;"></div>
                                <small>Week 1</small>
                            </div>

                            <div class="chart-group">
                                <div class="chart-bar income-bar" style="height: 86%;"></div>
                                <div class="chart-bar expense-bar" style="height: 38%;"></div>
                                <small>Week 2</small>
                            </div>

                            <div class="chart-group">
                                <div class="chart-bar income-bar" style="height: 67%;"></div>
                                <div class="chart-bar expense-bar" style="height: 28%;"></div>
                                <small>Week 3</small>
                            </div>

                            <div class="chart-group">
                                <div class="chart-bar income-bar" style="height: 92%;"></div>
                                <div class="chart-bar expense-bar" style="height: 35%;"></div>
                                <small>Week 4</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="chart-legend">
                    <span><i class="legend-dot income-dot"></i> Income</span>
                    <span><i class="legend-dot expense-dot"></i> Expenses</span>
                </div>
            </div>

            <div class="report-panel">
                <div class="report-panel-heading">
                    <div>
                        <h2>Expense breakdown</h2>
                        <p>By category</p>
                    </div>
                </div>

                <div class="expense-breakdown">
                    <div class="breakdown-item">
                        <div class="breakdown-label">
                            <span>Rent</span>
                            <strong>PKR 45,000</strong>
                        </div>
                        <div class="breakdown-track">
                            <div class="breakdown-fill rent-fill" style="width: 72%;"></div>
                        </div>
                    </div>

                    <div class="breakdown-item">
                        <div class="breakdown-label">
                            <span>Utilities</span>
                            <strong>PKR 58,000</strong>
                        </div>
                        <div class="breakdown-track">
                            <div class="breakdown-fill utility-fill" style="width: 84%;"></div>
                        </div>
                    </div>

                    <div class="breakdown-item">
                        <div class="breakdown-label">
                            <span>Supplies</span>
                            <strong>PKR 38,400</strong>
                        </div>
                        <div class="breakdown-track">
                            <div class="breakdown-fill supply-fill" style="width: 55%;"></div>
                        </div>
                    </div>

                    <div class="breakdown-item">
                        <div class="breakdown-label">
                            <span>Other</span>
                            <strong>PKR 45,000</strong>
                        </div>
                        <div class="breakdown-track">
                            <div class="breakdown-fill other-fill" style="width: 64%;"></div>
                        </div>
                    </div>
                </div>
            </div>

        </section>

        {{-- Recent Activity --}}
        <section class="report-panel recent-report-panel">
            <div class="report-panel-heading">
                <div>
                    <h2>Recent activity</h2>
                    <p>Latest financial transactions</p>
                </div>

                <a href="#" class="panel-link">View ledger</a>
            </div>

            <div class="report-activity-list">
                <div class="report-activity-row">
                    <div class="activity-icon income-activity">₨</div>
                    <div>
                        <strong>Payment received from Muhammad Ali</strong>
                        <small>21 Sep 2026 · Cash</small>
                    </div>
                    <span class="activity-income">+ PKR 2,500</span>
                </div>

                <div class="report-activity-row">
                    <div class="activity-icon expense-activity">◈</div>
                    <div>
                        <strong>Electricity bill recorded</strong>
                        <small>21 Sep 2026 · Bank transfer</small>
                    </div>
                    <span class="activity-expense">− PKR 8,500</span>
                </div>

                <div class="report-activity-row">
                    <div class="activity-icon income-activity">₨</div>
                    <div>
                        <strong>Payment received from Fatima Bibi</strong>
                        <small>21 Sep 2026 · Bank transfer</small>
                    </div>
                    <span class="activity-income">+ PKR 2,500</span>
                </div>
            </div>
        </section>

    </main>
</div>
@endsection