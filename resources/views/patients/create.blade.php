@extends('layouts.app')

@section(
    'title',
    isset($patient)
        ? 'Edit Patient — Salman Dawa Khana'
        : 'Add Patient — Salman Dawa Khana'
)

@section('content')
@php
    $editing = isset($patient) && $patient->exists;

    $birthDate = $editing && $patient->date_of_birth
        ? $patient->date_of_birth->format('Y-m-d')
        : '';

    $maritalStatus = old(
        'marital_status',
        $patient->marital_status ?? ''
    );

    $existingChildren = isset($patient)
        && $patient->has_children !== null
            ? (int) $patient->has_children
            : '';

    $hasChildren = (string) old('has_children', $existingChildren);

    $isMarried = $maritalStatus === 'married';
    $showChildrenCount = $isMarried && $hasChildren === '1';
@endphp

<div class="app-shell">

    {{-- PATIENT FORM — Sidebar --}}
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">+</div>
            <span>Salman Dawa Khana</span>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ url('/dashboard') }}" class="sidebar-link">
                <span>▦</span>
                <span>Dashboard</span>
            </a>

            <a
                href="{{ route('patients.index') }}"
                class="sidebar-link active"
            >
                <span>♙</span>
                <span>Patients</span>
            </a>

            <a href="{{ url('/visits') }}" class="sidebar-link">
                <span>▣</span>
                <span>Visits</span>
            </a>

            <a href="{{ url('/prescriptions') }}" class="sidebar-link">
                <span>✎</span>
                <span>Prescriptions</span>
            </a>

            <a href="{{ url('/payments') }}" class="sidebar-link">
                <span>₨</span>
                <span>Payments</span>
            </a>

            <a href="{{ url('/expenses') }}" class="sidebar-link">
                <span>◈</span>
                <span>Expenses</span>
            </a>

            <div class="sidebar-divider"></div>

            <a href="{{ url('/reports') }}" class="sidebar-link">
                <span>◌</span>
                <span>Reports</span>
            </a>

            <a href="{{ url('/settings') }}" class="sidebar-link">
                <span>⚙</span>
                <span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    {{-- PATIENT FORM — Main Content --}}
    <main class="dashboard-main">

        <div class="form-page-header">
            <div>
                <a
                    href="{{ route('patients.index') }}"
                    class="back-link"
                >
                    ← Back to patients
                </a>

                <h1>
                    {{ $editing ? 'Edit patient' : 'Add new patient' }}
                </h1>

                <p>
                    {{ $editing
                        ? 'Update the patient’s record.'
                        : 'Create a patient record for Salman Dawa Khana.' }}
                </p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Please correct the following:</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ $editing
                ? route('patients.update', $patient)
                : route('patients.store') }}"
            method="POST"
            data-patient-family-form
        >
            @csrf

            @if ($editing)
                @method('PUT')
            @endif

            {{-- PATIENT FORM — Basic Information --}}
            <section class="patient-form-panel">
                <div class="form-section-heading">
                    <div class="form-section-number">01</div>

                    <div>
                        <h2>Basic information</h2>
                        <p>Fields marked with * are required.</p>
                    </div>
                </div>

                <div class="patient-form-grid">

                    <div class="form-field">
                        <label for="full_name" class="form-label">
                            Full Name <span>*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control @error('full_name') is-invalid @enderror"
                            id="full_name"
                            name="full_name"
                            value="{{ old('full_name', $patient->full_name ?? '') }}"
                            placeholder="e.g. Muhammad Ali"
                            maxlength="160"
                            autocomplete="name"
                            required
                        >

                        @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label
                            for="father_or_husband_name"
                            class="form-label"
                        >
                            Father / Husband Name
                        </label>

                        <input
                            type="text"
                            class="form-control @error('father_or_husband_name') is-invalid @enderror"
                            id="father_or_husband_name"
                            name="father_or_husband_name"
                            value="{{ old('father_or_husband_name', $patient->father_or_husband_name ?? '') }}"
                            placeholder="Enter name"
                            maxlength="160"
                        >

                        @error('father_or_husband_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="phone" class="form-label">
                            Phone Number <span>*</span>
                        </label>

                        <input
                            type="tel"
                            class="form-control @error('phone') is-invalid @enderror"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $patient->phone ?? '') }}"
                            placeholder="+971 50 000 0000"
                            maxlength="30"
                            autocomplete="tel"
                            required
                        >

                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="emirates_id" class="form-label">
                            Emirates ID <span>*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control @error('emirates_id') is-invalid @enderror"
                            id="emirates_id"
                            name="emirates_id"
                            value="{{ old('emirates_id', $patient->emirates_id ?? '') }}"
                            placeholder="784-XXXX-XXXXXXX-X"
                            maxlength="18"
                            pattern="784[0-9]{12}|784-[0-9]{4}-[0-9]{7}-[0-9]"
                            title="Enter 15 digits starting with 784, with or without hyphens."
                            required
                        >

                        @error('emirates_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="gender" class="form-label">
                            Gender <span>*</span>
                        </label>

                        <select
                            class="form-select @error('gender') is-invalid @enderror"
                            id="gender"
                            name="gender"
                            required
                        >
                            <option value="">Select gender</option>

                            @foreach (['Male', 'Female', 'Other'] as $gender)
                                <option
                                    value="{{ $gender }}"
                                    @selected(
                                        strtolower(old('gender', $patient->gender ?? ''))
                                        === strtolower($gender)
                                    )
                                >
                                    {{ $gender }}
                                </option>
                            @endforeach
                        </select>

                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="date_of_birth" class="form-label">
                            Date of Birth <span>*</span>
                        </label>

                        <input
                            type="date"
                            class="form-control @error('date_of_birth') is-invalid @enderror"
                            id="date_of_birth"
                            name="date_of_birth"
                            value="{{ old('date_of_birth', $birthDate) }}"
                            max="{{ now()->format('Y-m-d') }}"
                            autocomplete="bday"
                            required
                        >

                        @error('date_of_birth')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- PATIENT FORM — Family Details --}}
            <section class="patient-form-panel">
                <div class="form-section-heading">
                    <div class="form-section-number">02</div>

                    <div>
                        <h2>Family details</h2>
                        <p>Select marital status and applicable family information.</p>
                    </div>
                </div>

                <div class="patient-form-grid">

                    <div class="form-field">
                        <label for="marital_status" class="form-label">
                            Marital Status <span>*</span>
                        </label>

                        <select
                            class="form-select @error('marital_status') is-invalid @enderror"
                            id="marital_status"
                            name="marital_status"
                            required
                        >
                            <option value="">Select marital status</option>

                            <option
                                value="unmarried"
                                @selected($maritalStatus === 'unmarried')
                            >
                                Unmarried
                            </option>

                            <option
                                value="married"
                                @selected($maritalStatus === 'married')
                            >
                                Married
                            </option>
                        </select>

                        @error('marital_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div
                        class="form-field {{ $isMarried ? '' : 'd-none' }}"
                        data-children-question
                    >
                        <fieldset>
                            <legend class="form-label fs-6">
                                Has Children? <span>*</span>
                            </legend>

                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input
                                        type="radio"
                                        class="form-check-input"
                                        id="has_children_yes"
                                        name="has_children"
                                        value="1"
                                        @checked($hasChildren === '1')
                                        @required($isMarried)
                                        @disabled(!$isMarried)
                                    >

                                    <label
                                        class="form-check-label"
                                        for="has_children_yes"
                                    >
                                        Yes
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input
                                        type="radio"
                                        class="form-check-input"
                                        id="has_children_no"
                                        name="has_children"
                                        value="0"
                                        @checked($hasChildren === '0')
                                        @required($isMarried)
                                        @disabled(!$isMarried)
                                    >

                                    <label
                                        class="form-check-label"
                                        for="has_children_no"
                                    >
                                        No
                                    </label>
                                </div>
                            </div>

                            @error('has_children')
                                <div class="text-danger small mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        </fieldset>
                    </div>

                    <div
                        class="form-field {{ $showChildrenCount ? '' : 'd-none' }}"
                        data-children-count-field
                    >
                        <label for="children_count" class="form-label">
                            Number of Children <span>*</span>
                        </label>

                        <input
                            type="number"
                            class="form-control @error('children_count') is-invalid @enderror"
                            id="children_count"
                            name="children_count"
                            value="{{ old('children_count', $patient->children_count ?? '') }}"
                            placeholder="Enter number"
                            min="1"
                            max="65535"
                            step="1"
                            @required($showChildrenCount)
                            @disabled(!$showChildrenCount)
                        >

                        @error('children_count')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- PATIENT FORM — Contact Details --}}
            <section class="patient-form-panel">
                <div class="form-section-heading">
                    <div class="form-section-number">03</div>

                    <div>
                        <h2>Contact details</h2>
                        <p>Add the patient's residential address.</p>
                    </div>
                </div>

                <div class="patient-form-grid">
                    <div class="form-field form-field-wide">
                        <label for="address" class="form-label">
                            Address
                        </label>

                        <input
                            type="text"
                            class="form-control @error('address') is-invalid @enderror"
                            id="address"
                            name="address"
                            value="{{ old('address', $patient->address ?? '') }}"
                            placeholder="Enter residential address, including city"
                            maxlength="255"
                            autocomplete="street-address"
                        >

                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- PATIENT FORM — Additional Notes --}}
            <section class="patient-form-panel">
                <div class="form-section-heading">
                    <div class="form-section-number">04</div>

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
                        class="form-control @error('notes') is-invalid @enderror"
                        id="notes"
                        name="notes"
                        rows="4"
                        placeholder="Write any additional notes here..."
                    >{{ old('notes', $patient->notes ?? '') }}</textarea>

                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </section>

            {{-- PATIENT FORM — Actions --}}
            <div class="patient-form-actions">
                <a
                    href="{{ route('patients.index') }}"
                    class="btn btn-light"
                >
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    {{ $editing ? 'Update Patient' : 'Save Patient' }}
                </button>
            </div>
        </form>
    </main>
</div>
@endsection