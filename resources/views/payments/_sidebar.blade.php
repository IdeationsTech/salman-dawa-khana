<aside class="app-sidebar no-print">
    <div class="sidebar-brand">
        <div class="sidebar-logo">+</div>
        <span>{{ $clinic->name }}</span>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="sidebar-link">
            <span><i class="bi bi-speedometer2 app-icon" aria-hidden="true"></i></span><span>Dashboard</span>
        </a>

        <a href="{{ route('patients.index') }}" class="sidebar-link">
            <span><i class="bi bi-person-vcard app-icon" aria-hidden="true"></i></span><span>Patients</span>
        </a>

        <a href="{{ route('visits.index') }}" class="sidebar-link">
            <span><i class="bi bi-calendar-check app-icon" aria-hidden="true"></i></span><span>Visits</span>
        </a>

        <a href="{{ route('prescriptions.index') }}" class="sidebar-link">
            <span><i class="bi bi-prescription app-icon" aria-hidden="true"></i></span><span>Prescriptions</span>
        </a>

        <a href="{{ route('payments.index') }}" class="sidebar-link active">
            <span><i class="bi bi-cash-coin app-icon" aria-hidden="true"></i></span><span>Payments</span>
        </a>

        <a href="{{ route('expenses.index') }}" class="sidebar-link">
            <span><i class="bi bi-receipt app-icon" aria-hidden="true"></i></span><span>Expenses</span>
        </a>

        <div class="sidebar-divider"></div>

        <a href="{{ route('reports.index') }}" class="sidebar-link">
            <span><i class="bi bi-bar-chart-line app-icon" aria-hidden="true"></i></span><span>Reports</span>
        </a>

        <a href="{{ route('settings.index') }}" class="sidebar-link">
            <span><i class="bi bi-gear app-icon" aria-hidden="true"></i></span><span>Settings</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <span class="status-dot"></span>
        System online
    </div>
</aside>