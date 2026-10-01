@extends('platform-admin.layouts.app')

@section('title', 'Subscription Plans')
@section('page_title', 'Subscription Plans')

@section('content')
    <div class="pa-page-heading">
        <div>
            <h1 class="pa-page-title">
                Subscription plans
            </h1>

            <p class="pa-page-description">
                Configure prices in AED. Only active paid plans appear
                in clinic approval and subscription forms.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="pa-notice" role="status">
            {{ session('success') }}
        </div>
    @endif

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

    <div class="pa-detail-grid">
        @foreach($plans as $plan)
            <section class="pa-panel">
                <header class="pa-panel-header">
                    <div>
                        <h2 class="pa-panel-title">
                            {{ $plan->name }}
                        </h2>

                        <p class="pa-panel-description">
                            {{ \Illuminate\Support\Str::headline($plan->term_type) }}
                            · {{ $plan->plan_code }}
                        </p>
                    </div>

                    <span class="pa-badge">
                        {{ $plan->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </header>

                <div class="pa-panel-body">
                    @if($plan->plan_code === 'trial_7d')
                        <dl class="pa-detail-list">
                            <div>
                                <dt>Price</dt>
                                <dd>
                                    {{ $plan->currency }}
                                    {{ number_format((float) $plan->price, 2) }}
                                </dd>
                            </div>

                            <div>
                                <dt>Duration</dt>
                                <dd>
                                    {{ $plan->duration_days }} days
                                </dd>
                            </div>
                        </dl>

                        <p class="pa-form-description">
                            The seven-day trial is free and is managed
                            separately from paid plans.
                        </p>
                    @else
                        <form
                            method="POST"
                            action="{{ route('platform-admin.plans.update', $plan->subscription_plan_id) }}"
                            class="pa-form"
                        >
                            @csrf
                            @method('PUT')

                            <div class="pa-field">
                                <label for="price-{{ $plan->subscription_plan_id }}">
                                    Price (AED) *
                                </label>

                                <input
                                    id="price-{{ $plan->subscription_plan_id }}"
                                    type="number"
                                    name="price"
                                    class="pa-input"
                                    min="0.01"
                                    max="9999999999.99"
                                    step="0.01"
                                    value="{{ $plan->price }}"
                                    required
                                >
                            </div>

                            <div class="pa-field">
                                <label for="active-{{ $plan->subscription_plan_id }}">
                                    Availability *
                                </label>

                                <select
                                    id="active-{{ $plan->subscription_plan_id }}"
                                    name="is_active"
                                    class="pa-input"
                                    required
                                >
                                    <option
                                        value="0"
                                        @selected(! $plan->is_active)
                                    >
                                        Inactive
                                    </option>

                                    <option
                                        value="1"
                                        @selected((bool) $plan->is_active)
                                    >
                                        Active
                                    </option>
                                </select>
                            </div>

                            <div class="pa-field">
                                <label for="description-{{ $plan->subscription_plan_id }}">
                                    Description
                                </label>

                                <textarea
                                    id="description-{{ $plan->subscription_plan_id }}"
                                    name="description"
                                    class="pa-input"
                                    rows="3"
                                    maxlength="2000"
                                >{{ $plan->description }}</textarea>
                            </div>

                            <button
                                type="submit"
                                class="pa-button pa-button-primary"
                            >
                                Save {{ $plan->name }}
                            </button>
                        </form>
                    @endif
                </div>
            </section>
        @endforeach
    </div>
@endsection