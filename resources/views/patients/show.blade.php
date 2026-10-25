@extends('layouts.app')

@section('title', $patient->full_name . ' — Patient Profile')

@section('content')
@php
    $nameParts = preg_split(
        '/\s+/u',
        trim($patient->full_name),
        -1,
        PREG_SPLIT_NO_EMPTY
    );

    $initials = mb_strtoupper(
        collect($nameParts)
            ->take(2)
            ->map(fn ($part) => mb_substr($part, 0, 1))
            ->implode('')
    );

    $age = $patient->date_of_birth
        ? (int) $patient->date_of_birth->diffInYears(\App\Support\ClinicClock::now())
        : null;

    $status = strtolower((string) $patient->status);

    $statusClass = $status === 'active'
        ? 'active-badge'
        : 'inactive-badge';

    $maritalStatus = match ($patient->marital_status) {
        'married' => __('Married'),
        'unmarried' => __('Unmarried'),
        default => __('Not recorded'),
    };

    $childrenLabel = 'Not recorded';

    if ($patient->marital_status === 'married') {
        if ($patient->has_children === true) {
            $childrenLabel = 'Yes — ' . $patient->children_count;
        } elseif ($patient->has_children === false) {
            $childrenLabel = 'No';
        }
    } elseif ($patient->marital_status === 'unmarried') {
        $childrenLabel = 'Not applicable';
    }

    $profileIdCountry = $patient->national_id_country
        ?: (filled($patient->emirates_id) ? 'AE' : null);

    $profileIdNumber = $patient->national_id_number
        ?: $patient->emirates_id;

    $profileIdDigits = preg_replace(
        '/[\s-]+/',
        '',
        $profileIdNumber ?? ''
    );

    $profileIdLabel = match ($profileIdCountry) {
        'PK' => __('CNIC'),
        'AE' => __('Emirates ID'),
        default => __('National ID'),
    };

    $formattedProfileId = match (true) {
        $profileIdCountry === 'PK'
            && preg_match('/^[0-9]{13}$/', $profileIdDigits) === 1 =>
                substr($profileIdDigits, 0, 5)
                . '-' . substr($profileIdDigits, 5, 7)
                . '-' . substr($profileIdDigits, 12, 1),

        $profileIdCountry === 'AE'
            && preg_match('/^784[0-9]{12}$/', $profileIdDigits) === 1 =>
                substr($profileIdDigits, 0, 3)
                . '-' . substr($profileIdDigits, 3, 4)
                . '-' . substr($profileIdDigits, 7, 7)
                . '-' . substr($profileIdDigits, 14, 1),

        default => $profileIdNumber ?: 'Not recorded',
    };

    $money = fn ($value, $code) =>
        $code . ' ' . number_format((float) $value, $moneyDecimals);
@endphp

<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <div class="form-page-header no-print">
            <a href="{{ route('patients.index') }}" class="back-link">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                {{ __('Back to patients') }}
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success no-print" role="status">
                {{ __(session('success')) }}
            </div>
        @endif

        @if ($unconfirmedFinancialRecords > 0)
            <div class="alert alert-warning" role="status">
                {{ $unconfirmedFinancialRecords }} {{ __('financial records have no confirmed currency and are excluded from account totals.') }}
            </div>
        @endif

        <div class="patient-print-heading">
            <div>
                <strong>{{ __('Patient summary') }}</strong>
                <span>{{ auth()->user()->clinic->name }}</span>
            </div>
            <small>{{ __('Printed on') }} {{ \\App\\Support\\ClinicClock::now()->format('d M Y, h:i A') }}</small>
        </div>

        <section class="patient-profile-header">
            <div class="patient-profile-identity">
                <div class="large-patient-avatar">{{ $initials ?: '?' }}</div>

                <div>
                    <div class="patient-id-label">
                        {{ __('PATIENT CODE:') }} {{ $patient->patient_code }}
                    </div>

                    <h1>{{ $patient->full_name }}</h1>

                    <p>
                        {{ $patient->gender ?: 'Gender not recorded' }}
                        @if ($age !== null)
                            · {{ $age }} {{ __('years') }}
                        @endif
                        @if ($patient->phone)
                            · <bdi class="phone-number" dir="ltr">&#x2066;{{ $patient->phone }}&#x2069;</bdi>
                        @endif
                    </p>
                </div>
            </div>

            <div class="patient-profile-actions no-print">
                @clinicCan('patients.edit')<a
                    href="{{ route('patients.edit', $patient) }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                    {{ __('Edit patient') }}
                </a>@endclinicCan

                <a
                    href="{{ route('visits.create', [
                        'patient_id' => $patient->patient_id,
                    ]) }}"
                    class="btn btn-primary"
                >
                    <i class="bi bi-plus-circle" aria-hidden="true"></i>
                    {{ __('New visit') }}
                </a>
            </div>
        </section>

        <section class="patient-profile-grid">
            <div class="patient-profile-main">
                <div class="patient-detail-panel">
                    <div class="profile-panel-heading">
                        <div>
                            <h2>{{ __('Personal information') }}</h2>
                            <p>{{ __('Registered patient details') }}</p>
                        </div>

                        <span class="status-badge {{ $statusClass }}">
                            {{ $status !== '' ? ucfirst($status) : __('Not recorded') }}
                        </span>
                    </div>

                    <div class="detail-grid">
                        <div>
                            <span class="detail-label">{{ __('Full name') }}</span>
                            <strong>{{ $patient->full_name }}</strong>
                        </div>

                        <div>
                            <span class="detail-label">{{ __('Patient code') }}</span>
                            <strong>{{ $patient->patient_code }}</strong>
                        </div>

                        <div>
                            <span class="detail-label">{{ __('Father / husband name') }}</span>
                            <strong>
                                {{ $patient->father_or_husband_name ?: __('Not recorded') }}
                            </strong>
                        </div>

                        <div>
                            <span class="detail-label">{{ __('Phone number') }}</span>
                            <strong><bdi class="phone-number" dir="ltr">&#x2066;{{ $patient->phone ?: __('Not recorded') }}&#x2069;</bdi></strong>
                        </div>

                        <div>
                            <span class="detail-label">{{ $profileIdLabel }}</span>
                            <strong>{{ $formattedProfileId }}</strong>
                        </div>

                        <div>
                            <span class="detail-label">{{ __('Date of birth') }}</span>
                            <strong>
                                {{ $patient->date_of_birth?->format('d F Y') ?? __('Not recorded') }}
                            </strong>
                        </div>

                        <div>
                            <span class="detail-label">{{ __('Gender') }}</span>
                            <strong>{{ $patient->gender ?: __('Not recorded') }}</strong>
                        </div>

                        <div>
                            <span class="detail-label">{{ __('Marital status') }}</span>
                            <strong>{{ $maritalStatus }}</strong>
                        </div>

                        <div>
                            <span class="detail-label">{{ __('Children') }}</span>
                            <strong>{{ $childrenLabel }}</strong>
                        </div>

                        <div>
                            <span class="detail-label">{{ __('Registered on') }}</span>
                            <strong>
                                {{ $patient->created_at?->format('d F Y') ?? __('Not recorded') }}
                            </strong>
                        </div>

                        <div>
                            <span class="detail-label">{{ __('Address') }}</span>
                            <strong>{{ $patient->address ?: __('Not recorded') }}</strong>
                        </div>
                    </div>
                </div>

                @if ($patient->notes)
                    <div class="patient-detail-panel">
                        <div class="profile-panel-heading">
                            <div><h2>{{ __('Patient notes') }}</h2></div>
                        </div>
                        <p class="mb-0">{!! nl2br(e($patient->notes)) !!}</p>
                    </div>
                @endif

                <div class="patient-detail-panel" id="patient-visits" data-print-empty="{{ $recentVisits->isEmpty() ? 'true' : 'false' }}">
                    <div class="profile-panel-heading">
                        <div>
                            <h2>{{ __('Visit history') }}</h2>
                            <p>{{ __('Latest five visits for this patient') }}</p>
                        </div>
                    </div>

                    <div class="visit-history-list">
                        @forelse ($recentVisits as $visit)
                            <div class="visit-history-item">
                                <div class="visit-date">
                                    <strong>{{ $visit->visit_date?->format('d') ?? '—' }}</strong>
                                    <span>
                                        {{ $visit->visit_date
                                            ? strtoupper($visit->visit_date->format('M'))
                                            : '—' }}
                                    </span>
                                </div>

                                <div class="visit-info">
                                    <strong>{{ $visit->visit_reason ?: __('Visit') }}</strong>
                                    <span>
                                        {{ $visit->visit_date?->format('d M Y, h:i A') ?? 'Date not recorded' }}
                                    </span>
                                    @if ($visit->diagnosis_name)
                                        <span>{{ __('Diagnosis:') }} {{ $visit->diagnosis_name }}</span>
                                    @endif
                                    <span>
                                        {{ $visit->status
                                            ? ucfirst(str_replace('_', ' ', $visit->status))
                                            : 'Status not recorded' }}
                                    </span>
                                </div>

                                <a
                                    href="{{ route('visits.show', $visit) }}"
                                    class="panel-link no-print"
                                >
                                    {{ __('View') }}
                                </a>
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ __('No visits recorded.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="patient-detail-panel" id="patient-prescriptions" data-print-empty="{{ $recentPrescriptions->isEmpty() ? 'true' : 'false' }}">
                    <div class="profile-panel-heading">
                        <div>
                            <h2>{{ __('Prescriptions') }}</h2>
                            <p>{{ __('Latest five prescriptions for this patient') }}</p>
                        </div>
                    </div>

                    <div class="visit-history-list">
                        @forelse ($recentPrescriptions as $prescription)
                            <div class="visit-history-item">
                                <div class="visit-date">
                                    <strong>
                                        {{ $prescription->prescribed_at?->format('d') ?? '—' }}
                                    </strong>
                                    <span>
                                        {{ $prescription->prescribed_at
                                            ? strtoupper($prescription->prescribed_at->format('M'))
                                            : '—' }}
                                    </span>
                                </div>

                                <div class="visit-info">
                                    <strong>{{ $prescription->prescription_no }}</strong>
                                    <span>
                                        {{ $prescription->prescribed_at?->format('d M Y') ?? 'Date not recorded' }}
                                    </span>
                                    @if ($prescription->nuskha_name)
                                        <span>{{ $prescription->nuskha_name }}</span>
                                    @endif
                                    <span>
                                        {{ ucfirst($prescription->status ?: __('Not recorded')) }}
                                    </span>
                                </div>

                                <a
                                    href="{{ route('prescriptions.show', $prescription) }}"
                                    class="panel-link no-print"
                                >
                                    {{ __('View') }}
                                </a>
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ __('No prescriptions recorded.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <aside class="patient-profile-side">
                <div class="patient-detail-panel account-summary-panel">
                    <div class="profile-panel-heading">
                        <div>
                            <h2>{{ __('Account summary') }}</h2>
                            <p>{{ __('Original currencies shown separately') }}</p>
                        </div>
                    </div>

                    @foreach ($accounts as $account)
                        @if (!$loop->first)
                            <hr>
                        @endif

                        <h3 class="h6">{{ $account['currency'] }}</h3>

                        <div class="account-total">
                            <span>{{ __('Outstanding bills') }}</span>
                            <strong>
                                {{ $money($account['outstanding'], $account['currency']) }}
                            </strong>
                        </div>

                        <div class="account-row">
                            <span>{{ __('Total billed') }}</span>
                            <strong>
                                {{ $money($account['billed'], $account['currency']) }}
                            </strong>
                        </div>

                        <div class="account-row">
                            <span>{{ __('Paid against bills') }}</span>
                            <strong>
                                {{ $money($account['paid'], $account['currency']) }}
                            </strong>
                        </div>

                        @if ((float) $account['unallocated'] > 0)
                            <div class="account-row">
                                <span>{{ __('Unallocated payments') }}</span>
                                <strong>
                                    {{ $money($account['unallocated'], $account['currency']) }}
                                </strong>
                            </div>
                        @endif
                    @endforeach

                    <small class="text-muted d-block mt-3">
                        {{ __('Currencies are not converted or combined. Unallocated payments are not deducted from bills.') }}
                    </small>

                    <a
                        href="{{ route('payments.create', [
                            'mode' => 'new_bill',
                            'patient_id' => $patient->patient_id,
                        ]) }}"
                        class="btn btn-primary w-100 mt-3 no-print"
                    >
                        {{ __('New bill + payment') }}
                    </a>

                    @if ($hasOutstanding)
                        <a
                            href="{{ route('payments.create', [
                                'mode' => 'existing_bill',
                                'patient_id' => $patient->patient_id,
                            ]) }}"
                            class="btn btn-outline-secondary w-100 mt-2 no-print"
                        >
                            {{ __('Receive outstanding payment') }}
                        </a>
                    @endif
                </div>

                <div class="patient-detail-panel no-print">
                    <div class="profile-panel-heading">
                        <div><h2>{{ __('Quick actions') }}</h2></div>
                    </div>

                    <div class="profile-quick-actions">
                        <a href="#patient-prescriptions">
                            {{ __('Recent prescriptions') }} <span>›</span>
                        </a>
                        <a href="{{ route('visits.create', [
                            'patient_id' => $patient->patient_id,
                        ]) }}">
                            {{ __('Create new visit') }} <span>›</span>
                        </a>
                    </div>

                    <button
                        type="button"
                        class="btn btn-outline-secondary w-100 mt-3"
                        data-print-page
                    >
                        {{ __('Print patient summary') }}
                    </button>
                </div>
            </aside>
        </section>
    </main>
</div>
@endsection