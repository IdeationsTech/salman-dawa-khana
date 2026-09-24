<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PATIENTS — List and Search
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'status' => [
                'nullable',
                Rule::in(['all', 'active', 'inactive']),
            ],
        ]);

        $clinicId = Auth::user()->clinic_id;

        $query = Patient::query()
            ->where('clinic_id', $clinicId);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $normalizedId = preg_replace('/[\s-]+/', '', $search);

            $query->where(function ($query) use ($search, $normalizedId) {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('patient_code', 'like', "%{$search}%");

                if ($normalizedId !== '') {
                    $query->orWhere(
                        'emirates_id',
                        'like',
                        "%{$normalizedId}%"
                    );
                }

                if (ctype_digit($search)) {
                    $query->orWhere('patient_id', $search);
                }
            });
        }

        if (
            !empty($filters['status']) &&
            $filters['status'] !== 'all'
        ) {
            $query->where('status', $filters['status']);
        }

        $patients = $query
            ->latest('created_at')
            ->orderByDesc('patient_id')
            ->paginate(10)
            ->withQueryString();

        $totalPatients = Patient::where('clinic_id', $clinicId)
            ->count();

        $activePatients = Patient::where('clinic_id', $clinicId)
            ->where('status', 'active')
            ->count();

        $inactivePatients = Patient::where('clinic_id', $clinicId)
            ->where('status', 'inactive')
            ->count();

        $newPatientsThisMonth = Patient::where('clinic_id', $clinicId)
            ->whereBetween('created_at', [
                now()->startOfMonth(),
                now(),
            ])
            ->count();

        return view('patients.index', compact(
            'patients',
            'totalPatients',
            'activePatients',
            'inactivePatients',
            'newPatientsThisMonth'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | PATIENTS — Create Form
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('patients.create');
    }

/*
|--------------------------------------------------------------------------
| PATIENTS — Save with Short Patient Code
|--------------------------------------------------------------------------
*/

    public function store(Request $request)
    {
        $validated = $this->validatePatient($request);

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated) {
            $patient = Patient::create([
                ...$validated,
                'clinic_id' => Auth::user()->clinic_id,
                'patient_code' => 'TMP-' . (string) Str::ulid(),
                'status' => 'active',
            ]);

            $patient->update([
                'patient_code' => 'P-' . str_pad(
                    (string) $patient->patient_id,
                    4,
                    '0',
                    STR_PAD_LEFT
                ),
            ]);
        });

        return redirect()
            ->route('patients.index')
            ->with('success', 'Patient created successfully.');
    }

    /*
|--------------------------------------------------------------------------
| PATIENTS — Dynamic Patient Profile
|--------------------------------------------------------------------------
*/

    public function show(Patient $patient)
    {
        $this->ensureClinicAccess($patient);

        $clinicId = (int) Auth::user()->clinic_id;

        $patient->load('clinic');

        $recentVisits = $patient->visits()
            ->where('clinic_id', $clinicId)
            ->orderByDesc('visit_date')
            ->orderByDesc('visit_id')
            ->limit(5)
            ->get();

        $recentPrescriptions = $patient->prescriptions()
            ->where('clinic_id', $clinicId)
            ->orderByDesc('prescribed_at')
            ->orderByDesc('prescription_id')
            ->limit(5)
            ->get();

        $bills = $patient->bills()
            ->where('clinic_id', $clinicId)
            ->whereNotIn('status', ['cancelled', 'void'])
            ->withSum([
                'payments as recorded_paid' => function ($query) use ($clinicId) {
                    $query->where('clinic_id', $clinicId);
                },
            ], 'amount')
            ->get();

        // Calculate balances using integer minor units.
        $toCents = static function ($amount): int {
            [$whole, $fraction] = array_pad(
                explode('.', (string) $amount, 2),
                2,
                ''
            );

            return ((int) $whole * 100)
                + (int) str_pad(substr($fraction, 0, 2), 2, '0');
        };

        $billedCents = 0;
        $paidCents = 0;
        $dueCents = 0;

        foreach ($bills as $bill) {
            $total = $toCents($bill->total_amount);
            $paid = $toCents($bill->recorded_paid ?? '0');

            $billedCents += $total;
            $paidCents += $paid;
            $dueCents += max(0, $total - $paid);
        }

        $totalBilled = $billedCents / 100;
        $paidAgainstBills = $paidCents / 100;
        $outstandingBalance = $dueCents / 100;

        $unallocatedPayments = $patient->payments()
            ->where('clinic_id', $clinicId)
            ->whereNull('bill_id')
            ->sum('amount');

        $currency = strtoupper($patient->clinic?->currency ?: 'AED');

        return view('patients.show', compact(
            'patient',
            'recentVisits',
            'recentPrescriptions',
            'totalBilled',
            'paidAgainstBills',
            'outstandingBalance',
            'unallocatedPayments',
            'currency'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | PATIENTS — Edit Form
    |--------------------------------------------------------------------------
    */

    public function edit(Patient $patient)
    {
        $this->ensureClinicAccess($patient);

        return view('patients.create', compact('patient'));
    }

    /*
    |--------------------------------------------------------------------------
    | PATIENTS — Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, Patient $patient)
    {
        $this->ensureClinicAccess($patient);

        $validated = $this->validatePatient($request, $patient);

        $patient->update($validated);

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', 'Patient updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | PATIENTS — Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(Patient $patient)
    {
        $this->ensureClinicAccess($patient);

        $hasRelatedRecords =
            $patient->visits()->exists() ||
            $patient->prescriptions()->exists() ||
            $patient->bills()->exists() ||
            $patient->payments()->exists() ||
            $patient->ledgerEntries()->exists();

        if ($hasRelatedRecords) {
            return redirect()
                ->route('patients.index')
                ->withErrors([
                    'patient' =>
                        'This patient has linked records and cannot be deleted.',
                ]);
        }

        $patient->delete();

        return redirect()
            ->route('patients.index')
            ->with('success', 'Patient deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | PATIENTS — Clinic Access
    |--------------------------------------------------------------------------
    */

    private function ensureClinicAccess(Patient $patient): void
    {
        abort_unless(
            (int) $patient->clinic_id ===
                (int) Auth::user()->clinic_id,
            404
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PATIENTS — Validation
    |--------------------------------------------------------------------------
    */

    private function validatePatient(
        Request $request,
        ?Patient $patient = null
    ): array {
        $emiratesId = $request->input('emirates_id');

        if (is_string($emiratesId)) {
            $request->merge([
                'emirates_id' => preg_replace(
                    '/[\s-]+/',
                    '',
                    $emiratesId
                ),
            ]);
        }

        $uniqueEmiratesId = Rule::unique(
            'patients',
            'emirates_id'
        )->where('clinic_id', Auth::user()->clinic_id);

        if ($patient !== null) {
            $uniqueEmiratesId->ignore(
                $patient->patient_id,
                'patient_id'
            );
        }

        $validated = $request->validate([
            'full_name' => [
                'required',
                'string',
                'max:160',
            ],

            'father_or_husband_name' => [
                'nullable',
                'string',
                'max:160',
            ],

            'phone' => [
                'bail',
                'required',
                'string',
                'max:30',
                'regex:/^\+?[0-9][0-9\s().-]{5,29}$/',
            ],

            'emirates_id' => [
                'bail',
                'required',
                'string',
                'regex:/^784[0-9]{12}$/',
                $uniqueEmiratesId,
            ],

            'date_of_birth' => [
                'bail',
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],

            'gender' => [
                'required',
                Rule::in(['Male', 'Female', 'Other']),
            ],

            'marital_status' => [
                'required',
                Rule::in(['married', 'unmarried']),
            ],

            'has_children' => [
                'exclude_unless:marital_status,married',
                'required',
                'boolean',
            ],

            'children_count' => [
                'exclude_unless:marital_status,married',
                'exclude_unless:has_children,1',
                'required',
                'integer',
                'min:1',
                'max:65535',
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ], [
            'full_name.required' =>
                'Please enter the patient’s full name.',

            'phone.required' =>
                'Please enter the phone number.',

            'phone.regex' =>
                'Please enter a valid phone number.',

            'emirates_id.required' =>
                'Please enter the Emirates ID.',

            'emirates_id.regex' =>
                'Enter a 15-digit Emirates ID starting with 784.',

            'emirates_id.unique' =>
                'This Emirates ID is already registered in your clinic.',

            'date_of_birth.required' =>
                'Please enter the date of birth.',

            'date_of_birth.date_format' =>
                'Please enter a valid date of birth.',

            'date_of_birth.before_or_equal' =>
                'Date of birth cannot be in the future.',

            'gender.required' =>
                'Please select the gender.',

            'marital_status.required' =>
                'Please select the marital status.',

            'has_children.required' =>
                'Please select whether the patient has children.',

            'has_children.boolean' =>
                'Please select Yes or No for children.',

            'children_count.required' =>
                'Please enter the number of children.',

            'children_count.integer' =>
                'The number of children must be a whole number.',

            'children_count.min' =>
                'Enter at least 1 child when Yes is selected.',
        ]);

        // Clear fields that no longer apply when editing a patient.
        if ($validated['marital_status'] !== 'married') {
            $validated['has_children'] = null;
            $validated['children_count'] = null;
        } else {
            $validated['has_children'] =
                (bool) $validated['has_children'];

            $validated['children_count'] =
                $validated['has_children']
                    ? (int) $validated['children_count']
                    : 0;
        }

        return $validated;
    }
}