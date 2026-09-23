<?php

namespace App\Http\Controllers;

use App\Models\NuskhaTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NuskhaTemplateController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = Auth::user()->clinic_id;

        $query = NuskhaTemplate::where(
            'clinic_id',
            $clinicId
        )->withCount('items');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere(
                        'instructions',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        if (
            $request->filled('status') &&
            $request->status !== 'all'
        ) {
            $query->where(
                'is_active',
                $request->status === 'active'
            );
        }

        $templates = $query
            ->latest('nuskha_template_id')
            ->paginate(12)
            ->withQueryString();

        return view(
            'prescriptions.templates.index',
            compact('templates')
        );
    }

    public function create()
    {
        return view('prescriptions.templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:160',
            ],

            'instructions' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'nullable',
                'boolean',
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

        $template = DB::transaction(function () use ($validated) {
            $template = NuskhaTemplate::create([
                'clinic_id' => Auth::user()->clinic_id,
                'name' => $validated['name'],
                'instructions' => $validated['instructions'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            foreach ($validated['items'] as $index => $item) {
                $template->items()->create([
                    'item_name' => $item['item_name'],
                    'dosage' => $item['dosage'] ?? null,
                    'frequency' => $item['frequency'] ?? null,
                    'duration' => $item['duration'] ?? null,
                    'timing' => $item['timing'] ?? null,
                    'instructions' => $item['instructions'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            }

            return $template;
        });

        return redirect()
            ->route(
                'prescriptions.templates.show',
                $template
            )
            ->with(
                'success',
                'Nuskha template created successfully.'
            );
    }

    public function show(NuskhaTemplate $template)
    {
        $this->ensureClinicAccess($template);

        $template->load('items');

        return view(
            'prescriptions.templates.show',
            compact('template')
        );
    }

    public function edit(NuskhaTemplate $template)
    {
        $this->ensureClinicAccess($template);

        $template->load('items');

        return view(
            'prescriptions.templates.create',
            compact('template')
        );
    }

    public function update(
        Request $request,
        NuskhaTemplate $template
    ) {
        $this->ensureClinicAccess($template);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:160',
            ],

            'instructions' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'nullable',
                'boolean',
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
            $template
        ) {
            $template->update([
                'name' => $validated['name'],
                'instructions' => $validated['instructions'] ?? null,
                'is_active' => $validated['is_active'] ?? false,
            ]);

            $template->items()->delete();

            foreach ($validated['items'] as $index => $item) {
                $template->items()->create([
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
                'prescriptions.templates.show',
                $template
            )
            ->with(
                'success',
                'Nuskha template updated successfully.'
            );
    }

    public function destroy(NuskhaTemplate $template)
    {
        $this->ensureClinicAccess($template);

        $template->delete();

        return redirect()
            ->route('prescriptions.templates.index')
            ->with(
                'success',
                'Nuskha template deleted successfully.'
            );
    }

    private function ensureClinicAccess(
        NuskhaTemplate $template
    ) {
        abort_if(
            $template->clinic_id !== Auth::user()->clinic_id,
            403
        );
    }
}