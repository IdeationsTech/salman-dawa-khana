@extends('layouts.app')

@section('title', 'Prescription Details — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Prescription Detail Sidebar --}}
    <aside class="app-sidebar no-print">
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
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    {{-- Prescription Detail Main Content --}}
    <main class="dashboard-main">

        <div class="prescription-detail-toolbar no-print">
            <a href="/prescriptions" class="back-link">
                ← Back to prescriptions
            </a>

            <div class="prescription-toolbar-actions">
                <button type="button" class="btn btn-outline-secondary">
                    Download PDF
                </button>

                <button type="button" class="btn btn-primary" onclick="window.print()">
                    Print prescription
                </button>
            </div>
        </div>

        <article class="prescription-document">

            {{-- Document Header --}}
            <header class="prescription-document-header">
                <div>
                    <div class="prescription-brand">
                        <div class="prescription-brand-mark">+</div>
                        <div>
                            <h1>Salman Dawa Khana</h1>
                            <p>Patient Care &amp; Herbal Wellness</p>
                        </div>
                    </div>
                </div>

                <div class="prescription-document-meta">
                    <span>PRESCRIPTION</span>
                    <strong>RX-00842</strong>
                    <small>21 September 2026</small>
                </div>
            </header>

            <div class="prescription-document-divider"></div>

            {{-- Patient Information --}}
            <section class="prescription-patient-info">
                <div>
                    <span>Patient name</span>
                    <strong>Muhammad Ali</strong>
                </div>

                <div>
                    <span>Patient ID</span>
                    <strong>P-0001</strong>
                </div>

                <div>
                    <span>Age / Gender</span>
                    <strong>42 years / Male</strong>
                </div>

                <div>
                    <span>Visit type</span>
                    <strong>Follow-up visit</strong>
                </div>
            </section>

            {{-- Prescription Title --}}
            <section class="prescription-document-section">
                <div class="document-section-heading">
                    <span class="document-section-number">Rx</span>
                    <h2>Prescribed nuskha</h2>
                </div>

                <div class="prescription-items-table">
                    <div class="prescription-table-row prescription-table-header">
                        <span>#</span>
                        <span>Item / ingredient</span>
                        <span>Quantity</span>
                        <span>Dosage and frequency</span>
                    </div>

                    <div class="prescription-table-row">
                        <span>01</span>
                        <strong>Herbal mixture</strong>
                        <span>100 g</span>
                        <span>2 teaspoons, twice daily</span>
                    </div>

                    <div class="prescription-table-row">
                        <span>02</span>
                        <strong>Black seed</strong>
                        <span>50 g</span>
                        <span>1 teaspoon, once daily</span>
                    </div>

                    <div class="prescription-table-row">
                        <span>03</span>
                        <strong>Honey blend</strong>
                        <span>250 ml</span>
                        <span>1 tablespoon, after meals</span>
                    </div>

                    <div class="prescription-table-row">
                        <span>04</span>
                        <strong>Herbal tea blend</strong>
                        <span>100 g</span>
                        <span>One cup, at night</span>
                    </div>
                </div>
            </section>

            {{-- Instructions --}}
            <section class="prescription-instructions">
                <h2>Patient instructions</h2>
                <p>
                    Take the prescribed nuskha regularly after meals with warm water.
                    Follow the instructions carefully and return for a follow-up visit
                    as advised.
                </p>
            </section>

            {{-- Footer --}}
            <footer class="prescription-document-footer">
                <div>
                    <span>Prepared by</span>
                    <strong>Dr. Ahmed Khan</strong>
                </div>

                <div class="signature-area">
                    <span>Doctor's signature</span>
                    <div></div>
                </div>
            </footer>

            <div class="prescription-print-note">
                This prescription is issued by Salman Dawa Khana.
            </div>

        </article>

    </main>
</div>
@endsection