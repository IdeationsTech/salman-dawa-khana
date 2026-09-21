@extends('layouts.app')

@section('title', 'Nuskha Templates — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Nuskha Templates Sidebar --}}
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

            <a href="/prescriptions" class="sidebar-link active">
                <span>✎</span>
                <span>Prescriptions</span>
            </a>

            <a href="#" class="sidebar-link">
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

    {{-- Templates Main Content --}}
    <main class="dashboard-main">

        <header class="page-header">
            <div>
                <p class="dashboard-date">Prescription library</p>
                <h1>Nuskha Templates</h1>
                <p class="page-subtitle">
                    Save and reuse commonly prescribed nuskhas.
                </p>
            </div>

            <a href="#" class="btn btn-primary">
                + Create Template
            </a>
        </header>

        {{-- Template Search --}}
        <section class="templates-toolbar">
            <div class="template-search">
                <span>⌕</span>
                <input
                    type="search"
                    placeholder="Search templates by name or ingredient"
                >
            </div>

            <select class="form-select template-category-select">
                <option>All categories</option>
                <option>General wellness</option>
                <option>Digestive care</option>
                <option>Joint care</option>
                <option>Respiratory care</option>
            </select>
        </section>

        {{-- Template Cards --}}
        <section class="template-card-grid">

            <article class="nuskha-template-card">
                <div class="template-card-top">
                    <div class="template-icon">✦</div>
                    <span class="template-menu">⋮</span>
                </div>

                <h2>General Wellness</h2>
                <p class="template-description">
                    A balanced daily wellness nuskha for general use.
                </p>

                <div class="template-meta">
                    <span>4 items</span>
                    <span>Used 86 times</span>
                </div>

                <div class="template-card-actions">
                    <a href="#" class="template-action">View details</a>
                    <button class="btn btn-primary btn-sm">
                        Use template
                    </button>
                </div>
            </article>

            <article class="nuskha-template-card">
                <div class="template-card-top">
                    <div class="template-icon">◈</div>
                    <span class="template-menu">⋮</span>
                </div>

                <h2>Joint Comfort</h2>
                <p class="template-description">
                    Traditional herbal blend for joint comfort and mobility.
                </p>

                <div class="template-meta">
                    <span>5 items</span>
                    <span>Used 64 times</span>
                </div>

                <div class="template-card-actions">
                    <a href="#" class="template-action">View details</a>
                    <button class="btn btn-primary btn-sm">
                        Use template
                    </button>
                </div>
            </article>

            <article class="nuskha-template-card">
                <div class="template-card-top">
                    <div class="template-icon">✿</div>
                    <span class="template-menu">⋮</span>
                </div>

                <h2>Digestive Support</h2>
                <p class="template-description">
                    A herbal combination for digestive support and comfort.
                </p>

                <div class="template-meta">
                    <span>3 items</span>
                    <span>Used 52 times</span>
                </div>

                <div class="template-card-actions">
                    <a href="#" class="template-action">View details</a>
                    <button class="btn btn-primary btn-sm">
                        Use template
                    </button>
                </div>
            </article>

            <article class="nuskha-template-card">
                <div class="template-card-top">
                    <div class="template-icon">☘</div>
                    <span class="template-menu">⋮</span>
                </div>

                <h2>Respiratory Care</h2>
                <p class="template-description">
                    Traditional nuskha for seasonal respiratory support.
                </p>

                <div class="template-meta">
                    <span>4 items</span>
                    <span>Used 39 times</span>
                </div>

                <div class="template-card-actions">
                    <a href="#" class="template-action">View details</a>
                    <button class="btn btn-primary btn-sm">
                        Use template
                    </button>
                </div>
            </article>

            <article class="nuskha-template-card">
                <div class="template-card-top">
                    <div class="template-icon">✧</div>
                    <span class="template-menu">⋮</span>
                </div>

                <h2>Energy &amp; Vitality</h2>
                <p class="template-description">
                    A general vitality and energy support prescription.
                </p>

                <div class="template-meta">
                    <span>6 items</span>
                    <span>Used 31 times</span>
                </div>

                <div class="template-card-actions">
                    <a href="#" class="template-action">View details</a>
                    <button class="btn btn-primary btn-sm">
                        Use template
                    </button>
                </div>
            </article>

            <article class="nuskha-template-card create-template-card">
                <div class="create-template-icon">+</div>
                <h2>Create new template</h2>
                <p>
                    Save a new reusable nuskha for future prescriptions.
                </p>
                <a href="#" class="btn btn-outline-primary">
                    Start creating
                </a>
            </article>

        </section>

    </main>
</div>
@endsection