<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = Auth::user()->clinic_id;

        $query = Prescription::where(
            'clinic_id',
            $clinicId
        )->withCount('items');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where(
                    'prescription_no',
                    'like',
                    "%{$search}%"
                )
                    ->orWhere(
                        'patient_id',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        if (
            $request->filled('status') &&
            $request->status !== 'all'
        ) {
            $query->where('status', $request->status);
        }

        $dateRange = strtolower(
            $request->get('date_range', 'all dates')
        );

        if ($dateRange === 'today') {
            $query->whereDate(
                'prescribed_at',
                Carbon::today()
            );
        }

        if ($dateRange === 'this week') {
            $query->whereBetween('prescribed_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ]);
        }

        if ($dateRange === 'this month') {
            $query->whereBetween('prescribed_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ]);
        }

        $prescriptions = $query
            ->latest('prescribed_at')
            ->paginate(10)
            ->withQueryString();

        $totalPrescriptions = Prescription::where(
            'clinic_id',
            $clinicId
        )->count();

        $monthlyPrescriptions = Prescription::where(
            'clinic_id',
            $clinicId
        )
            ->whereBetween('prescribed_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->count();

        $todayPrescriptions = Prescription::where(
            'clinic_id',
            $clinicId
        )
            ->whereDate(
                'prescribed_at',
                Carbon::today()
            )
            ->count();

        return view('prescriptions.index', compact(
            'prescriptions',
            'totalPrescriptions',
            'monthlyPrescriptions',
            'todayPrescriptions'
        ));
    }

    public function create()
    {
        return view('prescriptions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => [
                'required',
                'integer',
                'exists:patients,patient_id',
            ],

            'visit_id' => [
                'nullable',
                'integer',
                'exists:visits,visit_id',
            ],

            'prescribed_at' => [
                'required',
                'date',
            ],

            'general_instructions' => [
                'nullable',
                'string',
            ],

            'status' => [
                'nullable',
                'string',
                'max:20',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.item_name' => [
                'required',
                'string',
                'max:160',
            ],

            'items.*.dosage' => [
                'nullable',
                'string',
                'max:80',
            ],

            'items.*.frequency' => [
                'nullable',
                'string',
                'max:80',
            ],

            'items.*.duration' => [
                'nullable',
                'string',
                'max:80',
            ],

            'items.*.timing' => [
                'nullable',
                'string',
                'max:80',
            ],

            'items.*.instructions' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $clinicId = Auth::user()->clinic_id;

        $prescription = DB::transaction(function () use (
            $validated,
            $clinicId
        ) {
            $nextNumber = (
                Prescription::where('clinic_id', $clinicId)
                    ->lockForUpdate()
                    ->max('prescription_id') ?? 0
            ) + 1;

            $prescription = Prescription::create([
                'clinic_id' => $clinicId,
                'patient_id' => $validated['patient_id'],
                'visit_id' => $validated['visit_id'] ?? null,
                'created_by_user_id' => Auth::id(),
                'prescription_no' => 'RX-' . str_pad(
                    $nextNumber,
                    5,
                    '0',
                    STR_PAD_LEFT
                ),
                'prescribed_at' => $validated['prescribed_at'],
                'general_instructions' =>
                    $validated['general_instructions'] ?? null,
                'status' => $validated['status'] ?? 'issued',
            ]);

            foreach ($validated['items'] as $index => $item) {
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

            return $prescription;
        });

        return redirect()
            ->route(
                'prescriptions.show',
                $prescription
            )
            ->with(
                'success',
                'Prescription created successfully.'
            );
    }

    public function show(Prescription $prescription)
    {
        $this->ensureClinicAccess($prescription);

        $prescription->load([
            'patient',
            'visit',
            'createdBy',
            'items',
        ]);

        return view(
            'prescriptions.show',
            compact('prescription')
        );
    }

    public function edit(Prescription $prescription)
    {
        $this->ensureClinicAccess($prescription);

        $prescription->load('items');

        return view(
            'prescriptions.create',
            compact('prescription')
        );
    }

    public function update(
        Request $request,
        Prescription $prescription
    ) {
        $this->ensureClinicAccess($prescription);

        $validated = $request->validate([
            'patient_id' => [
                'required',
                'integer',
                'exists:patients,patient_id',
            ],

            'visit_id' => [
                'nullable',
                'integer',
                'exists:visits,visit_id',
            ],

            'prescribed_at' => [
                'required',
                'date',
            ],

            'general_instructions' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                'string',
                'max:20',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.item_name' => [
                'required',
                'string',
                'max:160',
            ],

            'items.*.dosage' => [
                'nullable',
                'string',
                'max:80',
            ],

            'items.*.frequency' => [
                'nullable',
                'string',
                'max:80',
            ],

            'items.*.duration' => [
                'nullable',
                'string',
                'max:80',
            ],

            'items.*.timing' => [
                'nullable',
                'string',
                'max:80',
            ],

            'items.*.instructions' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        DB::transaction(function () use (
            $validated,
            $prescription
        ) {
            $prescription->update([
                'patient_id' => $validated['patient_id'],
                'visit_id' => $validated['visit_id'] ?? null,
                'prescribed_at' => $validated['prescribed_at'],
                'general_instructions' =>
                    $validated['general_instructions'] ?? null,
                'status' => $validated['status'],
            ]);

            $prescription->items()->delete();

            foreach ($validated['items'] as $index => $item) {
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
        });

        return redirect()
            ->route(
                'prescriptions.show',
                $prescription
            )
            ->with(
                'success',
                'Prescription updated successfully.'
            );
    }

    public function destroy(Prescription $prescription)
    {
        $this->ensureClinicAccess($prescription);

        $prescription->delete();

        return redirect()
            ->route('prescriptions.index')
            ->with(
                'success',
                'Prescription deleted successfully.'
            );
    }

    private function ensureClinicAccess(
        Prescription $prescription
    ) {
        abort_if(
            $prescription->clinic_id !== Auth::user()->clinic_id,
            403
        );
    }
}