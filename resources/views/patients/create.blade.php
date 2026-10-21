@extends('layouts.app')

@section('title', isset($patient) ? 'Edit Patient' : 'Add Patient')

@section('content')
@php
    $editing = isset($patient) && $patient->exists;
    $clinic = auth()->user()->clinic;

    $isPakistan = $idCountry === 'PK';
    $isOtherId = $idCountry === 'ZZ';

    $idLabel = match ($idCountry) {
        'PK' => __('CNIC'),
        'AE' => __('Emirates ID'),
        default => 'Patient ID / Passport',
    };

    $existingId = $editing
        ? ($patient->national_id_number ?: $patient->emirates_id)
        : '';

    $birthDate = $editing && $patient->date_of_birth
        ? $patient->date_of_birth->format('Y-m-d')
        : '';

    $maritalStatus = old('marital_status', $patient->marital_status ?? '');

    $existingChildren = isset($patient) && $patient->has_children !== null
        ? (int) $patient->has_children
        : '';

    $hasChildren = (string) old('has_children', $existingChildren);
    $isMarried = $maritalStatus === 'married';
    $showChildrenCount = $isMarried && $hasChildren === '1';
@endphp

<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <div class="form-page-header">
            <div>
                <a href="{{ route('patients.index') }}" class="back-link">
                    <i class="bi bi-arrow-left app-icon" aria-hidden="true"></i>
                    {{ __('Back to patients') }}
                </a>

                <h1>{{ $editing ? __('Edit patient') : __('Add new patient') }}</h1>
                <p>
                    {{ $editing
                        ? 'Update the patient’s record.'
                        : 'Create a patient record for ' . $clinic->name . '.' }}
                </p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>{{ __('Please correct the following:') }}</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ __($error) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ $editing ? route('patients.update', $patient) : route('patients.store') }}"
            method="POST"
            data-patient-family-form
        >
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <section class="patient-form-panel">
                <div class="form-section-heading">
                    <div class="form-section-number">01</div>
                    <div>
                        <h2>{{ __('Basic information') }}</h2>
                        <p>{{ __('Fields marked with * are required.') }}</p>
                    </div>
                </div>

                <div class="patient-form-grid">
                    <div class="form-field">
                        <label for="full_name" class="form-label">{{ __('Full Name') }} *</label>
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
                            <div class="invalid-feedback">{{ __($message) }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="father_or_husband_name" class="form-label">
                            {{ __('Father / Husband Name') }}
                        </label>
                        <input
                            type="text"
                            id="father_or_husband_name"
                            name="father_or_husband_name"
                            class="form-control"
                            value="{{ old('father_or_husband_name', $patient->father_or_husband_name ?? '') }}"
                            maxlength="160"
                        >
                    </div>

                    <div class="form-field">
                        <label for="phone" class="form-label">{{ __('Phone Number') }} *</label>
                        <x-phone-input
                            name="phone"
                            id="phone"
                            :value="$patient->phone ?? ''"
                            :required="true"
                        />
                    </div>

                    <div class="form-field">
                        <label for="national_id_number" class="form-label">
                            {{ __($idLabel) }} (optional)
                        </label>

                        <input
                            type="text"
                            id="national_id_number"
                            name="national_id_number"
                            class="form-control @error('national_id_number') is-invalid @enderror"
                            value="{{ old('national_id_number', $existingId) }}"
                            @if ($isOtherId)
                                placeholder="{{ __('Enter national ID, passport or another ID') }}"
                                maxlength="40"
                                inputmode="text"
                            @else
                                placeholder="{{ $isPakistan ? '12345-1234567-1' : '784-1234-1234567-1' }}"
                                maxlength="{{ $isPakistan ? 15 : 18 }}"
                                pattern="{{ $isPakistan ? '[0-9]{13}|[0-9]{5}-[0-9]{7}-[0-9]' : '784[0-9]{12}|784-[0-9]{4}-[0-9]{7}-[0-9]' }}"
                                title="{{ $isPakistan ? __('Enter a 13-digit CNIC.') : __('Enter a 15-digit Emirates ID starting with 784.') }}"
                                inputmode="numeric"
                                data-national-id
                                data-id-country="{{ $idCountry }}"
                            @endif
                            autocomplete="off"
                        >

                        <small class="text-muted">
                            {{ match ($idCountry) {
                                'PK' => 'Pakistan · 13 digits',
                                'AE' => 'United Arab Emirates · 15 digits starting with 784',
                                default => 'Other · Maximum 40 characters; letters, numbers and hyphens allowed.',
                            } }}
                        </small>

                        @error('national_id_number')
                            <div class="invalid-feedback">{{ __($message) }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="gender" class="form-label">{{ __('Gender') }} *</label>
                        <select id="gender" name="gender" class="form-select" required>
                            <option value="">{{ __('Select gender') }}</option>
                            @foreach (['Male', 'Female', 'Other'] as $gender)
                                <option
                                    value="{{ __($gender) }}"
                                    @selected(strtolower(old('gender', $patient->gender ?? '')) === strtolower($gender))
                                >
                                    {{ __($gender) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="date_of_birth" class="form-label">
                            {{ __('Date of Birth (optional)') }}
                        </label>
                        <input
                            type="date"
                            id="date_of_birth"
                            name="date_of_birth"
                            class="form-control"
                            value="{{ old('date_of_birth', $birthDate) }}"
                            max="{{ \App\Support\ClinicClock::now()->format('Y-m-d') }}"
                            autocomplete="bday"
                        >
                    </div>
                </div>
            </section>

            <section class="patient-form-panel">
                <div class="form-section-heading">
                    <div class="form-section-number">02</div>
                    <div>
                        <h2>{{ __('Family details') }}</h2>
                        <p>{{ __('Select marital status and applicable family information.') }}</p>
                    </div>
                </div>

                <div class="patient-form-grid">
                    <div class="form-field">
                        <label for="marital_status" class="form-label">
                            {{ __('Marital Status') }} *
                        </label>
                        <select
                            id="marital_status"
                            name="marital_status"
                            class="form-select"
                            required
                        >
                            <option value="">{{ __('Select marital status') }}</option>
                            <option value="unmarried" @selected($maritalStatus === 'unmarried')>
                                {{ __('Unmarried') }}
                            </option>
                            <option value="married" @selected($maritalStatus === 'married')>
                                {{ __('Married') }}
                            </option>
                        </select>
                    </div>

                    <div
                        class="form-field {{ $isMarried ? '' : 'd-none' }}"
                        data-children-question
                    >
                        <fieldset>
                            <legend class="form-label fs-6">{{ __('Has Children?') }} *</legend>

                            <div class="d-flex gap-4">
                                @foreach (['1' => 'Yes', '0' => 'No'] as $value => $label)
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
                                            {{ __($label) }}
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
                            {{ __('Number of Children') }} *
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

            <section class="patient-form-panel">
                <div class="form-section-heading">
                    <div class="form-section-number">03</div>
                    <div>
                        <h2>{{ __('Contact details') }}</h2>
                        <p>{{ __('Add the patient’s residential address.') }}</p>
                    </div>
                </div>

                <div class="patient-form-grid">
                    <div class="form-field form-field-wide">
                        <label for="address" class="form-label">{{ __('Address') }}</label>
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

            <section class="patient-form-panel">
                <div class="form-section-heading">
                    <div class="form-section-number">04</div>
                    <div>
                        <h2>{{ __('Additional notes') }}</h2>
                        <p>{{ __('Add useful information about the patient.') }}</p>
                    </div>
                </div>

                <div class="form-field">
                    <label for="notes" class="form-label">{{ __('Notes') }}</label>
                    <textarea
                        id="notes"
                        name="notes"
                        class="form-control"
                        rows="4"
                    >{{ old('notes', $patient->notes ?? '') }}</textarea>
                </div>
            </section>

            <div class="patient-form-actions">
                <a href="{{ route('patients.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2-circle app-icon" aria-hidden="true"></i>
                    {{ $editing ? 'Update Patient' : 'Save Patient' }}
                </button>
            </div>
        </form>
    </main>
</div>
@endsection