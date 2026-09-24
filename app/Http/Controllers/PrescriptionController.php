<?php

namespace App\Http\Controllers;

use App\Models\NuskhaTemplate;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PrescriptionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PRESCRIPTIONS — List
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => [
                'nullable',
                Rule::in(['all', 'issued', 'draft', 'cancelled']),
            ],
            'date_range' => [
                'nullable',
                Rule::in(['all dates', 'today', 'this week', 'this month']),
            ],
        ]);

        $clinicId = Auth::user()->clinic_id;
        $now = now('Asia/Dubai');

        $query = Prescription::query()
            ->where('clinic_id', $clinicId)
            ->with('patient')
            ->withCount('items');

        $search = trim($filters['search'] ?? '');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('prescription_no', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($patientQuery) use ($search) {
                        $patientQuery
                            ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('patient_code', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });

                if (ctype_digit($search)) {
                    $query->orWhere('patient_id', $search);
                }
            });
        }

        $status = $filters['status'] ?? 'all';

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $range = $filters['date_range'] ?? 'all dates';

        if ($range === 'today') {
            $query->whereDate('prescribed_at', $now->toDateString());
        } elseif ($range === 'this week') {
            $query->whereBetween('prescribed_at', [
                $now->copy()->startOfWeek(1)->format('Y-m-d H:i:s'),
                $now->copy()->startOfWeek(1)->addDays(6)->endOfDay()
                    ->format('Y-m-d H:i:s'),
            ]);
        } elseif ($range === 'this month') {
            $query->whereBetween('prescribed_at', [
                $now->copy()->startOfMonth()->format('Y-m-d H:i:s'),
                $now->copy()->endOfMonth()->format('Y-m-d H:i:s'),
            ]);
        }

        $prescriptions = $query
            ->orderByDesc('prescribed_at')
            ->orderByDesc('prescription_id')
            ->paginate(10)
            ->withQueryString();

        $totalPrescriptions = Prescription::where('clinic_id', $clinicId)
            ->count();

        $monthlyPrescriptions = Prescription::where('clinic_id', $clinicId)
            ->whereBetween('prescribed_at', [
                $now->copy()->startOfMonth()->format('Y-m-d H:i:s'),
                $now->copy()->endOfMonth()->format('Y-m-d H:i:s'),
            ])
            ->count();

        $todayPrescriptions = Prescription::where('clinic_id', $clinicId)
            ->whereDate('prescribed_at', $now->toDateString())
            ->count();

        $savedTemplates = NuskhaTemplate::where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->count();

        return view('prescriptions.index', compact(
            'prescriptions',
            'totalPrescriptions',
            'monthlyPrescriptions',
            'todayPrescriptions',
            'savedTemplates'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | PRESCRIPTIONS — Create and Store
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('prescriptions.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validatePrescription($request);

        $prescription = DB::transaction(function () use ($data) {
            $prescription = Prescription::create([
                'clinic_id' => Auth::user()->clinic_id,
                'patient_id' => $data['patient_id'],
                'visit_id' => $data['visit_id'] ?? null,
                'created_by_user_id' => Auth::id(),
                'prescription_no' => 'RX-' . (string) Str::ulid(),
                'prescribed_at' => $data['prescribed_at'],
                'general_instructions' =>
                    $data['general_instructions'] ?? null,
                'status' => $data['status'],
            ]);

            $this->saveItems($prescription, $data['items']);

            return $prescription;
        });

        return redirect()
            ->route('prescriptions.show', $prescription)
            ->with('success', 'Prescription created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | PRESCRIPTIONS — Show
    |--------------------------------------------------------------------------
    */

    public function show(Prescription $prescription)
    {
        $this->ensureClinicAccess($prescription);

        $prescription->load([
            'clinic',
            'patient',
            'visit',
            'createdBy',
            'items',
        ]);

        return view('prescriptions.show', compact('prescription'));
    }

    /*
    |--------------------------------------------------------------------------
    | PRESCRIPTIONS — Edit and Update
    |--------------------------------------------------------------------------
    */

    public function edit(Prescription $prescription)
    {
        $this->ensureClinicAccess($prescription);

        $prescription->load('items');

        return view('prescriptions.create', array_merge(
            $this->formData(),
            ['prescription' => $prescription]
        ));
    }

    public function update(Request $request, Prescription $prescription)
    {
        $this->ensureClinicAccess($prescription);

        $data = $this->validatePrescription($request, $prescription);

        DB::transaction(function () use ($prescription, $data) {
            $prescription->update([
                'patient_id' => $data['patient_id'],
                'visit_id' => $data['visit_id'] ?? null,
                'prescribed_at' => $data['prescribed_at'],
                'general_instructions' =>
                    $data['general_instructions'] ?? null,
                'status' => $data['status'],
            ]);

            $prescription->items()->delete();

            $this->saveItems($prescription, $data['items']);
        });

        return redirect()
            ->route('prescriptions.show', $prescription)
            ->with('success', 'Prescription updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | PRESCRIPTIONS — Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(Prescription $prescription)
    {
        $this->ensureClinicAccess($prescription);

        DB::transaction(function () use ($prescription) {
            $prescription->items()->delete();
            $prescription->delete();
        });

        return redirect()
            ->route('prescriptions.index')
            ->with('success', 'Prescription deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | PRESCRIPTIONS — Form Data
    |--------------------------------------------------------------------------
    */

    private function formData(): array
    {
        $clinicId = Auth::user()->clinic_id;

        $patients = Patient::where('clinic_id', $clinicId)
            ->orderBy('full_name')
            ->get(['patient_id', 'patient_code', 'full_name']);

        $visits = Visit::where('clinic_id', $clinicId)
            ->orderByDesc('visit_date')
            ->get([
                'visit_id',
                'patient_id',
                'visit_date',
                'visit_reason',
            ]);

        $templates = NuskhaTemplate::where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->with('items')
            ->orderBy('name')
            ->get();

        // Only send the fields needed to populate the prescription form.
        $templateData = $templates->map(function ($template) {
            return [
                'id' => $template->nuskha_template_id,
                'instructions' => $template->instructions,
                'items' => $template->items->map(function ($item) {
                    return [
                        'item_name' => $item->item_name,
                        'dosage' => $item->dosage,
                        'frequency' => $item->frequency,
                        'duration' => $item->duration,
                        'timing' => $item->timing,
                        'instructions' => $item->instructions,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return compact('patients', 'visits', 'templates', 'templateData');
    }

    /*
    |--------------------------------------------------------------------------
    | PRESCRIPTIONS — Validation
    |--------------------------------------------------------------------------
    */

    private function validatePrescription(
        Request $request,
        ?Prescription $prescription = null
    ): array {
        $clinicId = Auth::user()->clinic_id;

        // Validate the patient first, then validate the visit against that patient.
        $patientRules = [
            'required',
            'integer',
            Rule::exists('patients', 'patient_id')
                ->where('clinic_id', $clinicId),
        ];

        if ($prescription !== null) {
            $patientRules[] = Rule::in([$prescription->patient_id]);
        }

        $patientData = $request->validate([
            'patient_id' => $patientRules,
        ], [
            'patient_id.required' => 'Please select a patient.',
            'patient_id.exists' => 'Select a patient from your clinic.',
            'patient_id.in' =>
                'The patient cannot be changed on an existing prescription.',
        ]);

        $data = $request->validate([
            'visit_id' => [
                'nullable',
                'integer',
                Rule::exists('visits', 'visit_id')
                    ->where('clinic_id', $clinicId)
                    ->where('patient_id', $patientData['patient_id']),
            ],

            'prescribed_at' => [
                'bail',
                'required',
                'date_format:Y-m-d\TH:i',
                'after_or_equal:1000-01-01',
                'before_or_equal:9999-12-31 23:59:59',
            ],

            'status' => [
                'required',
                Rule::in(['issued', 'draft', 'cancelled']),
            ],

            'prescription_type' => [
                'required',
                Rule::in(['custom', 'template']),
            ],

            'template_id' => [
                'exclude_unless:prescription_type,template',
                'required',
                'integer',
                Rule::exists('nuskha_templates', 'nuskha_template_id')
                    ->where('clinic_id', $clinicId)
                    ->where('is_active', true),
            ],

            'general_instructions' => [
                'nullable',
                'string',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],

            'items.*' => ['required', 'array'],

            'items.*.item_name' => [
                'required',
                'string',
                'max:160',
            ],

            'items.*.dosage' => ['nullable', 'string', 'max:80'],
            'items.*.frequency' => ['nullable', 'string', 'max:80'],
            'items.*.duration' => ['nullable', 'string', 'max:80'],
            'items.*.timing' => ['nullable', 'string', 'max:80'],
            'items.*.instructions' => ['nullable', 'string', 'max:255'],
        ], [
            'visit_id.exists' =>
                'Select a visit belonging to the selected patient.',

            'prescribed_at.required' =>
                'Please enter the prescription date and time.',

            'template_id.required' =>
                'Please select a saved nuskha template.',

            'template_id.exists' =>
                'The selected template is unavailable.',

            'items.required' =>
                'Please add at least one prescription item.',

            'items.*.item_name.required' =>
                'Every prescription item must have a name.',
        ]);

        $data['patient_id'] = $patientData['patient_id'];

        $data['prescribed_at'] =
            str_replace('T', ' ', $data['prescribed_at']) . ':00';

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | PRESCRIPTIONS — Save Item Snapshot
    |--------------------------------------------------------------------------
    */

    private function saveItems(
        Prescription $prescription,
        array $items
    ): void {
        foreach (array_values($items) as $index => $item) {
            $prescription->items()->create([
                'item_name' => $item['item_name'],
                'dosage' => $item['dosage'] ?? null,
                'frequency' => $item['frequency'] ?? null,
                'duration' => $item['duration'] ?? null,
                'timing' => $item['timing'] ?? null,
                'instructions' => $item['instructions'] ?? null,
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function ensureClinicAccess(
        Prescription $prescription
    ): void {
        abort_unless(
            (int) $prescription->clinic_id ===
                (int) Auth::user()->clinic_id,
            404
        );
    }
}