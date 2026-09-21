@extends('layouts.app')

@section('title', 'Create Nuskha Template — Salman Dawa Khana')

@section('content')
<div class="app-shell">

    {{-- Template Form Sidebar --}}
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

    {{-- Template Form Main Content --}}
    <main class="dashboard-main">

        <div class="form-page-header">
            <a href="/prescriptions/templates" class="back-link">
                ← Back to templates
            </a>

            <h1>Create new nuskha template</h1>
            <p>Save a reusable prescription for future patient visits.</p>
        </div>

        <form action="#" method="POST">
            @csrf

            {{-- Basic Information --}}
            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">01</div>

                    <div>
                        <h2>Basic information</h2>
                        <p>Give this reusable nuskha a clear name and category.</p>
                    </div>
                </div>

                <div class="template-form-grid">
                    <div class="form-field">
                        <label for="template_name" class="form-label">
                            Template name <span>*</span>
                        </label>

                        <input
                            type="text"
                            id="template_name"
                            name="template_name"
                            class="form-control"
                            placeholder="e.g. Joint Comfort"
                        >
                    </div>

                    <div class="form-field">
                        <label for="category" class="form-label">
                            Category
                        </label>

                        <select id="category" name="category" class="form-select">
                            <option selected>Select category</option>
                            <option>General wellness</option>
                            <option>Digestive care</option>
                            <option>Joint care</option>
                            <option>Respiratory care</option>
                            <option>Energy and vitality</option>
                        </select>
                    </div>

                    <div class="form-field template-field-wide">
                        <label for="description" class="form-label">
                            Description
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            class="form-control"
                            rows="3"
                            placeholder="Describe the purpose of this nuskha..."
                        ></textarea>
                    </div>
                </div>
            </section>

            {{-- Nuskha Items --}}
            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">02</div>

                    <div>
                        <h2>Nuskha items</h2>
                        <p>Add the ingredients or items included in this template.</p>
                    </div>
                </div>

                <div class="nuskha-item-list">

                    <div class="nuskha-item-row">
                        <div class="item-number">1</div>

                        <div class="form-field">
                            <label class="form-label">Item name</label>
                            <input
                                type="text"
                                name="items[]"
                                class="form-control"
                                value="Herbal mixture"
                            >
                        </div>

                        <div class="form-field item-quantity">
                            <label class="form-label">Quantity</label>
                            <input
                                type="text"
                                name="quantities[]"
                                class="form-control"
                                value="100 g"
                            >
                        </div>

                        <button type="button" class="remove-item-button">
                            ×
                        </button>
                    </div>

                    <div class="nuskha-item-row">
                        <div class="item-number">2</div>

                        <div class="form-field">
                            <label class="form-label">Item name</label>
                            <input
                                type="text"
                                name="items[]"
                                class="form-control"
                                value="Black seed"
                            >
                        </div>

                        <div class="form-field item-quantity">
                            <label class="form-label">Quantity</label>
                            <input
                                type="text"
                                name="quantities[]"
                                class="form-control"
                                value="50 g"
                            >
                        </div>

                        <button type="button" class="remove-item-button">
                            ×
                        </button>
                    </div>

                </div>

                <button type="button" class="btn btn-outline-primary add-item-button">
                    + Add another item
                </button>
            </section>

            {{-- Instructions --}}
            <section class="template-form-panel">
                <div class="template-form-heading">
                    <div class="form-section-number">03</div>

                    <div>
                        <h2>Usage instructions</h2>
                        <p>Define how the patient should use this nuskha.</p>
                    </div>
                </div>

                <div class="template-form-grid">
                    <div class="form-field">
                        <label for="dosage" class="form-label">
                            Dosage / quantity per use
                        </label>

                        <input
                            type="text"
                            id="dosage"
                            name="dosage"
                            class="form-control"
                            placeholder="e.g. 2 teaspoons"
                        >
                    </div>

                    <div class="form-field">
                        <label for="frequency" class="form-label">
                            Frequency
                        </label>

                        <select id="frequency" name="frequency" class="form-select">
                            <option selected>Select frequency</option>
                            <option>Once daily</option>
                            <option>Twice daily</option>
                            <option>Three times daily</option>
                            <option>As directed</option>
                        </select>
                    </div>

                    <div class="form-field template-field-wide">
                        <label for="instructions" class="form-label">
                            Additional instructions
                        </label>

                        <textarea
                            id="instructions"
                            name="instructions"
                            class="form-control"
                            rows="3"
                            placeholder="e.g. Take after meals with warm water..."
                        ></textarea>
                    </div>
                </div>
            </section>

            <div class="template-form-actions">
                <a href="/prescriptions/templates" class="btn btn-light">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    Save template
                </button>
            </div>
        </form>

    </main>
</div>
@endsection