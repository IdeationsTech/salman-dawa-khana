<?php

namespace App\Http\Controllers;

use App\Models\NuskhaItem;
use App\Models\NuskhaTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NuskhaTemplateController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | TEMPLATES — List and Filters
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

        $query = NuskhaTemplate::query()
            ->where('clinic_id', Auth::user()->clinic_id)
            ->withCount('items');

        $search = trim($filters['search'] ?? '');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('instructions', 'like', "%{$search}%")
                    ->orWhereHas('items', function ($items) use ($search) {
                        $items->where('item_name', 'like', "%{$search}%");
                    });
            });
        }

        $status = $filters['status'] ?? 'all';

        if ($status !== 'all') {
            $query->where('is_active', $status === 'active');
        }

        $templates = $query
            ->orderByDesc('nuskha_template_id')
            ->paginate(12)
            ->withQueryString();

        return view(
            'prescriptions.templates.index',
            compact('templates')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TEMPLATES — Create and Store
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('prescriptions.templates.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateTemplate($request);

        DB::transaction(function () use ($data) {
            $template = NuskhaTemplate::create([
                'clinic_id' => Auth::user()->clinic_id,
                'name' => $data['name'],
                'instructions' => $data['instructions'] ?? null,
                'is_active' => $data['is_active'],
            ]);

            $this->saveItems($template, $data['items']);
        });

        return redirect()
            ->route('prescriptions.templates.index')
            ->with('success', 'Nuskha template saved successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | TEMPLATES — View / Edit
    |--------------------------------------------------------------------------
    */

    public function show(NuskhaTemplate $template)
    {
        $this->ensureClinicAccess($template);

        return redirect()->route(
            'prescriptions.templates.edit',
            $template
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

    public function update(Request $request, NuskhaTemplate $template)
    {
        $this->ensureClinicAccess($template);

        $data = $this->validateTemplate($request);

        DB::transaction(function () use ($template, $data) {
            $lockedTemplate = NuskhaTemplate::query()
                ->where('clinic_id', Auth::user()->clinic_id)
                ->whereKey($template->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $lockedTemplate->update([
                'name' => $data['name'],
                'instructions' => $data['instructions'] ?? null,
                'is_active' => $data['is_active'],
            ]);

            $lockedTemplate->items()->delete();

            $this->saveItems($lockedTemplate, $data['items']);
        });

        return redirect()
            ->route('prescriptions.templates.index')
            ->with('success', 'Nuskha template updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | TEMPLATES — Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(NuskhaTemplate $template)
    {
        $this->ensureClinicAccess($template);

        DB::transaction(function () use ($template) {
            $template->items()->delete();
            $template->delete();
        });

        return redirect()
            ->route('prescriptions.templates.index')
            ->with('success', 'Nuskha template deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | TEMPLATES — Validation
    |--------------------------------------------------------------------------
    */

    private function validateTemplate(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'instructions' => ['nullable', 'string', 'max:10000'],
            'is_active' => ['required', 'boolean'],

            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*' => ['required', 'array'],

            'items.*.item_name' => ['required', 'string', 'max:160'],

            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0.001',
                'max:999999999.999',
                'decimal:0,3',
            ],

            'items.*.unit' => [
                'required',
                Rule::in(array_keys(NuskhaItem::UNITS)),
            ],

            'items.*.dosage' => ['nullable', 'string', 'max:80'],
            'items.*.frequency' => ['nullable', 'string', 'max:80'],
            'items.*.duration' => ['nullable', 'string', 'max:80'],
            'items.*.timing' => ['nullable', 'string', 'max:80'],
            'items.*.instructions' => ['nullable', 'string', 'max:255'],
        ], [
            'items.required' => 'Add at least one ingredient.',
            'items.*.item_name.required' =>
                'Enter a name for every ingredient.',
            'items.*.quantity.required' =>
                'Enter a quantity for every ingredient.',
            'items.*.quantity.min' =>
                'Ingredient quantity must be greater than zero.',
            'items.*.quantity.decimal' =>
                'Quantity can have up to three decimal places.',
            'items.*.unit.required' =>
                'Select a unit for every ingredient.',
            'items.*.unit.in' =>
                'Select a unit from the available list.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TEMPLATES — Save Ingredients and Clinic Access
    |--------------------------------------------------------------------------
    */

    private function saveItems(
        NuskhaTemplate $template,
        array $items
    ): void {
        foreach (array_values($items) as $index => $item) {
            $template->items()->create(
                array_merge(
                    Arr::only($item, NuskhaItem::ITEM_FIELDS),
                    ['sort_order' => $index + 1]
                )
            );
        }
    }

    private function ensureClinicAccess(
        NuskhaTemplate $template
    ): void {
        abort_unless(
            (int) $template->clinic_id ===
                (int) Auth::user()->clinic_id,
            404
        );
    }
}