@extends('layouts.app')

@section('title', 'Payment Settings — ' . $clinic->name)

@section('content')
@php
    $isOwner = auth()->user()->role?->name === 'Owner';

    $preference = fn ($key, $default = '1') =>
        old($key, $settings->get($key)?->setting_value ?? $default);

    $methodOptions = [
        [
            'key' => 'cash_enabled',
            'label' => 'Cash payments',
            'description' => 'Accept cash payments from patients.',
        ],
        [
            'key' => 'bank_enabled',
            'label' => 'Bank transfers',
            'description' => 'Record payments received by bank transfer.',
        ],
        [
            'key' => 'card_enabled',
            'label' => 'Card payments',
            'description' => 'Record payments received through card or POS.',
        ],
    ];

    $billingOptions = [
        [
            'key' => 'show_previous_due',
            'label' => 'Show previous due',
            'description' => 'Show the patient’s previous outstanding balance on receipts.',
        ],
        [
            'key' => 'allow_partial_payments',
            'label' => 'Allow partial payments',
            'description' => 'Accept less than the remaining bill balance.',
        ],
    ];
@endphp

<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">{{ __('Settings') }}</p>
                <h1>{{ __('Payment settings') }}</h1>
                <p class="page-subtitle">
                    {{ __('Configure payment preferences and amount display.') }}
                </p>
            </div>
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="status">
                {{ __(session('success')) }}
            </div>
        @endif

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

        <section class="settings-layout">
            @include('settings._navigation')

            <div class="settings-content">
                <form
                    method="POST"
                    action="{{ route('settings.payments.update') }}"
                >
                    @csrf
                    @method('PUT')

                    <section class="settings-panel">
                        <div class="settings-panel-heading">
                            <div>
                                <h2>{{ __('Currency and format') }}</h2>
                                <p>{{ __('View clinic currency and configure amount display.') }}</p>
                            </div>
                        </div>

                        <div class="settings-form-grid">
                            <div class="form-field">
                                <label for="currency" class="form-label">
                                    {{ __('Clinic currency') }}
                                </label>

                                <input
                                    type="text"
                                    id="currency"
                                    class="form-control"
                                    value="{{ $clinic->currency }}"
                                    readonly
                                >

                                <small class="text-muted">
                                    {{ __('Currency is locked after signup. Contact the platform administrator to change it.') }}
                                </small>
                            </div>

                            <div class="form-field">
                                <label for="decimal_places" class="form-label">
                                    {{ __('Decimal places') }} *
                                </label>

                                <select
                                    id="decimal_places"
                                    name="decimal_places"
                                    class="form-select"
                                    @disabled(!$isOwner)
                                    required
                                >
                                    <option
                                        value="2"
                                        @selected((string) $preference('decimal_places', '2') === '2')
                                    >
                                        2 decimal places
                                    </option>

                                    <option
                                        value="0"
                                        @selected((string) $preference('decimal_places', '2') === '0')
                                    >
                                        0 decimal places
                                    </option>
                                </select>
                            </div>

                            <div class="form-field settings-field-wide">
                                <small class="text-muted">
                                    {{ __('Decimal places affect display only. Existing amounts are not converted.') }}
                                </small>
                            </div>
                        </div>
                    </section>

                    @foreach ([
                        ['title' => 'Payment methods', 'description' => 'Keep at least one payment method enabled.', 'options' => $methodOptions],
                        ['title' => 'Billing preferences', 'description' => 'Configure balance display and payment entry.', 'options' => $billingOptions],
                    ] as $section)
                        <section class="settings-panel">
                            <div class="settings-panel-heading">
                                <div>
                                    <h2>{{ $section['title'] }}</h2>
                                    <p>{{ $section['description'] }}</p>
                                </div>
                            </div>

                            <div class="settings-toggle-list">
                                @foreach ($section['options'] as $option)
                                    <div class="settings-toggle-row">
                                        <div>
                                            <label for="{{ $option['key'] }}">
                                                <strong>{{ $option['label'] }}</strong>
                                            </label>
                                            <small>{{ $option['description'] }}</small>
                                        </div>

                                        <input
                                            type="hidden"
                                            name="{{ $option['key'] }}"
                                            value="0"
                                        >

                                        <input
                                            type="checkbox"
                                            id="{{ $option['key'] }}"
                                            name="{{ $option['key'] }}"
                                            class="form-check-input"
                                            value="1"
                                            @checked((string) $preference($option['key']) === '1')
                                            @disabled(!$isOwner)
                                        >
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endforeach

                    @if ($isOwner)
                        <div class="settings-panel-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle app-icon" aria-hidden="true"></i>
                                {{ __('Save payment settings') }}
                            </button>
                        </div>
                    @else
                        <p class="text-muted">
                            {{ __('The clinic Owner manages payment settings.') }}
                        </p>
                    @endif
                </form>
            </div>
        </section>
    </main>
</div>
@endsection