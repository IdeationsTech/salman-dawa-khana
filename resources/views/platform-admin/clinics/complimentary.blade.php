@extends('platform-admin.layouts.app')

@section('title', 'Complimentary Lifetime License')
@section('page_title', 'Complimentary Lifetime License')

@section('content')
    <div class="pa-page-heading">
        <div>
            <a
                href="{{ route('platform-admin.clinics.edit', $clinic->clinic_id) }}"
                class="pa-back-link"
            >
                ← Back to edit clinic
            </a>

            <h1 class="pa-page-title">
                Complimentary Lifetime license
            </h1>

            <p class="pa-page-description">
                {{ $clinic->name }}
                · Clinic #{{ $clinic->clinic_id }}
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="pa-notice pa-notice-error" role="alert">
            <strong>Please correct the following:</strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="pa-panel">
        <header class="pa-panel-header">
            <div>
                <h2 class="pa-panel-title">
                    Grant Lifetime access
                </h2>

                <p class="pa-panel-description">
                    A Lifetime license and an AED 0 complimentary
                    payment record will be created.
                </p>
            </div>
        </header>

        <div class="pa-panel-body">
            <dl class="pa-detail-list">
                <div>
                    <dt>Clinic</dt>
                    <dd>{{ $clinic->name }}</dd>
                </div>

                <div>
                    <dt>Current status</dt>
                    <dd>
                        {{ \Illuminate\Support\Str::headline($clinic->onboarding_status) }}
                    </dd>
                </div>

                <div>
                    <dt>Amount received</dt>
                    <dd>AED 0.00 — complimentary</dd>
                </div>
            </dl>

            <div class="pa-form-divider"></div>

            @if($clinic->onboarding_status === 'rejected')
                <p class="pa-form-description">
                    A rejected clinic cannot receive this license.
                </p>
            @else
                <form
                    method="POST"
                    action="{{ route('platform-admin.clinics.complimentary.store', $clinic->clinic_id) }}"
                    class="pa-form"
                >
                    @csrf

                    <div class="pa-field">
                        <label for="complimentary-reason">
                            Reason for complimentary access *
                        </label>

                        <textarea
                            id="complimentary-reason"
                            name="reason"
                            class="pa-input"
                            rows="4"
                            maxlength="2000"
                            required
                        >{{ old('reason') }}</textarea>
                    </div>

                    <button
                        type="submit"
                        class="pa-button pa-button-primary"
                    >
                        Issue complimentary Lifetime license
                    </button>
                </form>
            @endif
        </div>
    </section>
@endsection