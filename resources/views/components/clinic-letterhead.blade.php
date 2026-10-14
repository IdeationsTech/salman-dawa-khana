@props([
    'clinic',
    'patient',
    'practitioner' => null,
    'date' => null,
    'documentTitle' => 'Patient / visit slip',
    'reference' => '',
    'visitType' => null,
])

@php
    $practitionerRole = $practitioner?->role?->name;

    $practitionerLabel = in_array(
        $practitionerRole,
        ['Owner', 'Doctor'],
        true
    ) ? 'Consulting practitioner' : 'Recorded by';

    $dob = $patient?->date_of_birth;

    $age = $dob && $date && $dob->lte($date)
        ? (int) $dob->diffInYears($date)
        : null;

    $logoUrl = $clinic?->logo_path
        ? asset('storage/' . $clinic->logo_path)
        : null;

    $headerLocation = $clinic
        ? \Illuminate\Support\Facades\DB::table('settings')
            ->where('clinic_id', $clinic->clinic_id)
            ->where('setting_key', 'print_location')
            ->value('setting_value')
        : null;
@endphp

<article class="clinic-letterhead">
    <header class="clh-header">
        <svg
            class="clh-header-curve"
            viewBox="0 0 640 170"
            preserveAspectRatio="none"
            aria-hidden="true"
            focusable="false"
        >
            <path
                d="M0 0 H248 C277 0 286 20 303 49
                   L350 124 C361 143 377 156 407 159
                   L640 168 V170 H0 Z"
                fill="#199bb4"
            />
            <path
                d="M248 0 C277 0 286 20 303 49
                   L350 124 C361 143 377 156 407 159
                   L640 168"
                fill="none"
                stroke="#74c8d0"
                stroke-width="3"
            />
        </svg>

        <div class="clh-practitioner">
            <h2>{{ $practitioner?->name ?? '—' }}</h2>
            <p>{{ $practitionerLabel }}</p>
        </div>

        <div class="clh-clinic">
            <div class="clh-clinic-text">
                <h1>{{ $clinic?->name ?? 'Clinic' }}</h1>

                <p>Patient care &amp; consultation</p>

                @if ($headerLocation)
                    <p class="clh-location">{{ $headerLocation }}</p>
                @endif
            </div>

            @if ($logoUrl)
                <img
                    src="{{ $logoUrl }}"
                    alt="{{ $clinic?->name }} logo"
                    class="clh-logo"
                >
            @endif
        </div>
    </header>

    <div class="clh-document-meta">
        <span>{{ $documentTitle }}</span>
        <strong>{{ $reference }}</strong>
    </div>

    <div class="clh-frame">
        <section class="clh-patient-info" aria-label="Patient information">
            <div>
                <span>Patient name</span>
                <strong>{{ $patient?->full_name ?? '—' }}</strong>
            </div>

            <div>
                <span>Patient code</span>
                <strong>{{ $patient?->patient_code ?? '—' }}</strong>
            </div>

            <div>
                <span>Date of birth / Age</span>
                <strong>
                    {{ $dob?->format('d M Y') ?? '—' }}
                    @if ($age !== null)
                        / {{ $age }} years
                    @endif
                </strong>
            </div>

            <div>
                <span>Date &amp; time</span>
                <strong>{{ $date?->format('d M Y · h:i A') ?? '—' }}</strong>
            </div>

            <div>
                <span>Phone number</span>
                <strong>{{ $patient?->phone ?: '—' }}</strong>
            </div>

            <div>
                <span>Gender / Visit type</span>
                <strong>
                    {{ $patient?->gender ?: '—' }}
                    @if ($visitType)
                        / {{ $visitType }}
                    @endif
                </strong>
            </div>
        </section>

        <section class="clh-content">
            {{ $slot }}
        </section>

        <div class="clh-signatures">
            <div>
                <span>{{ $practitionerLabel }}</span>
                <strong>{{ $practitioner?->name ?? '—' }}</strong>
            </div>

            <div class="clh-signature-line">
                Signature / clinic stamp
            </div>
        </div>
    </div>

    <footer class="clh-footer">
        <span class="clh-contact-label">Contact:</span>

        <div class="clh-contact-details">
            @if ($clinic?->phone)
                <div>
                    <i class="bi bi-telephone" aria-hidden="true"></i>
                    <span>{{ $clinic->phone }}</span>
                </div>
            @endif

            @if ($clinic?->address)
                <div>
                    <i class="bi bi-geo-alt" aria-hidden="true"></i>
                    <span>{{ $clinic->address }}</span>
                </div>
            @endif
        </div>
    </footer>
</article>