<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VisitController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | VISITS — List
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $clinicId = Auth::user()->clinic_id;

        $query = Visit::query()
            ->where('clinic_id', $clinicId)
            ->with(['patient', 'recordedBy']);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($query) use ($search) {
                $query->where('visit_reason', 'like', "%{$search}%")
                    ->orWhere('diagnosis_name', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($patientQuery) use ($search) {
                        $patientQuery
                            ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('patient_code', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });

                if (ctype_digit($search)) {
                    $query->orWhere('visit_id', $search);
                }
            });
        }

        $visits = $query
            ->orderByDesc('visit_date')
            ->orderByDesc('visit_id')
            ->paginate(10)
            ->withQueryString();

        $today = now('Asia/Dubai');

        $totalVisits = Visit::where('clinic_id', $clinicId)->count();

        $todayVisits = Visit::where('clinic_id', $clinicId)
            ->whereDate('visit_date', $today->toDateString())
            ->count();

        $monthVisits = Visit::where('clinic_id', $clinicId)
            ->whereBetween('visit_date', [
                $today->copy()->startOfMonth()->format('Y-m-d H:i:s'),
                $today->copy()->endOfMonth()->format('Y-m-d H:i:s'),
            ])
            ->count();

        $followUpVisits = Visit::where('clinic_id', $clinicId)
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

    /*
    |--------------------------------------------------------------------------
    | VISITS — Create Form
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $patients = $this->patientOptions();

        return view('visits.create', compact('patients'));
    }

    /*
    |--------------------------------------------------------------------------
    | VISITS — Save
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $data = $this->validatedVisit($request);

        $data['clinic_id'] = Auth::user()->clinic_id;
        $data['recorded_by_user_id'] = Auth::id();

        Visit::create($data);

        return redirect()
            ->route('visits.index')
            ->with('success', 'Visit created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | VISITS — Profile
    |--------------------------------------------------------------------------
    */

    public function show(Visit $visit)
    {
        $this->ensureClinicAccess($visit);

        $visit->load(['patient', 'recordedBy']);

        return view('visits.show', compact('visit'));
    }

    /*
    |--------------------------------------------------------------------------
    | VISITS — Edit Form
    |--------------------------------------------------------------------------
    */

    public function edit(Visit $visit)
    {
        $this->ensureClinicAccess($visit);

        $patients = $this->patientOptions();

        return view('visits.create', compact('visit', 'patients'));
    }

    /*
    |--------------------------------------------------------------------------
    | VISITS — Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, Visit $visit)
    {
        $this->ensureClinicAccess($visit);

        $data = $this->validatedVisit($request, $visit);

        $visit->update($data);

        return redirect()
            ->route('visits.index')
            ->with('success', 'Visit updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | VISITS — Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(Visit $visit)
    {
        $this->ensureClinicAccess($visit);

        if ($visit->prescription()->exists() || $visit->bill()->exists()) {
            return redirect()
                ->route('visits.index')
                ->withErrors([
                    'visit' =>
                        'This visit has a prescription or bill and cannot be deleted.',
                ]);
        }

        $visit->delete();

        return redirect()
            ->route('visits.index')
            ->with('success', 'Visit deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | VISITS — Patient Options
    |--------------------------------------------------------------------------
    */

    private function patientOptions()
    {
        return Patient::query()
            ->where('clinic_id', Auth::user()->clinic_id)
            ->orderBy('full_name')
            ->get([
                'patient_id',
                'patient_code',
                'full_name',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VISITS — Clinic Access
    |--------------------------------------------------------------------------
    */

    private function ensureClinicAccess(Visit $visit): void
    {
        abort_unless(
            (int) $visit->clinic_id ===
                (int) Auth::user()->clinic_id,
            404
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VISITS — Validation and Date/Time
    |--------------------------------------------------------------------------
    */

    private function validatedVisit(
        Request $request,
        ?Visit $visit = null
    ): array {
        $patientRules = [
            'required',
            'integer',
            Rule::exists('patients', 'patient_id')
                ->where('clinic_id', Auth::user()->clinic_id),
        ];

        // Keep the patient unchanged when editing an existing visit.
        if ($visit !== null) {
            $patientRules[] = Rule::in([$visit->patient_id]);
        }

        $data = $request->validate([
            'patient_id' => $patientRules,

            'visit_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:1000-01-01',
                'before_or_equal:9999-12-31',
            ],

            'visit_time' => [
                'required',
                'date_format:H:i',
            ],

            'visit_reason' => [
                'required',
                Rule::in([
                    'General visit',
                    'Follow-up',
                    'New consultation',
                    'Prescription refill',
                ]),
            ],

            'diagnosis_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'general_notes' => [
                'nullable',
                'string',
            ],
        ], [
            'patient_id.required' =>
                'Please select a patient.',

            'patient_id.exists' =>
                'Please select a patient registered in your clinic.',

            'patient_id.in' =>
                'The patient cannot be changed for an existing visit.',

            'visit_date.required' =>
                'Please select the visit date.',

            'visit_time.required' =>
                'Please enter the visit time.',

            'visit_reason.required' =>
                'Please select the visit type.',

            'diagnosis_name.max' =>
                'The diagnosis name must not exceed 255 characters.',
        ]);

        // Store the entered UAE local date and time in the existing DATETIME column.
        $data['visit_date'] =
            $data['visit_date'] . ' ' . $data['visit_time'] . ':00';

        unset($data['visit_time']);

        return $data;
    }
}