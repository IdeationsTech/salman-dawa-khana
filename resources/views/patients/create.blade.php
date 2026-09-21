@extends('layouts.app')

@section('title', 'Add Patient — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Add Patient Sidebar --}}
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

    {{-- Add Patient Main Content --}}
    <main class="dashboard-main">

        <div class="form-page-header">
            <div>
                <a href="/patients" class="back-link">← Back to patients</a>
                <h1>Add new patient</h1>
                <p>Create a patient record for Salman Dawa Khana.</p>
            </div>
        </div>

        <form action="#" method="POST">
            @csrf

            <section class="patient-form-panel">
                <div class="form-section-heading">
                    <div class="form-section-number">01</div>
                    <div>
                        <h2>Basic information</h2>
                        <p>Enter the patient's personal details.</p>
                    </div>
                </div>

                <div class="patient-form-grid">
                    <div class="form-field">
                        <label for="first_name" class="form-label">
                            First Name <span>*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="first_name"
                            name="first_name"
                            placeholder="e.g. Muhammad"
                        >
                    </div>

                    <div class="form-field">
                        <label for="last_name" class="form-label">
                            Last Name
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="last_name"
                            name="last_name"
                            placeholder="e.g. Ali"
                        >
                    </div>

                    <div class="form-field">
                        <label for="phone" class="form-label">
                            Phone Number <span>*</span>
                        </label>

                        <input
                            type="tel"
                            class="form-control"
                            id="phone"
                            name="phone"
                            placeholder="+971 50 000 0000"
                        >
                    </div>

                    <div class="form-field">
                        <label for="emirates_id" class="form-label">
                            Emirates ID
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="emirates_id"
                            name="emirates_id"
                            placeholder="784-XXXX-XXXXXXX-X"
                        >
                    </div>

                    <div class="form-field">
                        <label for="gender" class="form-label">
                            Gender
                        </label>

                        <select class="form-select" id="gender" name="gender">
                            <option selected>Select gender</option>
                            <option>Male</option>
                            <option>Female</option>
                            <option>Other</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="date_of_birth" class="form-label">
                            Date of Birth
                        </label>

                        <input
                            type="date"
                            class="form-control"
                            id="date_of_birth"
                            name="date_of_birth"
                        >
                    </div>
                </div>
            </section>

            <section class="patient-form-panel">
                <div class="form-section-heading">
                    <div class="form-section-number">02</div>
                    <div>
                        <h2>Contact details</h2>
                        <p>Add optional address and contact information.</p>
                    </div>
                </div>

                <div class="patient-form-grid">
                    <div class="form-field form-field-wide">
                        <label for="address" class="form-label">
                            Address
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="address"
                            name="address"
                            placeholder="Enter residential address"
                        >
                    </div>

                    <div class="form-field">
                        <label for="city" class="form-label">
                            City
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="city"
                            name="city"
                            placeholder="e.g. Dubai"
                        >
                    </div>

                    <div class="form-field">
                        <label for="emergency_contact" class="form-label">
                            Emergency Contact
                        </label>

                        <input
                            type="tel"
                            class="form-control"
                            id="emergency_contact"
                            name="emergency_contact"
                            placeholder="+971 50 000 0000"
                        >
                    </div>
                </div>
            </section>

            <section class="patient-form-panel">
                <div class="form-section-heading">
                    <div class="form-section-number">03</div>
                    <div>
                        <h2>Additional notes</h2>
                        <p>Add any useful information about the patient.</p>
                    </div>
                </div>

                <div class="form-field">
                    <label for="notes" class="form-label">
                        Notes
                    </label>

                    <textarea
                        class="form-control"
                        id="notes"
                        name="notes"
                        rows="4"
                        placeholder="Write any additional notes here..."
                    ></textarea>
                </div>
            </section>

            <div class="patient-form-actions">
                <a href="/patients" class="btn btn-light">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    Save Patient
                </button>
            </div>
        </form>

    </main>
</div>
@endsection