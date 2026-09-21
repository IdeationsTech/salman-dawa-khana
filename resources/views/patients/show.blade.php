@extends('layouts.app')

@section('title', 'Patient Profile — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Patient Profile Sidebar --}}
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

            <a href="/patients" class="sidebar-link active">
                <span>♙</span>
                <span>Patients</span>
            </a>

            <a href="#" class="sidebar-link">
                <span>▣</span>
                <span>Visits</span>
            </a>

            <a href="#" class="sidebar-link">
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

    {{-- Patient Profile Main Content --}}
    <main class="dashboard-main">

        <div class="form-page-header">
            <a href="/patients" class="back-link">← Back to patients</a>
        </div>

        {{-- Patient Header --}}
        <section class="patient-profile-header">
            <div class="patient-profile-identity">
                <div class="large-patient-avatar">MA</div>

                <div>
                    <div class="patient-id-label">PATIENT ID: P-0001</div>
                    <h1>Muhammad Ali</h1>
                    <p>Male · 42 years · Dubai, UAE</p>
                </div>
            </div>

            <div class="patient-profile-actions">
                <a href="#" class="btn btn-outline-secondary">
                    Edit patient
                </a>

                <a href="#" class="btn btn-primary">
                    + New visit
                </a>
            </div>
        </section>

        {{-- Patient Summary --}}
        <section class="patient-profile-grid">

            <div class="patient-profile-main">

                {{-- Personal Information --}}
                <div class="patient-detail-panel">
                    <div class="profile-panel-heading">
                        <div>
                            <h2>Personal information</h2>
                            <p>Basic patient details</p>
                        </div>

                        <span class="status-badge active-badge">
                            Active
                        </span>
                    </div>

                    <div class="detail-grid">
                        <div>
                            <span class="detail-label">Full name</span>
                            <strong>Muhammad Ali</strong>
                        </div>

                        <div>
                            <span class="detail-label">Phone number</span>
                            <strong>050 123 4567</strong>
                        </div>

                        <div>
                            <span class="detail-label">Emirates ID</span>
                            <strong>784-1990-1234567-1</strong>
                        </div>

                        <div>
                            <span class="detail-label">Date of birth</span>
                            <strong>14 March 1984</strong>
                        </div>

                        <div>
                            <span class="detail-label">Address</span>
                            <strong>Al Nahda, Dubai</strong>
                        </div>

                        <div>
                            <span class="detail-label">Registered on</span>
                            <strong>12 January 2026</strong>
                        </div>
                    </div>
                </div>

                {{-- Visit History --}}
                <div class="patient-detail-panel">
                    <div class="profile-panel-heading">
                        <div>
                            <h2>Visit history</h2>
                            <p>Recent visits and prescriptions</p>
                        </div>

                        <a href="#" class="panel-link">View all</a>
                    </div>

                    <div class="visit-history-list">
                        <div class="visit-history-item">
                            <div class="visit-date">
                                <strong>21</strong>
                                <span>SEP</span>
                            </div>

                            <div class="visit-info">
                                <strong>Follow-up visit</strong>
                                <span>Prescription prepared · Dr. Ahmed Khan</span>
                            </div>

                            <span class="visit-amount">PKR 2,500</span>
                        </div>

                        <div class="visit-history-item">
                            <div class="visit-date">
                                <strong>05</strong>
                                <span>SEP</span>
                            </div>

                            <div class="visit-info">
                                <strong>General consultation</strong>
                                <span>Prescription prepared · Dr. Ahmed Khan</span>
                            </div>

                            <span class="visit-amount">PKR 2,500</span>
                        </div>

                        <div class="visit-history-item">
                            <div class="visit-date">
                                <strong>18</strong>
                                <span>AUG</span>
                            </div>

                            <div class="visit-info">
                                <strong>First visit</strong>
                                <span>New patient registration</span>
                            </div>

                            <span class="visit-amount">PKR 2,500</span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Account Summary --}}
            <aside class="patient-profile-side">

                <div class="patient-detail-panel account-summary-panel">
                    <div class="profile-panel-heading">
                        <div>
                            <h2>Account summary</h2>
                            <p>Patient payment balance</p>
                        </div>
                    </div>

                    <div class="account-total">
                        <span>Outstanding balance</span>
                        <strong>PKR 1,500</strong>
                    </div>

                    <div class="account-row">
                        <span>Total billed</span>
                        <strong>PKR 7,500</strong>
                    </div>

                    <div class="account-row">
                        <span>Total paid</span>
                        <strong>PKR 6,000</strong>
                    </div>

                    <button class="btn btn-primary w-100 mt-3">
                        Record payment
                    </button>
                </div>

                <div class="patient-detail-panel">
                    <div class="profile-panel-heading">
                        <div>
                            <h2>Quick actions</h2>
                        </div>
                    </div>

                    <div class="profile-quick-actions">
                        <a href="#">View prescriptions <span>›</span></a>
                        <a href="#">Create new visit <span>›</span></a>
                        <a href="#">Print patient summary <span>›</span></a>
                    </div>
                </div>

            </aside>

        </section>

    </main>
</div>
@endsection