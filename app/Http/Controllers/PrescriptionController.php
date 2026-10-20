<?php

namespace App\Http\Controllers;

use App\Models\NuskhaTemplate;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionNuskha;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
                'patient' => fn ($query) => $query->where('clinic_id', $clinicId),
                'createdBy' => fn ($query) => $query->where('clinic_id', $clinicId),
                'doctor' => fn ($query) => $query->where('clinic_id', $clinicId),
            ])
            ->withCount('nuskhas');

        $search = trim($filters['search'] ?? '');

        if ($search !== '') {
            $query->where(function ($query) use ($search, $clinicId) {
                $query
                    ->where('prescription_no', 'like', "%{$search}%")
                    ->orWhere('nuskha_name', 'like', "%{$search}%")
                    ->orWhereHas('nuskhas', fn ($nuskhas) => $nuskhas->where('nuskha_name', 'like', "%{$search}%"))
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

        $prescription = DB::transaction(function () use ($data) {
            $rows = $this->resolveNuskhas($data['nuskhas']);

            $prescription = Prescription::create([
                'clinic_id' => Auth::user()->clinic_id,
                'patient_id' => $data['patient_id'],
                'visit_id' => $data['visit_id'] ?? null,
                'created_by_user_id' => Auth::id(),
                'doctor_user_id' => $data['doctor_user_id'],
                'prescription_no' => 'TMP-'.(string) Str::ulid(),
                'nuskha_name' => $this->summaryName($rows),
                'prescribed_at' => $data['prescribed_at'],
                'general_instructions' => $data['general_instructions'] ?? null,
                'status' => $data['status'],
            ]);

            $prescription->update([
                'prescription_no' => 'RX'.str_pad(
                    (string) $prescription->prescription_id, 2, '0', STR_PAD_LEFT
                ),
            ]);

            $this->saveNuskhas($prescription, $rows);

            return $prescription;
        }, 3);

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Prescription created successfully.');
    }

    public function show(Prescription $prescription)
    {
        $this->ensureClinicAccess($prescription);
        $clinicId = (int) Auth::user()->clinic_id;

        $prescription->load([
            'clinic',
            'patient' => fn ($query) => $query->where('clinic_id', $clinicId),
            'visit' => fn ($query) => $query->where('clinic_id', $clinicId),
            'createdBy' => fn ($query) => $query->where('clinic_id', $clinicId),
            'doctor' => fn ($query) => $query->where('clinic_id', $clinicId)->with('role'),
            'nuskhas',
        ]);

        return view('prescriptions.show', compact('prescription'));
    }

    public function edit(Prescription $prescription)
    {
        $this->ensureClinicAccess($prescription);
        $prescription->load(['nuskhas', 'createdBy', 'doctor']);

        return view('prescriptions.create', [
            ...$this->formData($prescription),
            'prescription' => $prescription,
        ]);
    }

    public function update(Request $request, Prescription $prescription)
    {
        $this->ensureClinicAccess($prescription);
        $data = $this->validatePrescription($request, $prescription);

        DB::transaction(function () use ($prescription, $data) {
            $locked = Prescription::query()
                ->where('clinic_id', Auth::user()->clinic_id)
                ->whereKey($prescription->getKey())
                ->lockForUpdate()->firstOrFail();

            $rows = $this->resolveNuskhas($data['nuskhas'], $locked);

            $locked->update([
                'patient_id' => $data['patient_id'],
                'visit_id' => $data['visit_id'] ?? null,
                'doctor_user_id' => $data['doctor_user_id'],
                'nuskha_name' => $this->summaryName($rows),
                'prescribed_at' => $data['prescribed_at'],
                'general_instructions' => $data['general_instructions'] ?? null,
                'status' => $data['status'],
            ]);

            $this->saveNuskhas($locked, $rows);
            // Historical prescription_items contain ingredients. Preserve them;
            // they are never used as patient nuskhas or shown in patient output.
        }, 3);

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Prescription updated successfully.');
    }

    public function destroy(Prescription $prescription)
    {
        $this->ensureClinicAccess($prescription);

        DB::transaction(function () use ($prescription) {
            $locked = Prescription::where('clinic_id', Auth::user()->clinic_id)
                ->whereKey($prescription->getKey())->lockForUpdate()->firstOrFail();

            foreach ($locked->nuskhas as $row) {
                $row->delete();
            }

            foreach ($locked->items as $item) {
                $item->delete();
            }

            $locked->delete();
        });

        return redirect()->route('prescriptions.index')
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

        $existingTemplateIds = $prescription
            ? $prescription->nuskhas->pluck('nuskha_template_id')->filter()->all()
            : [];

        // Do not fetch ingredient relationships or expose recipes in form JSON.
        $templates = NuskhaTemplate::query()
            ->where('clinic_id', $clinicId)
            ->where(function ($query) use ($existingTemplateIds) {
                $query->where('is_active', true);

                if ($existingTemplateIds) {
                    $query->orWhereIn('nuskha_template_id', $existingTemplateIds);
                }
            })
            ->orderBy('name')
            ->get(['nuskha_template_id', 'name', 'is_active']);

        $units = PrescriptionNuskha::UNITS;

        return compact('patients', 'visits', 'doctors', 'templates', 'units');
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
            'patient_id.in' => 'The patient cannot be changed on an existing prescription.',
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

            'general_instructions' => ['nullable', 'string', 'max:10000'],
            'nuskhas' => ['required', 'array', 'min:1', 'max:30'],
            'nuskhas.*' => [
                'required',
                'array:assignment_id,source,nuskha_template_id,nuskha_name,quantity,unit,frequency,duration,timing,instructions',
            ],
            'nuskhas.*.assignment_id' => ['nullable', 'integer', 'min:1', 'distinct'],
            'nuskhas.*.source' => ['required', Rule::in(['template', 'custom'])],
            'nuskhas.*.nuskha_template_id' => [
                'nullable', 'required_if:nuskhas.*.source,template', 'integer',
                Rule::exists('nuskha_templates', 'nuskha_template_id')
                    ->where('clinic_id', $clinicId),
            ],
            'nuskhas.*.nuskha_name' => [
                'nullable', 'required_if:nuskhas.*.source,custom', 'string', 'max:160',
            ],
            'nuskhas.*.quantity' => [
                'bail', 'required', 'numeric', 'min:0.001',
                'max:999999999.999', 'regex:/^\d{1,9}(?:\.\d{1,3})?$/',
            ],
            'nuskhas.*.unit' => ['required', Rule::in(array_keys(PrescriptionNuskha::UNITS))],
            'nuskhas.*.frequency' => ['nullable', 'string', 'max:80'],
            'nuskhas.*.duration' => ['nullable', 'string', 'max:80'],
            'nuskhas.*.timing' => ['nullable', 'string', 'max:80'],
            'nuskhas.*.instructions' => ['nullable', 'string', 'max:255'],
            // Reject stale ingredient-based forms instead of silently saving them.
            'items' => ['prohibited'],
            'template_id' => ['prohibited'],
            'prescription_type' => ['prohibited'],
        ], [
            'doctor_user_id.required' => 'Please select the treating Doctor/Hakeem.',
            'doctor_user_id.exists' => 'Select an active Owner or Doctor from your clinic.',
            'visit_id.exists' => 'Select a visit belonging to the selected patient.',
            'nuskhas.required' => 'Add at least one nuskha to the prescription.',
            'nuskhas.*.nuskha_template_id.required_if' => 'Select a saved nuskha.',
            'nuskhas.*.nuskha_template_id.exists' => 'Select a template from your clinic.',
            'nuskhas.*.nuskha_name.required_if' => 'Enter the custom nuskha name.',
            'nuskhas.*.quantity.required' => 'Enter the total quantity or weight for each nuskha.',
            'nuskhas.*.unit.required' => 'Select a unit for each nuskha.',
            'items.prohibited' => 'This form is outdated. Reload the prescription page.',
        ]);

        $data['patient_id'] = $patientData['patient_id'];
        $data['doctor_user_id'] = (int) $data['doctor_user_id'];

        $data['prescribed_at'] =
            str_replace('T', ' ', $data['prescribed_at']).':00';

        return $data;
    }

    private function resolveNuskhas(array $rows, ?Prescription $prescription = null): array
    {
        $existing = $prescription
            ? $prescription->nuskhas()->lockForUpdate()->get()->keyBy('prescription_nuskha_id')
            : collect();

        $templates = NuskhaTemplate::where('clinic_id', Auth::user()->clinic_id)
            ->whereIn('nuskha_template_id', collect($rows)->pluck('nuskha_template_id')->filter())
            ->lockForUpdate()->get(['nuskha_template_id', 'name', 'is_active'])
            ->keyBy('nuskha_template_id');

        $resolved = [];

        foreach (array_values($rows) as $index => $row) {
            $id = ! empty($row['assignment_id']) ? (int) $row['assignment_id'] : null;
            $original = $id ? $existing->get($id) : null;

            if ($id && ! $original) {
                throw ValidationException::withMessages([
                    "nuskhas.{$index}.assignment_id" => 'This nuskha does not belong to this prescription. Reload the page.',
                ]);
            }

            $templateId = null;
            $name = trim((string) ($row['nuskha_name'] ?? ''));

            if ($row['source'] === 'template') {
                $templateId = (int) $row['nuskha_template_id'];
                $template = $templates->get($templateId);
                $unchanged = $original
                    && (int) $original->nuskha_template_id === $templateId;

                if (! $template || (! $template->is_active && ! $unchanged)) {
                    throw ValidationException::withMessages([
                        "nuskhas.{$index}.nuskha_template_id" => 'Select an active nuskha template from your clinic.',
                    ]);
                }

                // Preserve the name actually prescribed, even if the library is renamed.
                $name = $unchanged ? $original->nuskha_name : $template->name;
            }

            if (trim($name) === '') {
                throw ValidationException::withMessages([
                    "nuskhas.{$index}.nuskha_name" => 'Enter a nuskha name.',
                ]);
            }

            $resolved[] = [
                ...Arr::only($row, ['quantity', 'unit', 'frequency', 'duration', 'timing', 'instructions']),
                'assignment_id' => $id,
                'nuskha_template_id' => $templateId,
                'nuskha_name' => $name,
                'sort_order' => $index + 1,
            ];
        }

        return $resolved;
    }

    private function summaryName(array $rows): string
    {
        // Compatibility summary for existing patient history widgets.
        // Complete names always remain in prescription_nuskhas.
        return Str::limit(implode(' / ', array_column($rows, 'nuskha_name')), 160, '');
    }

    private function saveNuskhas(Prescription $prescription, array $rows): void
    {
        $existing = $prescription->nuskhas()->get()->keyBy('prescription_nuskha_id');
        $kept = [];

        foreach ($rows as $row) {
            $id = $row['assignment_id'];
            unset($row['assignment_id']);

            if ($id !== null) {
                $assignment = $existing->get($id);
                $assignment->update($row);
            } else {
                $assignment = $prescription->nuskhas()->create($row);
            }

            $kept[] = $assignment->getKey();
        }

        foreach ($existing as $assignment) {
            if (! in_array($assignment->getKey(), $kept, true)) {
                $assignment->delete();
            }
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
