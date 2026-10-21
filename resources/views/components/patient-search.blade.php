@props(['patient' => null, 'locked' => false, 'billing' => false])
<div class="form-field" data-rx-patient-search data-search-url="{{ route('patients.lookup') }}" data-selected-name="{{ $patient?->full_name ?? '' }}" data-locked="{{ $locked ? '1' : '0' }}">
    <label for="patient-query" class="form-label">{{ __('Patient') }} *</label>
    <input type="hidden" name="patient_id" value="{{ $patient?->patient_id ?? '' }}" data-rx-patient @if($billing) data-cp-patient @endif>
    <input type="text" id="patient-query" class="form-control" value="{{ $patient?->full_name ?? '' }}" placeholder="{{ __('Start typing a patient name...') }}" maxlength="160" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="patient-results" aria-describedby="patient-search-help" data-rx-patient-query @readonly($locked) required>
    <div class="rx-patient-results" id="patient-results" role="listbox" aria-label="{{ __('Matching patients') }}" data-rx-patient-results hidden></div>
    <small id="patient-search-help" class="form-help" data-rx-patient-feedback role="status" aria-live="polite">{{ $patient ? 'Selected: '.$patient->patient_code : __('Type a name, then select the matching patient.') }}</small>
    @error('patient_id') <div class="text-danger">{{ __($message) }}</div> @enderror
</div>
