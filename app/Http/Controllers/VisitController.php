<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        $query = Visit::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('visit_id', 'like', "%{$search}%")
                    ->orWhere('patient_id', 'like', "%{$search}%")
                    ->orWhere('visit_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $visits = $query
            ->latest('visit_date')
            ->paginate(10)
            ->withQueryString();

        return view('visits.index', compact('visits'));
    }

    public function create()
    {
        return view('visits.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'integer'],
            'visit_date' => ['required', 'date'],
            'visit_type' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['clinic_id'] = Auth::user()->clinic_id;
        $validated['status'] = $validated['status'] ?? 'completed';

        Visit::create($validated);

        return redirect()
            ->route('visits.index')
            ->with('success', 'Visit created successfully.');
    }

    public function show(Visit $visit)
    {
        return view('visits.show', compact('visit'));
    }

    public function edit(Visit $visit)
    {
        return view('visits.create', compact('visit'));
    }

    public function update(Request $request, Visit $visit)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'integer'],
            'visit_date' => ['required', 'date'],
            'visit_type' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);

        $visit->update($validated);

        return redirect()
            ->route('visits.show', $visit)
            ->with('success', 'Visit updated successfully.');
    }

    public function destroy(Visit $visit)
    {
        $visit->delete();

        return redirect()
            ->route('visits.index')
            ->with('success', 'Visit deleted successfully.');
    }
}