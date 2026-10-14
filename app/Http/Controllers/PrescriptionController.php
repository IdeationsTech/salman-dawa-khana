<?php

namespace App\Http\Controllers;

use App\Models\NuskhaItem;
use App\Models\NuskhaTemplate;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PrescriptionController extends Controller
{
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

        $clinicId = (int) Auth::user()->clinic_id;
        $now = now('Asia/Dubai');

        $query = Prescription::query()
            ->where('clinic_id', $clinicId)
            ->with([
                'patient' => fn ($query) =>
                    $query->where('clinic_id', $clinicId),
                'createdBy' => fn ($query) =>
                    $query->where('clinic_id', $clinicId),
                'doctor' => fn ($query) =>
                    $query->where('clinic_id', $clinicId),
            ])
            ->withCount('items');

        $search = trim($filters['search'] ?? '');

        if ($search !== '') {
            $query->where(function ($query) use ($search, $clinicId) {
                $query
                    ->where('prescription_no', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($patients) use (
                        $search,
                        $clinicId
                    ) {
                        $patients
                            ->where('clinic_id', $clinicId)
                            ->where(function ($patients) use ($search) {
                                $patients
                                    ->where('full_name', 'like', "%{$search}%")
                                    ->orWhere('patient_code', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%");
                            });
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

        $base = Prescription::query()->where('clinic_id', $clinicId);

        $totalPrescriptions = (clone $base)->count();

        $monthlyPrescriptions = (clone $base)
            ->whereBetween('prescribed_at', [
                $now->copy()->startOfMonth()->format('Y-m-d H:i:s'),
                $now->copy()->endOfMonth()->format('Y-m-d H:i:s'),
            ])
            ->count();

        $todayPrescriptions = (clone $base)
            ->whereDate('prescribed_at', $now->toDateString())
            ->count();

        $savedTemplates = NuskhaTemplate::query()
            ->where('clinic_id', $clinicId)
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

    public function create()
    {
        return view('prescriptions.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validatePrescription($request);
        $nuskhaName = $this->resolveNuskhaName($data);

        $prescription = DB::transaction(function () use ($data, $nuskhaName) {
            $prescription = Prescription::create([
                'clinic_id' => Auth::user()->clinic_id,
                'patient_id' => $data['patient_id'],
                'visit_id' => $data['visit_id'] ?? null,
                'created_by_user_id' => Auth::id(),
                'doctor_user_id' => $data['doctor_user_id'],
                'prescription_no' => 'TMP-' . (string) Str::ulid(),
                'nuskha_name' => $nuskhaName,
                'prescribed_at' => $data['prescribed_at'],
                'general_instructions' => $data['general_instructions'] ?? null,
                'status' => $data['status'],
            ]);

            $prescription->update([
                'prescription_no' => 'RX' . str_pad(
                    (string) $prescription->prescription_id,
                    2,
                    '0',
                    STR_PAD_LEFT
                ),
            ]);

            $this->saveItems($prescription, $data['items']);

            return $prescription;
        });

        return redirect()
            ->route('prescriptions.show', $prescription)
            ->with('success', 'Prescription created successfully.');
    }

    public function show(Prescription $prescription)
    {
        $this->ensureClinicAccess($prescription);

        $clinicId = (int) Auth::user()->clinic_id;

        $prescription->load([
            'clinic',
            'patient' => fn ($query) =>
                $query->where('clinic_id', $clinicId),
            'visit' => fn ($query) =>
                $query->where('clinic_id', $clinicId),
            'createdBy' => fn ($query) =>
                $query->where('clinic_id', $clinicId),
            'doctor' => fn ($query) =>
                $query->where('clinic_id', $clinicId)->with('role'),
            'items',
        ]);

        return view('prescriptions.show', compact('prescription'));
    }

    public function edit(Prescription $prescription)
    {
        $this->ensureClinicAccess($prescription);

        $prescription->load(['items', 'createdBy', 'doctor']);

        return view('prescriptions.create', [
            ...$this->formData($prescription),
            'prescription' => $prescription,
        ]);
    }

    public function update(Request $request, Prescription $prescription)
    {
        $this->ensureClinicAccess($prescription);

        $data = $this->validatePrescription($request, $prescription);
        $nuskhaName = $this->resolveNuskhaName($data);

        DB::transaction(function () use ($prescription, $data, $nuskhaName) {
            $locked = Prescription::query()
                ->where('clinic_id', Auth::user()->clinic_id)
                ->whereKey($prescription->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $locked->update([
                'patient_id' => $data['patient_id'],
                'visit_id' => $data['visit_id'] ?? null,
                'doctor_user_id' => $data['doctor_user_id'],
                'nuskha_name' => $nuskhaName,
                'prescribed_at' => $data['prescribed_at'],
                'general_instructions' => $data['general_instructions'] ?? null,
                'status' => $data['status'],
            ]);

            $locked->items()->delete();
            $this->saveItems($locked, $data['items']);
        });

        return redirect()
            ->route('prescriptions.show', $prescription)
            ->with('success', 'Prescription updated successfully.');
    }

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

    private function formData(?Prescription $prescription = null): array
    {
        $clinicId = (int) Auth::user()->clinic_id;

        $patients = Patient::query()
            ->where('clinic_id', $clinicId)
            ->orderBy('full_name')
            ->get(['patient_id', 'patient_code', 'full_name']);

        $visits = Visit::query()
            ->where('clinic_id', $clinicId)
            ->orderByDesc('visit_date')
            ->get([
                'visit_id',
                'patient_id',
                'visit_date',
                'visit_reason',
                'doctor_user_id',
            ]);

        $roleIds = Role::query()
            ->whereIn('name', ['Owner', 'Doctor'])
            ->pluck('role_id')
            ->all();

        $doctors = User::query()
            ->where('clinic_id', $clinicId)
            ->where(function ($query) use ($roleIds, $prescription) {
                $query->where(function ($eligible) use ($roleIds) {
                    $eligible
                        ->where('is_active', true)
                        ->whereIn('role_id', $roleIds);
                });

                if ($prescription?->doctor_user_id) {
                    $query->orWhere(
                        'user_id',
                        $prescription->doctor_user_id
                    );
                }
            })
            ->orderBy('name')
            ->get(['user_id', 'name', 'is_active']);

        $templates = NuskhaTemplate::query()
            ->where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->with('items')
            ->orderBy('name')
            ->get();

        $templateData = $templates->map(fn ($template) => [
            'id' => $template->nuskha_template_id,
            'instructions' => $template->instructions,
            'items' => $template->items->map(
                fn ($item) => $item->only(NuskhaItem::ITEM_FIELDS)
            )->values()->all(),
        ])->values()->all();

        return compact(
            'patients',
            'visits',
            'doctors',
            'templates',
            'templateData'
        );
    }

    private function resolveNuskhaName(array $data): ?string
    {
        if ($data['prescription_type'] === 'template') {
            return NuskhaTemplate::query()
                ->where('clinic_id', Auth::user()->clinic_id)
                ->whereKey($data['template_id'])
                ->value('name');
        }

        $name = trim((string) ($data['nuskha_name'] ?? ''));

        return $name !== '' ? $name : null;
    }

    private function validatePrescription(
        Request $request,
        ?Prescription $prescription = null
    ): array {
        $clinicId = (int) Auth::user()->clinic_id;

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

        $roleIds = Role::query()
            ->whereIn('name', ['Owner', 'Doctor'])
            ->pluck('role_id')
            ->all();

        $doctorRule = Rule::exists('users', 'user_id')
            ->where('clinic_id', $clinicId)
            ->where(function ($query) use ($roleIds, $prescription) {
                $query->where(function ($eligible) use ($roleIds) {
                    $eligible
                        ->where('is_active', true)
                        ->whereIn('role_id', $roleIds);
                });

                if ($prescription?->doctor_user_id) {
                    $query->orWhere(
                        'user_id',
                        $prescription->doctor_user_id
                    );
                }
            });

        $data = $request->validate([
            'doctor_user_id' => ['required', 'integer', $doctorRule],

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

            'status' => ['required', Rule::in(['issued', 'draft', 'cancelled'])],

            'prescription_type' => [
                'required',
                Rule::in(['custom', 'template']),
            ],

            'nuskha_name' => ['nullable', 'string', 'max:160'],

            'template_id' => [
                'exclude_unless:prescription_type,template',
                'required',
                'integer',
                Rule::exists('nuskha_templates', 'nuskha_template_id')
                    ->where('clinic_id', $clinicId)
                    ->where('is_active', true),
            ],

            'general_instructions' => ['nullable', 'string', 'max:10000'],

            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*' => ['required', 'array'],
            'items.*.item_name' => ['required', 'string', 'max:160'],

            'items.*.quantity' => [
                'nullable',
                'required_with:items.*.unit',
                'numeric',
                'min:0.001',
                'max:999999999.999',
                'decimal:0,3',
            ],

            'items.*.unit' => [
                'nullable',
                'required_with:items.*.quantity',
                Rule::in(array_keys(NuskhaItem::UNITS)),
            ],

            'items.*.dosage' => ['nullable', 'string', 'max:80'],
            'items.*.frequency' => ['nullable', 'string', 'max:80'],
            'items.*.duration' => ['nullable', 'string', 'max:80'],
            'items.*.timing' => ['nullable', 'string', 'max:80'],
            'items.*.instructions' => ['nullable', 'string', 'max:255'],
        ], [
            'doctor_user_id.required' =>
                'Please select the treating Doctor/Hakeem.',
            'doctor_user_id.exists' =>
                'Select an active Owner or Doctor from your clinic.',
            'visit_id.exists' =>
                'Select a visit belonging to the selected patient.',
            'template_id.required' => 'Please select a saved nuskha template.',
            'template_id.exists' => 'The selected template is unavailable.',
            'items.*.item_name.required' =>
                'Every prescription item must have a name.',
            'items.*.quantity.required_with' =>
                'Enter a quantity when a unit is selected.',
            'items.*.unit.required_with' =>
                'Select a unit when a quantity is entered.',
        ]);

        $data['patient_id'] = $patientData['patient_id'];
        $data['doctor_user_id'] = (int) $data['doctor_user_id'];

        $data['prescribed_at'] =
            str_replace('T', ' ', $data['prescribed_at']) . ':00';

        return $data;
    }

    private function saveItems(Prescription $prescription, array $items): void
    {
        foreach (array_values($items) as $index => $item) {
            $prescription->items()->create([
                ...Arr::only($item, NuskhaItem::ITEM_FIELDS),
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function ensureClinicAccess(Prescription $prescription): void
    {
        abort_unless(
            (int) $prescription->clinic_id === (int) Auth::user()->clinic_id,
            404
        );
    }
}