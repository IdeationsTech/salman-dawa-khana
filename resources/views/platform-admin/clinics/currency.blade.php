@extends('platform-admin.layouts.app')

@section('title', 'Clinic Country and Currency')
@section('page_title', 'Clinic Country and Currency')

@section('content')
<div class="pa-page-heading">
    <div>
        <a
            href="{{ route('platform-admin.clinics.edit', $clinic->clinic_id) }}"
            class="pa-back-link"
        >
            Back to clinic
        </a>

        <h1 class="pa-page-title">{{ $clinic->name }}</h1>
        <p class="pa-page-description">
            Manage the clinic’s country and currency.
        </p>
    </div>
</div>

@if ($errors->any())
    <div class="pa-notice pa-notice-error" role="alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="pa-panel">
    <header class="pa-panel-header">
        <div>
            <h2 class="pa-panel-title">Current settings</h2>
            <p class="pa-panel-description">
                {{ $countries[$clinic->country_code ?? ''] ?? 'Country not recorded' }}
                · {{ $clinic->currency }}
            </p>
        </div>
    </header>

    <div class="pa-panel-body">
        <p class="pa-form-description">
            Existing bills, payments, expenses and orders retain their original currency.
            Their amounts will not be converted. Reports show each currency separately;
            dashboard totals use the clinic’s selected currency.
            New records use the new currency.
        </p>

        <form
            method="POST"
            action="{{ route('platform-admin.clinics.currency.update', $clinic->clinic_id) }}"
            class="pa-form"
        >
            @csrf
            @method('PUT')

            <input
                type="hidden"
                name="expected_currency"
                value="{{ $clinic->currency }}"
            >
            <input
                type="hidden"
                name="expected_country"
                value="{{ $clinic->country_code }}"
            >

            <div class="pa-form-grid">
                <div class="pa-field">
                    <label for="country_code">Clinic country *</label>

                    <select
                        id="country_code"
                        name="country_code"
                        class="pa-input"
                        required
                    >
                        <option value="">Select country</option>
                        @foreach ($countries as $code => $name)
                            <option
                                value="{{ $code }}"
                                @selected(old('country_code', $clinic->country_code) === $code)
                            >
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pa-field">
                    <label for="currency">Clinic currency *</label>

                    <select
                        id="currency"
                        name="currency"
                        class="pa-input"
                        required
                    >
                        <option value="">Select currency</option>
                        @foreach ($currencies as $code => $name)
                            <option
                                value="{{ $code }}"
                                @selected(old('currency', $clinic->currency) === $code)
                            >
                                {{ $code }} — {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pa-field">
                <label for="reason">Reason for change *</label>
                <textarea
                    id="reason"
                    name="reason"
                    class="pa-input"
                    rows="3"
                    maxlength="2000"
                    required
                >{{ old('reason') }}</textarea>
            </div>

            <label class="pa-logo-remove">
                <input
                    type="checkbox"
                    name="client_consulted"
                    value="1"
                    @checked(old('client_consulted'))
                    required
                >
                <span>I discussed this change with the client.</span>
            </label>

            <label class="pa-logo-remove">
                <input
                    type="checkbox"
                    name="history_acknowledged"
                    value="1"
                    @checked(old('history_acknowledged'))
                    required
                >
                <span>
                    The client understands that existing amounts retain their
                    original currency and are not converted.
                </span>
            </label>

            <button type="submit" class="pa-button pa-button-primary">
                Save country and currency
            </button>
        </form>
    </div>
</section>

<section class="pa-panel">
    <header class="pa-panel-header">
        <h2 class="pa-panel-title">Existing financial records</h2>
    </header>

    <div class="pa-panel-body">
        <dl class="pa-detail-list">
            @foreach ($history as $table => $records)
                <div>
                    <dt>{{ \Illuminate\Support\Str::headline($table) }}</dt>
                    <dd>
                        @forelse ($records as $record)
                            <p>
                                {{ $record->currency ?: 'Currency not recorded' }}
                                — {{ $record->record_count }} records
                            </p>
                        @empty
                            No records
                        @endforelse
                    </dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>
@endsection