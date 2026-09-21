@extends('layouts.app')

@section('title', 'Create Prescription — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Prescription Sidebar --}}
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
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    {{-- Prescription Main Content --}}
    <main class="dashboard-main">

        <div class="form-page-header">
            <a href="/prescriptions" class="back-link">
                ← Back to prescriptions
            </a>

            <h1>Create prescription</h1>
            <p>Prepare a nuskha for a patient visit.</p>
        </div>

        <form action="#" method="POST">
            @csrf

            {{-- Patient and Visit --}}
            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">01</div>

                    <div>
                        <h2>Patient and visit</h2>
                        <p>Select the patient and related visit.</p>
                    </div>
                </div>

                <div class="template-form-grid">
                    <div class="form-field">
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
                    </div>

                    <div class="form-field">
                        <label for="visit" class="form-label">
                            Visit
                        </label>

                        <select id="visit" name="visit" class="form-select">
                            <option selected>21 Sep 2026 — Follow-up</option>
                            <option>20 Sep 2026 — General visit</option>
                            <option>05 Sep 2026 — Previous visit</option>
                        </select>
                    </div>
                </div>
            </section>

            {{-- Template Selection --}}
            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">02</div>

                    <div>
                        <h2>Choose a nuskha</h2>
                        <p>Use a saved template or create a custom prescription.</p>
                    </div>
                </div>

                <div class="prescription-choice-grid">
                    <label class="prescription-choice active">
                        <input type="radio" name="prescription_type" checked>
                        <span class="choice-content">
                            <strong>Use saved template</strong>
                            <small>Select from your nuskha library</small>
                        </span>
                    </label>

                    <label class="prescription-choice">
                        <input type="radio" name="prescription_type">
                        <span class="choice-content">
                            <strong>Create custom nuskha</strong>
                            <small>Add new items manually</small>
                        </span>
                    </label>
                </div>

                <div class="form-field template-selection-field">
                    <label for="template" class="form-label">
                        Saved nuskha template
                    </label>

                    <select id="template" name="template" class="form-select">
                        <option selected>Select a saved template</option>
                        <option>General Wellness — 4 items</option>
                        <option>Joint Comfort — 5 items</option>
                        <option>Digestive Support — 3 items</option>
                        <option>Respiratory Care — 4 items</option>
                        <option>Energy and Vitality — 6 items</option>
                    </select>
                </div>
            </section>

            {{-- Prescription Items --}}
            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">03</div>

                    <div>
                        <h2>Prescription items</h2>
                        <p>Review items and instructions before saving.</p>
                    </div>
                </div>

                <div class="prescription-item-list">
                    <div class="prescription-item-row">
                        <div class="item-number">1</div>

                        <div>
                            <span class="prescription-item-name">
                                Herbal mixture
                            </span>
                            <small>100 g</small>
                        </div>

                        <div>
                            <span class="detail-label">Dosage</span>
                            <strong>2 teaspoons</strong>
                        </div>

                        <div>
                            <span class="detail-label">Frequency</span>
                            <strong>Twice daily</strong>
                        </div>

                        <button type="button" class="remove-item-button">
                            ×
                        </button>
                    </div>

                    <div class="prescription-item-row">
                        <div class="item-number">2</div>

                        <div>
                            <span class="prescription-item-name">
                                Black seed
                            </span>
                            <small>50 g</small>
                        </div>

                        <div>
                            <span class="detail-label">Dosage</span>
                            <strong>1 teaspoon</strong>
                        </div>

                        <div>
                            <span class="detail-label">Frequency</span>
                            <strong>Once daily</strong>
                        </div>

                        <button type="button" class="remove-item-button">
                            ×
                        </button>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-primary add-item-button">
                    + Add custom item
                </button>
            </section>

            {{-- Instructions and Notes --}}
            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">04</div>

                    <div>
                        <h2>Instructions and notes</h2>
                        <p>Add patient-specific instructions.</p>
                    </div>
                </div>

                <div class="template-form-grid">
                    <div class="form-field">
                        <label for="duration" class="form-label">
                            Duration
                        </label>

                        <select id="duration" name="duration" class="form-select">
                            <option selected>Select duration</option>
                            <option>7 days</option>
                            <option>14 days</option>
                            <option>30 days</option>
                            <option>Until follow-up</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="prepared_by" class="form-label">
                            Prepared by
                        </label>

                        <input
                            type="text"
                            id="prepared_by"
                            class="form-control"
                            value="Dr. Ahmed Khan"
                            readonly
                        >
                    </div>

                    <div class="form-field template-field-wide">
                        <label for="notes" class="form-label">
                            Patient instructions
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            class="form-control"
                            rows="3"
                            placeholder="Write instructions for the patient..."
                        ></textarea>
                    </div>
                </div>
            </section>

            <div class="template-form-actions">
                <a href="/prescriptions" class="btn btn-light">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    Save prescription
                </button>
            </div>
        </form>

    </main>
</div>
@endsection