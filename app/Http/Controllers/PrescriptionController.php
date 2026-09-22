<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Prescription::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('prescription_id', 'like', "%{$search}%")
                    ->orWhere('patient_id', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $prescriptions = $query
            ->latest('prescription_date')
            ->paginate(10)
            ->withQueryString();

        return view('prescriptions.index', compact('prescriptions'));
    }

    public function create()
    {
        return view('prescriptions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'integer'],
            'visit_id' => ['nullable', 'integer'],
            'nuskha_template_id' => ['nullable', 'integer'],
            'prescription_date' => ['required', 'date'],
            'status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['clinic_id'] = Auth::user()->clinic_id;
        $validated['status'] = $validated['status'] ?? 'active';

        Prescription::create($validated);

        return redirect()
            ->route('prescriptions.index')
            ->with('success', 'Prescription created successfully.');
    }

    public function show(Prescription $prescription)
    {
        return view('prescriptions.show', compact('prescription'));
    }

    public function edit(Prescription $prescription)
    {
        return view('prescriptions.create', compact('prescription'));
    }

    public function update(Request $request, Prescription $prescription)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'integer'],
            'visit_id' => ['nullable', 'integer'],
            'nuskha_template_id' => ['nullable', 'integer'],
            'prescription_date' => ['required', 'date'],
            'status' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);

        $prescription->update($validated);

        return redirect()
            ->route('prescriptions.show', $prescription)
            ->with('success', 'Prescription updated successfully.');
    }

    public function destroy(Prescription $prescription)
    {
        $prescription->delete();

        return redirect()
            ->route('prescriptions.index')
            ->with('success', 'Prescription deleted successfully.');
    }
}