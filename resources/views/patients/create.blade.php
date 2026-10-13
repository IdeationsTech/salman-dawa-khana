@extends('layouts.app')

@section('title', isset($patient) ? 'Edit Patient' : 'Add Patient')

@section('content')
@php
    $editing = isset($patient) && $patient->exists;
    $clinic = auth()->user()->clinic;

    $isPakistan = $idCountry === 'PK';
    $idLabel = $isPakistan ? 'CNIC' : 'Emirates ID';

    $existingId = $editing
        ? ($patient->national_id_number ?: $patient->emirates_id)
        : '';

    $birthDate = $editing && $patient->date_of_birth
        ? $patient->date_of_birth->format('Y-m-d')
        : '';

    $maritalStatus = old(
        'marital_status',
        $patient->marital_status ?? ''
    );

    $existingChildren = isset($patient) && $patient->has_children !== null
        ? (int) $patient->has_children
        : '';

    $hasChildren = (string) old('has_children', $existingChildren);
    $isMarried = $maritalStatus === 'married';
    $showChildrenCount = $isMarried && $hasChildren === '1';

    $navigation = [
        ['route' => 'dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
        ['route' => 'patients.index', 'icon' => 'bi-person-vcard', 'label' => 'Patients'],
        ['route' => 'visits.index', 'icon' => 'bi-calendar-check', 'label' => 'Visits'],
        ['route' => 'prescriptions.index', 'icon' => 'bi-prescription', 'label' => 'Prescriptions'],
        ['route' => 'payments.index', 'icon' => 'bi-cash-coin', 'label' => 'Payments'],
        ['route' => 'expenses.index', 'icon' => 'bi-receipt', 'label' => 'Expenses'],
        ['route' => 'reports.index', 'icon' => 'bi-bar-chart-line', 'label' => 'Reports'],
        ['route' => 'settings.index', 'icon' => 'bi-gear', 'label' => 'Settings'],
    ];
@endphp

<div class="app-shell">

    {{-- Patient Form — Sidebar --}}
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">
                @if($clinic->logo_path)
                    <img
                        src="{{ asset('storage/' . $clinic->logo_path) }}"
                        alt="{{ $clinic->name }} logo"
                        class="settings-logo-image"
                    >
                @else
                    <i class="bi bi-hospital app-icon" aria-hidden="true"></i>
                @endif
            </div>

            <span>{{ $clinic->name }}</span>
        </div>

        <nav class="sidebar-nav">
            @foreach($navigation as $item)
                @if($item['route'] === 'reports.index')
                    <div class="sidebar-divider"></div>
                @endif

                <a
                    href="{{ route($item['route']) }}"
                    class="sidebar-link {{ $item['route'] === 'patients.index' ? 'active' : '' }}"
                    @if($item['route'] === 'patients.index')
                        aria-current="page"
                    @endif
                >
                    <span>
                        <i
                            class="bi {{ $item['icon'] }} app-icon"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="sidebar-footer">
            <span class="status-dot"></span>
            System online
        </div>
    </aside>

    {{-- Patient Form — Main Content --}}
    <main class="dashboard-main">
        <div class="form-page-header">
            <div>
                <a href="{{ route('patients.index') }}" class="back-link">
                    <i class="bi bi-arrow-left app-icon" aria-hidden="true"></i>
                    Back to patients
                </a>

                <h1>{{ $editing ? 'Edit patient' : 'Add new patient' }}</h1>

                <p>
                    {{ $editing
                        ? 'Update the patient’s record.'
                        : 'Create a patient record for ' . $clinic->name . '.' }}
                </p>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Please correct the following:</strong>

                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
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

            @if($editing)
                @method('PUT')
            @endif

            {{-- Patient Form — Basic Information --}}
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
                            id="full_name"
                            name="full_name"
                            class="form-control @error('full_name') is-invalid @enderror"
                            value="{{ old('full_name', $patient->full_name ?? '') }}"
                            maxlength="160"
                            autocomplete="name"
                            required
                        >

                        @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="father_or_husband_name" class="form-label">
                            Father / Husband Name
                        </label>

                        <input
                            type="text"
                            id="father_or_husband_name"
                            name="father_or_husband_name"
                            class="form-control"
                            value="{{ old(
                                'father_or_husband_name',
                                $patient->father_or_husband_name ?? ''
                            ) }}"
                            maxlength="160"
                        >
                    </div>

                    {{-- Patient Form — International Phone --}}
                    <div class="form-field">
                        <label for="phone" class="form-label">
                            Phone Number <span>*</span>
                        </label>

                        <x-phone-input
                            name="phone"
                            id="phone"
                            :value="$patient->phone ?? ''"
                            :required="true"
                        />
                    </div>

                    {{-- Patient Form — Country-Specific National ID --}}
                    <div class="form-field">
                        <label for="national_id_number" class="form-label">
                            {{ $idLabel }} <span>*</span>
                        </label>

                        <input
                            type="text"
                            id="national_id_number"
                            name="national_id_number"
                            class="form-control @error('national_id_number') is-invalid @enderror"
                            value="{{ old('national_id_number', $existingId) }}"
                            placeholder="{{ $isPakistan
                                ? '12345-1234567-1'
                                : '784-1234-1234567-1' }}"
                            maxlength="{{ $isPakistan ? 15 : 18 }}"
                            pattern="{{ $isPakistan
                                ? '[0-9]{13}|[0-9]{5}-[0-9]{7}-[0-9]'
                                : '784[0-9]{12}|784-[0-9]{4}-[0-9]{7}-[0-9]' }}"
                            title="{{ $isPakistan
                                ? 'Enter a 13-digit CNIC.'
                                : 'Enter a 15-digit Emirates ID starting with 784.' }}"
                            inputmode="numeric"
                            autocomplete="off"
                            data-national-id
                            data-id-country="{{ $idCountry }}"
                            required
                        >

                        <small class="text-muted">
                            {{ $isPakistan
                                ? 'Pakistan · 13 digits'
                                : 'United Arab Emirates · 15 digits starting with 784' }}
                        </small>

                        @error('national_id_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="gender" class="form-label">
                            Gender <span>*</span>
                        </label>

                        <select
                            id="gender"
                            name="gender"
                            class="form-select"
                            required
                        >
                            <option value="">Select gender</option>

                            @foreach(['Male', 'Female', 'Other'] as $gender)
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
                    </div>

                    <div class="form-field">
                        <label for="date_of_birth" class="form-label">
                            Date of Birth <span>*</span>
                        </label>

                        <input
                            type="date"
                            id="date_of_birth"
                            name="date_of_birth"
                            class="form-control"
                            value="{{ old('date_of_birth', $birthDate) }}"
                            max="{{ now()->format('Y-m-d') }}"
                            autocomplete="bday"
                            required
                        >
                    </div>
                </div>
            </section>

            {{-- Patient Form — Family Details --}}
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
                            id="marital_status"
                            name="marital_status"
                            class="form-select"
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
                                @foreach(['1' => 'Yes', '0' => 'No'] as $value => $label)
                                    <div class="form-check">
                                        <input
                                            type="radio"
                                            id="{{ $value == '1' ? 'has_children_yes' : 'has_children_no' }}"
                                            name="has_children"
                                            class="form-check-input"
                                            value="{{ $value }}"
                                            @checked($hasChildren === (string) $value)
                                            @required($isMarried)
                                            @disabled(!$isMarried)
                                        >

                                        <label
                                            class="form-check-label"
                                            for="{{ $value == '1' ? 'has_children_yes' : 'has_children_no' }}"
                                        >
                                            {{ $label }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
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
                            id="children_count"
                            name="children_count"
                            class="form-control"
                            value="{{ old('children_count', $patient->children_count ?? '') }}"
                            min="1"
                            max="65535"
                            step="1"
                            @required($showChildrenCount)
                            @disabled(!$showChildrenCount)
                        >
                    </div>
                </div>
            </section>

            {{-- Patient Form — Contact Details --}}
            <section class="patient-form-panel">
                <div class="form-section-heading">
                    <div class="form-section-number">03</div>

                    <div>
                        <h2>Contact details</h2>
                        <p>Add the patient’s residential address.</p>
                    </div>
                </div>

                <div class="patient-form-grid">
                    <div class="form-field form-field-wide">
                        <label for="address" class="form-label">Address</label>

                        <input
                            type="text"
                            id="address"
                            name="address"
                            class="form-control"
                            value="{{ old('address', $patient->address ?? '') }}"
                            maxlength="255"
                            autocomplete="street-address"
                        >
                    </div>
                </div>
            </section>

            {{-- Patient Form — Additional Notes --}}
            <section class="patient-form-panel">
                <div class="form-section-heading">
                    <div class="form-section-number">04</div>

                    <div>
                        <h2>Additional notes</h2>
                        <p>Add any useful information about the patient.</p>
                    </div>
                </div>

                <div class="form-field">
                    <label for="notes" class="form-label">Notes</label>

                    <textarea
                        id="notes"
                        name="notes"
                        class="form-control"
                        rows="4"
                    >{{ old('notes', $patient->notes ?? '') }}</textarea>
                </div>
            </section>

            {{-- Patient Form — Actions --}}
            <div class="patient-form-actions">
                <a href="{{ route('patients.index') }}" class="btn btn-light">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2-circle app-icon" aria-hidden="true"></i>
                    {{ $editing ? 'Update Patient' : 'Save Patient' }}
                </button>
            </div>
        </form>
    </main>
</div>
@endsection