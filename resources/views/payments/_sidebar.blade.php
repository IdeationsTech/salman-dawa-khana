<aside class="app-sidebar no-print">
    <div class="sidebar-brand">
        <div class="sidebar-logo">+</div>
        <span>{{ $clinic->name }}</span>
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

        <a href="{{ route('payments.index') }}" class="sidebar-link active">
            <span>▤</span><span>Payments</span>
        </a>

        <a href="{{ route('expenses.index') }}" class="sidebar-link">
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