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
    $documentDate = $date
        ? \Illuminate\Support\Carbon::parse($date)
        : null;

    $dob = $patient?->date_of_birth
        ? \Illuminate\Support\Carbon::parse($patient->date_of_birth)
        : null;

    $age = $dob && $documentDate && $dob->lte($documentDate)
        ? (int) $dob->diffInYears($documentDate)
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

    $nationalId = $patient?->national_id_number
        ?: $patient?->emirates_id;

    $idCountry = strtoupper(
        (string) ($patient?->national_id_country ?? '')
    );

    $idDigits = preg_replace('/\D/', '', (string) $nationalId);

    if ($idCountry === 'PK' && strlen($idDigits) === 13) {
        $nationalId = substr($idDigits, 0, 5)
            . '-' . substr($idDigits, 5, 7)
            . '-' . substr($idDigits, 12, 1);
    } elseif (
        ($idCountry === 'AE' || (!$idCountry && $patient?->emirates_id))
        && strlen($idDigits) === 15
    ) {
        $nationalId = substr($idDigits, 0, 3)
            . '-' . substr($idDigits, 3, 4)
            . '-' . substr($idDigits, 7, 7)
            . '-' . substr($idDigits, 14, 1);
    }
@endphp

<article class="clinic-letterhead">
    <header class="clh-header">
        <svg
            class="clh-header-shape"
            viewBox="0 0 680 175"
            preserveAspectRatio="none"
            aria-hidden="true"
            focusable="false"
        >
            <path d="M0 0H680V175H0Z" fill="#f2f9f7"/>
            <path d="M0 0H393L270 175H0Z" fill="#078582"/>
            <path
                d="M0 174H680"
                fill="none"
                stroke="#83cbb8"
                stroke-width="2"
            />
        </svg>

        <div class="clh-practitioner">
            <span class="clh-practitioner-label">
                {{ __('Consulting Doctor / Hakeem') }}
            </span>

            <h2>{{ $practitioner?->name ?: 'Not assigned' }}</h2>

            <p>{{ __('Patient care & consultation') }}</p>
            <p>{{ __('Consultation by appointment') }}</p>
        </div>

        <div class="clh-clinic">
            @if ($logoUrl)
                <img
                    src="{{ $logoUrl }}"
                    alt="{{ $clinic?->name }} logo"
                    class="clh-logo"
                >
            @endif

            <div class="clh-clinic-text">
                <h1>{{ $clinic?->name ?: __('Clinic') }}</h1>

                <p>{{ __('Patient care & consultation') }}</p>

                @if ($headerLocation)
                    <p class="clh-location">{{ $headerLocation }}</p>
                @endif
            </div>
        </div>
    </header>

    <div class="clh-document-meta">
        <h2>{{ $documentTitle }}</h2>

        <span>
            {{ __('Reference') }} <strong>{{ $reference ?: '—' }}</strong>
        </span>
    </div>

    <div class="clh-frame">
        <section class="clh-patient-info" aria-label="{{ __('Patient information') }}">
            <div>
                <span>{{ __('Patient name') }}</span>
                <strong>{{ $patient?->full_name ?: '—' }}</strong>
            </div>

            <div>
                <span>{{ __('Patient ID') }}</span>
                <strong>{{ $nationalId ?: '—' }}</strong>
            </div>

            <div>
                <span>{{ __('Date of birth / Age') }}</span>

                <strong>
                    {{ $dob?->format('d M Y') ?? '—' }}
                    @if ($age !== null)
                        / {{ $age }} {{ __('years') }}
                    @endif
                </strong>
            </div>

            <div>
                <span>{{ __('Date & time') }}</span>
                <strong>
                    {{ $documentDate?->format('d M Y · h:i A') ?? '—' }}
                </strong>
            </div>

            <div>
                <span>{{ __('Phone number') }}</span>
                <strong>{{ $patient?->phone ?: '—' }}</strong>
            </div>

            <div>
                <span>{{ __('Gender / Visit type') }}</span>

                <strong>
                    {{ $patient?->gender ? ucfirst($patient->gender) : '—' }}
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
                <span>{{ __('Consulting Doctor / Hakeem') }}</span>
                <strong>{{ $practitioner?->name ?: 'Not assigned' }}</strong>
            </div>

            <div class="clh-signature-line">
                {{ __('Doctor’s signature / clinic stamp') }}
            </div>
        </div>
    </div>

    <footer class="clh-footer">
        @if ($clinic?->phone)
            <div class="clh-contact-item">
                <i class="bi bi-telephone" aria-hidden="true"></i>
                <span>{{ $clinic->phone }}</span>
            </div>
        @endif

        @if ($clinic?->address)
            <div class="clh-contact-item clh-contact-address">
                <i class="bi bi-geo-alt" aria-hidden="true"></i>
                <span>{{ $clinic->address }}</span>
            </div>
        @endif
    </footer>
</article>