<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Bill;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use App\Support\ClinicClock;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VisitController extends Controller
{
    private const VISIT_REASONS = [
        'General visit',
        'Follow-up',
        'New consultation',
        'Prescription refill',
    ];

    private const STATUSES = [
        'in_progress',
        'completed',
        'follow_up_required',
        'cancelled',
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'date_range' => [
                'nullable',
                Rule::in([
                    'all dates',
                    'today',
                    'this week',
                    'this month',
                ]),
            ],
            'visit_type' => [
                'nullable',
                Rule::in(['all', ...self::VISIT_REASONS]),
            ],
            'status' => [
                'nullable',
                Rule::in(['all', 'not_recorded', ...self::STATUSES]),
            ],
        ]);

        $clinicId = (int) Auth::user()->clinic_id;
        $today = ClinicClock::now();

        $query = Visit::query()
            ->where('clinic_id', $clinicId)
            ->with([
                'patient' => fn ($query) => $query->where('clinic_id', $clinicId),
                'recordedBy' => fn ($query) => $query->where('clinic_id', $clinicId),
                'doctor' => fn ($query) => $query->where('clinic_id', $clinicId),
            ]);

        $search = trim($filters['search'] ?? '');

        if ($search !== '') {
            $query->where(function ($query) use ($search, $clinicId) {
                $query
                    ->where('visit_reason', 'like', "%{$search}%")
                    ->orWhere('diagnosis_name', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($patientQuery) use (
                        $search,
                        $clinicId
                    ) {
                        $patientQuery
                            ->where('clinic_id', $clinicId)
                            ->where(function ($patientQuery) use ($search) {
                                $patientQuery
                                    ->where('full_name', 'like', "%{$search}%")
                                    ->orWhere('patient_code', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%");
                            });
                    });

                if (ctype_digit($search)) {
                    $query->orWhere('visit_id', $search);
                }
            });
        }

        $range = $filters['date_range'] ?? 'all dates';
        $start = null;
        $end = null;

        if ($range === 'today') {
            $start = $today->copy()->startOfDay();
            $end = $start->copy()->addDay();
        } elseif ($range === 'this week') {
            $start = $today->copy()->startOfWeek(1);
            $end = $start->copy()->addWeek();
        } elseif ($range === 'this month') {
            $start = $today->copy()->startOfMonth();
            $end = $start->copy()->addMonth();
        }

        if ($start !== null) {
            $query
                ->where('visit_date', '>=', $start->copy()->setTimezone(ClinicClock::STORAGE_ZONE)->format('Y-m-d H:i:s'))
                ->where('visit_date', '<', $end->copy()->setTimezone(ClinicClock::STORAGE_ZONE)->format('Y-m-d H:i:s'));
        }

        $type = $filters['visit_type'] ?? 'all';

        if ($type !== 'all') {
            $query->where('visit_reason', $type);
        }

        $status = $filters['status'] ?? 'all';

        if ($status === 'not_recorded') {
            $query->whereNull('status');
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        $visits = $query
            ->orderByDesc('visit_date')
            ->orderByDesc('visit_id')
            ->paginate(100)
            ->withQueryString();

        $base = Visit::query()->where('clinic_id', $clinicId);

        $totalVisits = (clone $base)->count();

        $todayVisits = (clone $base)
            ->where('visit_date', '>=', ClinicClock::stored($today->copy()->startOfDay()))
            ->where('visit_date', '<', ClinicClock::stored($today->copy()->startOfDay()->addDay()))
            ->count();

        $monthVisits = (clone $base)
            ->where('visit_date', '>=', $today->copy()->startOfMonth()->copy()->setTimezone(ClinicClock::STORAGE_ZONE)->format('Y-m-d H:i:s'))
            ->where('visit_date', '<', $today->copy()->startOfMonth()->addMonth()->copy()->setTimezone(ClinicClock::STORAGE_ZONE)->format('Y-m-d H:i:s'))
            ->count();

        $followUpVisits = (clone $base)
            ->where('visit_reason', 'Follow-up')
            ->count();

        return view('visits.index', compact(
            'visits',
            'totalVisits',
            'todayVisits',
            'monthVisits',
            'followUpVisits'
        ));
    }

    public function create()
    {
        $patients = $this->patientOptions($visit ?? null);
        $doctors = $this->doctorOptions();

        return view('visits.create', compact('patients', 'doctors'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedVisit($request);

        Visit::create([
            ...$data,
            'clinic_id' => Auth::user()->clinic_id,
            'recorded_by_user_id' => Auth::id(),
        ]);

        return redirect()
            ->route('visits.index')
            ->with('success', 'Visit created successfully.');
    }

    public function show(Visit $visit)
    {
        $this->ensureClinicAccess($visit);

        $clinicId = (int) Auth::user()->clinic_id;

        $visit->load([
            'clinic',
            'patient' => fn ($query) => $query->where('clinic_id', $clinicId),
            'recordedBy' => fn ($query) => $query->where('clinic_id', $clinicId),
            'doctor' => fn ($query) => $query->where('clinic_id', $clinicId)->with('role'),
        ]);

        $currency = strtoupper($visit->clinic?->currency ?: 'AED');
        $patientBills = $visit->patient
            ? Bill::query()
                ->where('clinic_id', $clinicId)
                ->where('patient_id', $visit->patient_id)
                ->where('currency', $currency)
                ->where(fn ($query) => $query->whereNull('status')
                    ->orWhereNotIn('status', ['cancelled', 'void']))
                ->withSum([
                    'payments as recorded_paid' => fn ($query) => $query
                        ->where('clinic_id', $clinicId)
                        ->whereColumn('payments.patient_id', 'bills.patient_id')
                        ->whereColumn('payments.currency', 'bills.currency'),
                ], 'amount')
                ->get(['bill_id', 'patient_id', 'total_amount'])
            : collect();

        $outstandingCents = $patientBills->sum(fn ($bill) => max(
            0,
            Money::scaled($bill->total_amount)
                - Money::scaled($bill->recorded_paid ?? '0')
        ));
        $patientOutstanding = Money::decimal($outstandingCents);

        $visitBill = $visit->bill()
            ->where('clinic_id', $clinicId)
            ->where('patient_id', $visit->patient_id)
            ->first();

        return view('visits.show', compact(
            'visit',
            'currency',
            'patientOutstanding',
            'visitBill'
        ));
    }

    public function edit(Visit $visit)
    {
        $this->ensureClinicAccess($visit);

        $patients = $this->patientOptions($visit ?? null);
        $doctors = $this->doctorOptions($visit);

        return view('visits.create', compact(
            'visit',
            'patients',
            'doctors'
        ));
    }

    public function update(Request $request, Visit $visit)
    {
        $this->ensureClinicAccess($visit);

        $visit->update($this->validatedVisit($request, $visit));

        return redirect()
            ->route('visits.index')
            ->with('success', 'Visit updated successfully.');
    }

    public function destroy(Visit $visit)
    {
        $this->ensureClinicAccess($visit);

        if ($visit->prescription()->exists() || $visit->bill()->exists()) {
            return redirect()
                ->route('visits.index')
                ->withErrors([
                    'visit' => 'This visit has a prescription or bill and cannot be deleted.',
                ]);
        }

        $visit->delete();

        return redirect()
            ->route('visits.index')
            ->with('success', 'Visit deleted successfully.');
    }

    private function patientOptions(?Visit $visit = null)
    {
        return Patient::query()
            ->where('clinic_id', Auth::user()->clinic_id)
            ->whereKey($visit?->patient_id ?? (filter_var(old('patient_id', request('patient_id')), FILTER_VALIDATE_INT) ?: 0))
            ->get(['patient_id', 'patient_code', 'full_name']);
    }

    private function doctorOptions(?Visit $visit = null)
    {
        $roleIds = Role::query()
            ->whereIn('name', ['Owner', 'Doctor'])
            ->pluck('role_id')
            ->all();

        return User::query()
            ->where('clinic_id', Auth::user()->clinic_id)
            ->where(function ($query) use ($roleIds, $visit) {
                $query->where(function ($eligible) use ($roleIds) {
                    $eligible
                        ->where('is_active', true)
                        ->whereIn('role_id', $roleIds);
                });

                if ($visit?->doctor_user_id) {
                    $query->orWhere('user_id', $visit->doctor_user_id);
                }
            })
            ->with('role')
            ->orderBy('name')
            ->get([
                'user_id',
                'clinic_id',
                'role_id',
                'name',
                'is_active',
            ]);
    }

    private function ensureClinicAccess(Visit $visit): void
    {
        abort_unless(
            (int) $visit->clinic_id === (int) Auth::user()->clinic_id,
            404
        );
    }

    private function validatedVisit(
        Request $request,
        ?Visit $visit = null
    ): array {
        $clinicId = (int) Auth::user()->clinic_id;

        $patientRules = [
            'required',
            'integer',
            Rule::exists('patients', 'patient_id')
                ->where('clinic_id', $clinicId),
        ];

        if ($visit !== null) {
            $patientRules[] = Rule::in([$visit->patient_id]);
        }

        $roleIds = Role::query()
            ->whereIn('name', ['Owner', 'Doctor'])
            ->pluck('role_id')
            ->all();

        $doctorRule = Rule::exists('users', 'user_id')
            ->where('clinic_id', $clinicId)
            ->where(function ($query) use ($roleIds, $visit) {
                $query->where(function ($eligible) use ($roleIds) {
                    $eligible
                        ->where('is_active', true)
                        ->whereIn('role_id', $roleIds);
                });

                if ($visit?->doctor_user_id) {
                    $query->orWhere('user_id', $visit->doctor_user_id);
                }
            });

        $data = $request->validate([
            'patient_id' => $patientRules,
            'doctor_user_id' => ['required', 'integer', $doctorRule],

            'visit_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:1000-01-01',
                'before_or_equal:9999-12-31',
            ],

            'visit_time' => ['required', 'date_format:H:i'],

            'visit_reason' => [
                'required',
                Rule::in(self::VISIT_REASONS),
            ],

            'status' => [
                'required',
                Rule::in(self::STATUSES),
            ],

            'diagnosis_name' => ['nullable', 'string', 'max:255'],
            'general_notes' => ['nullable', 'string'],
        ], [
            'patient_id.required' => 'Please select a patient.',
            'patient_id.exists' => 'Select a patient from your clinic.',
            'patient_id.in' => 'The patient cannot be changed for an existing visit.',

            'doctor_user_id.required' => 'Please select the treating Doctor/Hakeem.',
            'doctor_user_id.exists' => 'Select an active Owner or Doctor from your clinic.',

            'visit_date.required' => 'Please select the visit date.',
            'visit_time.required' => 'Please enter the visit time.',
            'visit_reason.required' => 'Please select the visit type.',
            'status.required' => 'Please select the visit status.',
            'status.in' => 'Please select a valid visit status.',
            'diagnosis_name.max' => 'The diagnosis name must not exceed 255 characters.',
        ]);

        $data['doctor_user_id'] = (int) $data['doctor_user_id'];

        $data['visit_date'] =
            $data['visit_date'].' '.$data['visit_time'].':00';

        unset($data['visit_time']);

        return $data;
    }
}
