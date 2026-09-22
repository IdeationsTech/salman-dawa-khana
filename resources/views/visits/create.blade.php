@extends('layouts.app')

@section('title', 'Create Visit — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Visit Sidebar --}}
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

            <a href="/visits" class="sidebar-link active">
                <span>▣</span><span>Visits</span>
            </a>

            <a href="/prescriptions" class="sidebar-link">
                <span>✎</span><span>Prescriptions</span>
            </a>

            <a href="/payments" class="sidebar-link">
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

    {{-- Visit Form Main Content --}}
    <main class="dashboard-main">

        <div class="form-page-header">
            <a href="/visits" class="back-link">
                ← Back to visits
            </a>

            <h1>Create new visit</h1>
            <p>Record a patient visit and start a consultation.</p>
        </div>

        <form action="#" method="POST">
            @csrf

            {{-- Patient Information --}}
            <section class="visit-form-panel">
                <div class="visit-form-heading">
                    <div class="form-section-number">01</div>

                    <div>
                        <h2>Patient information</h2>
                        <p>Select an existing patient for this visit.</p>
                    </div>
                </div>

                <div class="visit-form-grid">
                    <div class="form-field visit-field-wide">
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

                        <small class="form-help">
                            Patient not registered?
                            <a href="/patients/create">Add new patient</a>
                        </small>
                    </div>
                </div>
            </section>

            {{-- Visit Details --}}
            <section class="visit-form-panel">
                <div class="visit-form-heading">
                    <div class="form-section-number">02</div>

                    <div>
                        <h2>Visit details</h2>
                        <p>Enter the basic information for this visit.</p>
                    </div>
                </div>

                <div class="visit-form-grid">
                    <div class="form-field">
                        <label for="visit_date" class="form-label">
                            Visit date <span>*</span>
                        </label>

                        <input
                            type="date"
                            id="visit_date"
                            name="visit_date"
                            class="form-control"
                            value="2026-09-22"
                        >
                    </div>

                    <div class="form-field">
                        <label for="visit_time" class="form-label">
                            Visit time
                        </label>

                        <input
                            type="time"
                            id="visit_time"
                            name="visit_time"
                            class="form-control"
                            value="10:30"
                        >
                    </div>

                    <div class="form-field">
                        <label for="visit_type" class="form-label">
                            Visit type <span>*</span>
                        </label>

                        <select id="visit_type" name="visit_type" class="form-select">
                            <option selected>General visit</option>
                            <option>Follow-up</option>
                            <option>New consultation</option>
                            <option>Prescription refill</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="doctor" class="form-label">
                            Attending doctor
                        </label>

                        <select id="doctor" name="doctor" class="form-select">
                            <option selected>Dr. Ahmed Khan</option>
                            <option>Dr. Sara Ahmed</option>
                        </select>
                    </div>
                </div>
            </section>

            {{-- Visit Notes --}}
            <section class="visit-form-panel">
                <div class="visit-form-heading">
                    <div class="form-section-number">03</div>

                    <div>
                        <h2>Visit notes</h2>
                        <p>Add notes for this patient visit.</p>
                    </div>
                </div>

                <div class="visit-form-grid">
                    <div class="form-field visit-field-wide">
                        <label for="notes" class="form-label">
                            Notes
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            class="form-control"
                            rows="5"
                            placeholder="Write visit notes here..."
                        ></textarea>
                    </div>

                    <div class="form-field">
                        <label for="visit_status" class="form-label">
                            Visit status
                        </label>

                        <select id="visit_status" name="visit_status" class="form-select">
                            <option selected>Completed</option>
                            <option>Follow-up required</option>
                            <option>In progress</option>
                        </select>
                    </div>
                </div>
            </section>

            <div class="visit-form-actions">
                <a href="/visits" class="btn btn-light">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    Save visit
                </button>
            </div>
        </form>

    </main>
</div>
@endsection