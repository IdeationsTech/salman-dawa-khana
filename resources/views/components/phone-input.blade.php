@props([
    'name',
    'id' => null,
    'value' => '',
    'required' => false,
])

@php
    $inputId = $id ?? str_replace(['[', ']'], ['_', ''], $name);
    $currentValue = old($name, $value);
@endphp

<div class="international-phone-field" data-phone-field>
    {{-- Visible number input; the complete number is submitted below. --}}
    <input
        type="tel"
        id="{{ $inputId }}"
        class="form-control @error($name) is-invalid @enderror"
        value="{{ $currentValue }}"
        autocomplete="tel"
        inputmode="tel"
        data-international-phone
        aria-describedby="{{ $inputId }}_help"
        @required($required)
    >

    <input
        type="hidden"
        name="{{ $name }}"
        value="{{ $currentValue }}"
        data-phone-full
    >

    <small id="{{ $inputId }}_help" class="text-muted">
        {{ __('Select the country, then enter the phone number.') }}
    </small>

    @error($name)
        <div class="text-danger small">{{ __($message) }}</div>
    @enderror
</div>