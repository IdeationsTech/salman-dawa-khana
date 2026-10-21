<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Setting;
use App\Rules\InternationalPhone;
use App\Services\ClinicNotificationService;
use App\Support\ClinicClock;
use App\Support\ClinicLocaleCatalog;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'status' => ['nullable', Rule::in(['all', 'active', 'inactive'])],
            'export' => ['nullable', 'in:csv'],
        ]);

        $clinicId = (int) Auth::user()->clinic_id;
        $balances = $this->patientBalancesQuery($clinicId);

        $query = Patient::query()
            ->where('patients.clinic_id', $clinicId)
            ->leftJoinSub(
                clone $balances,
                'patient_balances',
                'patient_balances.patient_id',
                '=',
                'patients.patient_id'
            )
            ->select('patients.*')
            ->selectRaw('COALESCE(patient_balances.balance_due, 0) AS balance_due')
            ->withMax([
                'visits as last_visit_at' => fn ($query) => $query->where('clinic_id', $clinicId),
            ], 'visit_date')
            ->withCasts([
                'last_visit_at' => 'datetime',
                'balance_due' => 'decimal:2',
            ]);

        $search = trim($filters['search'] ?? '');

        if ($search !== '') {
            $normalizedId = preg_replace('/[\s-]+/', '', $search);

            $query->where(function ($query) use ($search, $normalizedId) {
                $query->where('patients.full_name', 'like', "%{$search}%")
                    ->orWhere('patients.phone', 'like', "%{$search}%")
                    ->orWhere('patients.patient_code', 'like', "%{$search}%")
                    ->orWhere('patients.national_id_number', 'like', "%{$search}%");

                if ($normalizedId !== '') {
                    $query->orWhere(
                        'patients.national_id_number',
                        'like',
                        "%{$normalizedId}%"
                    )->orWhere('patients.emirates_id', 'like', "%{$normalizedId}%");
                }

                if (ctype_digit($search)) {
                    $query->orWhere('patients.patient_id', $search);
                }
            });
        }

        $status = $filters['status'] ?? 'all';

        if ($status !== 'all') {
            $query->where('patients.status', $status);
        }

        $currency = strtoupper(Auth::user()->clinic->currency ?: 'AED');

        $moneyDecimals = (string) Setting::query()
            ->where('clinic_id', $clinicId)
            ->where('setting_key', 'decimal_places')
            ->value('setting_value') === '0' ? 0 : 2;

        if (($filters['export'] ?? null) === 'csv') {
            $exportQuery = (clone $query)->orderBy('patients.patient_id');
            $filename = 'patients-'.now()->format('Y-m-d-His').'.csv';

            return response()->streamDownload(function () use (
                $exportQuery,
                $currency
            ) {
                $stream = fopen('php://output', 'w');

                if ($stream === false) {
                    throw new \RuntimeException('The export stream could not be opened.');
                }

                fwrite($stream, "\xEF\xBB\xBF");

                $safeText = static function ($value): string {
                    $text = (string) ($value ?? '');

                    return preg_match('/^[\s]*[=+\-@]/u', $text)
                        ? "'".$text
                        : $text;
                };

                try {
                    fputcsv($stream, [
                        'Patient ID', 'Patient Code', 'Full Name', 'Phone',
                        'ID Country', 'ID Type', 'National ID', 'Date of Birth',
                        'Gender', 'Last Visit', 'Outstanding Balance',
                        'Currency', 'Status',
                    ], ',', '"', '');

                    $exportQuery->chunk(500, function ($patients) use (
                        $stream,
                        $safeText,
                        $currency
                    ) {
                        foreach ($patients as $patient) {
                            $idNumber = $patient->national_id_number
                                ?: $patient->emirates_id;

                            fputcsv($stream, [
                                $patient->patient_id,
                                $safeText($patient->patient_code),
                                $safeText($patient->full_name),
                                $safeText($patient->phone),
                                $safeText(
                                    $patient->national_id_country
                                        ?: ($patient->emirates_id ? 'AE' : '')
                                ),
                                $safeText(
                                    $patient->national_id_type
                                        ?: ($patient->emirates_id ? 'emirates_id' : '')
                                ),
                                $idNumber ? "'".$idNumber : '',
                                $patient->date_of_birth?->format('Y-m-d') ?? '',
                                $safeText($patient->gender),
                                $patient->last_visit_at?->format('Y-m-d H:i') ?? '',
                                $patient->balance_due,
                                $currency,
                                $safeText($patient->status),
                            ], ',', '"', '');
                        }
                    });
                } finally {
                    fclose($stream);
                }
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'private, no-store',
            ]);
        }

        $patients = $query->orderByDesc('patients.created_at')
            ->orderByDesc('patients.patient_id')
            ->paginate(100)->withQueryString();

        $clinicPatients = Patient::where('clinic_id', $clinicId);
        $totalPatients = (clone $clinicPatients)->count();
        $activePatients = (clone $clinicPatients)->where('status', 'active')->count();
        $inactivePatients = (clone $clinicPatients)->where('status', 'inactive')->count();

        $newPatientsThisMonth = (clone $clinicPatients)
            ->whereBetween('created_at', [now()->startOfMonth(), now()])
            ->count();

        $outstandingBalance = DB::query()
            ->fromSub(clone $balances, 'patient_balances')
            ->sum('balance_due');

        return view('patients.index', compact(
            'patients', 'totalPatients', 'activePatients', 'inactivePatients',
            'newPatientsThisMonth', 'outstandingBalance', 'currency', 'moneyDecimals'
        ));
    }

    private function patientBalancesQuery(int $clinicId): Builder
    {
        $currency = strtoupper(Auth::user()->clinic->currency ?: 'AED');

        $payments = DB::table('payments')
            ->where('clinic_id', $clinicId)
            ->where('currency', $currency)
            ->whereNotNull('bill_id')
            ->select('bill_id', 'patient_id')
            ->selectRaw('SUM(amount) AS received_amount')
            ->groupBy('bill_id', 'patient_id');

        return DB::table('bills as balance_bills')
            ->join('patients as balance_patients', function ($join) {
                $join->on(
                    'balance_patients.patient_id',
                    '=',
                    'balance_bills.patient_id'
                )->on(
                    'balance_patients.clinic_id',
                    '=',
                    'balance_bills.clinic_id'
                );
            })
            ->leftJoinSub($payments, 'bill_payments', function ($join) {
                $join->on(
                    'bill_payments.bill_id',
                    '=',
                    'balance_bills.bill_id'
                )->on(
                    'bill_payments.patient_id',
                    '=',
                    'balance_bills.patient_id'
                );
            })
            ->where('balance_bills.clinic_id', $clinicId)
            ->where('balance_bills.currency', $currency)
            ->where(fn ($query) => $query
                ->whereNull('balance_bills.status')
                ->orWhereNotIn('balance_bills.status', ['cancelled', 'void']))
            ->select('balance_bills.patient_id')
            ->selectRaw(
                'SUM(GREATEST(balance_bills.total_amount
                - COALESCE(bill_payments.received_amount, 0), 0)) AS balance_due'
            )
            ->groupBy('balance_bills.patient_id');
    }

    public function create()
    {
        $idCountry = $this->patientIdCountry();

        return view('patients.create', compact('idCountry'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePatient($request);

        DB::transaction(function () use ($validated) {
            $patient = Patient::create([
                ...$validated,
                'clinic_id' => Auth::user()->clinic_id,
                'patient_code' => 'TMP-'.(string) Str::ulid(),
                'status' => 'active',
            ]);

            $patient->update([
                'patient_code' => 'P-'.str_pad(
                    (string) $patient->patient_id,
                    4,
                    '0',
                    STR_PAD_LEFT
                ),
            ]);

            app(ClinicNotificationService::class)->patientAdded($patient);
        });

        return redirect()->route('patients.index')
            ->with('success', 'Patient created successfully.');
    }

    public function show(Patient $patient)
    {
        $this->ensureClinicAccess($patient);

        $clinicId = (int) Auth::user()->clinic_id;
        $patient->load('clinic');
        $currency = strtoupper($patient->clinic?->currency ?: 'AED');

        $recentVisits = $patient->visits()
            ->where('clinic_id', $clinicId)
            ->orderByDesc('visit_date')->orderByDesc('visit_id')
            ->limit(5)->get();

        $recentPrescriptions = $patient->prescriptions()
            ->where('clinic_id', $clinicId)
            ->orderByDesc('prescribed_at')->orderByDesc('prescription_id')
            ->limit(5)->get();

        $bills = $patient->bills()
            ->where('clinic_id', $clinicId)
            ->where(fn ($query) => $query->whereNull('status')
                ->orWhereNotIn('status', ['cancelled', 'void']))
            ->get();

        $payments = $patient->payments()->where('clinic_id', $clinicId)->get();

        $toCents = static function ($amount): int {
            [$whole, $fraction] = array_pad(explode('.', (string) $amount, 2), 2, '');

            return ((int) $whole * 100)
                + (int) str_pad(substr($fraction, 0, 2), 2, '0', STR_PAD_RIGHT);
        };

        $toDecimal = static fn (int $cents): string => intdiv($cents, 100).'.'
            .str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);

        $currencyCode = static function ($value): ?string {
            $code = strtoupper(trim((string) $value));

            return ClinicLocaleCatalog::knownCurrency($code) ? $code : null;
        };

        $emptyAccount = [
            'billed' => 0, 'paid' => 0, 'outstanding' => 0, 'unallocated' => 0,
        ];

        $totals = [$currency => $emptyAccount];
        $paymentsByBill = [];
        $unconfirmedFinancialRecords = 0;

        foreach ($payments as $payment) {
            $code = $currencyCode($payment->currency);

            if ($code === null) {
                $unconfirmedFinancialRecords++;

                continue;
            }

            $amount = $toCents($payment->amount);

            if ($payment->bill_id === null) {
                $totals[$code] ??= $emptyAccount;
                $totals[$code]['unallocated'] += $amount;
            } else {
                $billId = (int) $payment->bill_id;
                $paymentsByBill[$billId][$code] =
                    ($paymentsByBill[$billId][$code] ?? 0) + $amount;
            }
        }

        foreach ($bills as $bill) {
            $code = $currencyCode($bill->currency);

            if ($code === null) {
                $unconfirmedFinancialRecords++;

                continue;
            }

            $totals[$code] ??= $emptyAccount;
            $total = $toCents($bill->total_amount);
            $paid = $paymentsByBill[(int) $bill->bill_id][$code] ?? 0;

            $totals[$code]['billed'] += $total;
            $totals[$code]['paid'] += $paid;
            $totals[$code]['outstanding'] += max(0, $total - $paid);
        }

        ksort($totals);
        $accounts = [];
        $hasOutstanding = false;

        foreach ($totals as $code => $values) {
            $hasOutstanding = $hasOutstanding || $values['outstanding'] > 0;

            $accounts[] = [
                'currency' => $code,
                'billed' => $toDecimal($values['billed']),
                'paid' => $toDecimal($values['paid']),
                'outstanding' => $toDecimal($values['outstanding']),
                'unallocated' => $toDecimal($values['unallocated']),
            ];
        }

        $moneyDecimals = (string) Setting::where('clinic_id', $clinicId)
            ->where('setting_key', 'decimal_places')
            ->value('setting_value') === '0' ? 0 : 2;

        return view('patients.show', compact(
            'patient', 'recentVisits', 'recentPrescriptions', 'currency',
            'accounts', 'moneyDecimals', 'hasOutstanding',
            'unconfirmedFinancialRecords'
        ));
    }

    public function edit(Patient $patient)
    {
        $this->ensureClinicAccess($patient);
        $idCountry = $this->patientIdCountry($patient);

        return view('patients.create', compact('patient', 'idCountry'));
    }

    public function update(Request $request, Patient $patient)
    {
        $this->ensureClinicAccess($patient);
        $patient->update($this->validatePatient($request, $patient));

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Patient updated successfully.');
    }

    public function destroy(Patient $patient)
    {
        $this->ensureClinicAccess($patient);

        $hasRelatedRecords = $patient->visits()->exists()
            || $patient->prescriptions()->exists()
            || $patient->bills()->exists()
            || $patient->payments()->exists()
            || $patient->ledgerEntries()->exists();

        if ($hasRelatedRecords) {
            return redirect()->route('patients.index')->withErrors([
                'patient' => 'This patient has linked records and cannot be deleted.',
            ]);
        }

        $patient->delete();

        return redirect()->route('patients.index')
            ->with('success', 'Patient deleted successfully.');
    }

    private function ensureClinicAccess(Patient $patient): void
    {
        abort_unless(
            (int) $patient->clinic_id === (int) Auth::user()->clinic_id,
            404
        );
    }

    private function validatePatient(Request $request, ?Patient $patient = null): array
    {
        $clinicId = (int) Auth::user()->clinic_id;
        $country = $this->patientIdCountry($patient);

        $idType = match ($country) {
            'PK' => 'cnic',
            'AE' => 'emirates_id',
            default => 'other',
        };

        $idInput = $request->input('national_id_number');

        if (is_string($idInput)) {
            $idInput = $country === 'ZZ'
                ? trim($idInput)
                : preg_replace('/[\s-]+/', '', $idInput);

            $request->merge([
                'national_id_number' => $idInput === '' ? null : $idInput,
            ]);
        }

        $uniqueId = Rule::unique('patients', 'national_id_number')
            ->where('clinic_id', $clinicId)
            ->where('national_id_country', $country)
            ->where('national_id_type', $idType);

        if ($patient !== null) {
            $uniqueId->ignore($patient->patient_id, 'patient_id');
        }

        $idRules = [
            'bail', 'nullable', 'string', 'max:40',
            match ($country) {
                'PK' => 'regex:/^[0-9]{13}$/',
                'AE' => 'regex:/^784[0-9]{12}$/',
                default => 'not_regex:/[\x00-\x1F\x7F]/',
            },
            $uniqueId,
        ];

        if ($country === 'AE') {
            $idRules[] = function ($attribute, $value, $fail) use ($clinicId, $patient) {
                $query = Patient::where('clinic_id', $clinicId)->whereRaw(
                    "REPLACE(REPLACE(emirates_id, '-', ''), ' ', '') = ?",
                    [$value]
                );

                if ($patient !== null) {
                    $query->where('patient_id', '<>', $patient->patient_id);
                }

                if ($query->exists()) {
                    $fail('This Emirates ID is already registered in your clinic.');
                }
            };
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:160'],
            'father_or_husband_name' => ['nullable', 'string', 'max:160'],
            'phone' => [
                'bail', 'required', 'string', 'max:16', new InternationalPhone,
            ],
            'national_id_number' => $idRules,
            'date_of_birth' => [
                'bail', 'nullable', 'date_format:Y-m-d', 'before_or_equal:'.ClinicClock::now()->toDateString(),
            ],
            'gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],
            'marital_status' => ['required', Rule::in(['married', 'unmarried'])],
            'has_children' => [
                'exclude_unless:marital_status,married', 'required', 'boolean',
            ],
            'children_count' => [
                'exclude_unless:marital_status,married',
                'exclude_unless:has_children,1',
                'required', 'integer', 'min:1', 'max:65535',
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ], [
            'full_name.required' => 'Please enter the patient’s full name.',
            'phone.required' => 'Please enter the phone number.',
            'national_id_number.regex' => $country === 'PK'
                ? 'Enter a 13-digit CNIC.'
                : 'Enter a 15-digit Emirates ID starting with 784.',
            'national_id_number.not_regex' => 'The ID cannot contain control characters.',
            'national_id_number.max' => 'The ID must not exceed 40 characters.',
            'national_id_number.unique' => 'This ID is already registered in your clinic.',
            'date_of_birth.date_format' => 'Please enter a valid date of birth.',
            'date_of_birth.before_or_equal' => 'Date of birth cannot be in the future.',
            'gender.required' => 'Please select the gender.',
            'marital_status.required' => 'Please select the marital status.',
            'has_children.required' => 'Please select whether the patient has children.',
            'has_children.boolean' => 'Please select Yes or No for children.',
            'children_count.required' => 'Please enter the number of children.',
            'children_count.integer' => 'The number of children must be a whole number.',
            'children_count.min' => 'Enter at least 1 child when Yes is selected.',
        ]);

        $validated['national_id_number'] = $validated['national_id_number'] ?? null;
        $validated['date_of_birth'] = $validated['date_of_birth'] ?? null;
        $validated['national_id_country'] = $country;
        $validated['national_id_type'] = $idType;
        $validated['emirates_id'] = $country === 'AE'
            ? $validated['national_id_number']
            : null;

        if ($validated['marital_status'] !== 'married') {
            $validated['has_children'] = null;
            $validated['children_count'] = null;
        } else {
            $validated['has_children'] = (bool) $validated['has_children'];
            $validated['children_count'] = $validated['has_children']
                ? (int) $validated['children_count']
                : 0;
        }

        return $validated;
    }

    private function patientIdCountry(?Patient $patient = null): string
    {
        if ($patient !== null) {
            if (in_array($patient->national_id_country, ['AE', 'PK', 'ZZ'], true)) {
                return $patient->national_id_country;
            }

            if (filled($patient->emirates_id)) {
                return 'AE';
            }

            if (filled($patient->national_id_number)) {
                return 'ZZ';
            }
        }

        $country = Setting::where('clinic_id', Auth::user()->clinic_id)
            ->where('setting_key', 'patient_id_country')
            ->value('setting_value');

        if (in_array($country, ['AE', 'PK', 'ZZ'], true)) {
            return $country;
        }

        return ClinicLocaleCatalog::idCountry(Auth::user()->clinic->country_code);
    }
}
