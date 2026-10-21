<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\NuskhaOrder;
use App\Models\NuskhaTemplate;
use App\Models\Setting;
use App\Rules\InternationalPhone;
use App\Support\ClinicClock;
use App\Support\ClinicLocaleCatalog;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NuskhaOrderController extends Controller
{
    private const STATUSES = [
        'confirmed', 'preparing', 'ready', 'completed', 'cancelled',
    ];

    private const UNITS = ['pack', 'bottle', 'piece', 'g', 'kg', 'ml', 'l'];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'status' => ['nullable', Rule::in(['all', ...self::STATUSES])],
            'fulfillment' => ['nullable', 'in:all,pickup,delivery'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $query = NuskhaOrder::where('clinic_id', $this->clinicId())
            ->withSum('payments', 'amount');

        if (filled($filters['search'] ?? null)) {
            $search = trim($filters['search']);

            $query->where(fn ($query) => $query
                ->where('customer_name', 'like', "%{$search}%")
                ->orWhere('customer_phone', 'like', "%{$search}%")
                ->orWhere('order_no', 'like', "%{$search}%"));
        }

        if (($filters['status'] ?? 'all') !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (($filters['fulfillment'] ?? 'all') !== 'all') {
            $query->where('fulfillment_method', $filters['fulfillment']);
        }

        if (filled($filters['from'] ?? null)) {
            $query->where('ordered_at', '>=', ClinicClock::stored($filters['from'].' 00:00:00'));
        }

        if (filled($filters['to'] ?? null)) {
            $query->where('ordered_at', '<=', ClinicClock::stored($filters['to'].' 23:59:59'));
        }

        return view('orders.index', [
            'orders' => $query->orderByDesc('ordered_at')
                ->orderByDesc('nuskha_order_id')
                ->paginate(50)->withQueryString(),
            'clinic' => auth()->user()->clinic,
            'statuses' => self::STATUSES,
            'decimalPlaces' => $this->decimalPlaces(),
        ]);
    }

    public function create()
    {
        return view('orders.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validateOrder($request);

        $order = DB::transaction(function () use ($data) {
            $clinic = Clinic::whereKey($this->clinicId())
                ->lockForUpdate()->firstOrFail();

            abort_unless(
                ClinicLocaleCatalog::knownCurrency($clinic->currency),
                422,
                'Clinic currency is not configured.'
            );

            [$items, $subtotal, $discount, $fee, $total] =
                $this->prepareItems($data);

            $received = Money::scaled($data['payment_amount']);
            $this->checkPaymentAmount($received, $total);

            $order = NuskhaOrder::create([
                'clinic_id' => $this->clinicId(),
                'created_by_user_id' => auth()->id(),
                'order_no' => 'TMP-'.Str::ulid(),
                'ordered_at' => $this->dateValue($data['ordered_at']),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'order_source' => $data['order_source'],
                'fulfillment_method' => $data['fulfillment_method'],
                'delivery_address' => $data['delivery_address'] ?? null,
                'status' => 'confirmed',
                'currency' => strtoupper($clinic->currency),
                'subtotal' => Money::decimal($subtotal),
                'discount' => Money::decimal($discount),
                'delivery_fee' => Money::decimal($fee),
                'total_amount' => Money::decimal($total),
                'notes' => $data['notes'] ?? null,
            ]);

            $order->update([
                'order_no' => 'NO-'.str_pad(
                    (string) $order->nuskha_order_id,
                    5,
                    '0',
                    STR_PAD_LEFT
                ),
            ]);

            $order->items()->createMany($items);

            if ($received > 0) {
                $order->payments()->create([
                    'clinic_id' => $this->clinicId(),
                    'received_by_user_id' => auth()->id(),
                    'paid_at' => $order->ordered_at,
                    'amount' => Money::decimal($received),
                    'method' => $data['payment_method'],
                    'reference_no' => $data['reference_no'] ?? null,
                ]);
            }

            return $order;
        }, 3);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Nuskha order saved successfully.');
    }

    public function edit(NuskhaOrder $order)
    {
        $this->ensureClinic($order);

        if ($order->status === 'cancelled') {
            return redirect()->route('orders.show', $order)->withErrors([
                'order' => 'Restore this cancelled order before editing it.',
            ]);
        }

        $order->load('items');

        return view('orders.edit', [
            ...$this->formData($order),
            'order' => $order,
        ]);
    }

    public function update(Request $request, NuskhaOrder $order)
    {
        $this->ensureClinic($order);
        $data = $this->validateOrder($request, $order);

        DB::transaction(function () use ($order, $data) {
            $locked = NuskhaOrder::where('clinic_id', $this->clinicId())
                ->whereKey($order->nuskha_order_id)
                ->lockForUpdate()->firstOrFail();

            if ($locked->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'order' => 'Restore this cancelled order before editing it.',
                ]);
            }

            $existing = $locked->items()->lockForUpdate()->get()
                ->keyBy('nuskha_order_item_id');

            [$items, $subtotal, $discount, $fee, $total] =
                $this->prepareItems($data, $existing);

            $netPaid = $this->netPaid($locked);

            if ($total < $netPaid) {
                throw ValidationException::withMessages([
                    'items' => 'Refund the difference first. The revised total cannot be less than the net amount received.',
                ]);
            }

            $keepIds = [];

            foreach ($items as $values) {
                $id = $values['item_id'] ?? null;
                unset($values['item_id']);

                if ($id) {
                    $item = $existing->get((int) $id);

                    if (! $item) {
                        throw ValidationException::withMessages([
                            'items' => 'The order changed. Reload the edit page.',
                        ]);
                    }

                    $item->update($values);
                    $keepIds[] = (int) $item->nuskha_order_item_id;
                } else {
                    $item = $locked->items()->create($values);
                    $keepIds[] = (int) $item->nuskha_order_item_id;
                }
            }

            foreach ($existing as $item) {
                if (! in_array((int) $item->nuskha_order_item_id, $keepIds, true)) {
                    $item->delete();
                }
            }

            $locked->update([
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'ordered_at' => $this->dateValue($data['ordered_at']),
                'order_source' => $data['order_source'],
                'fulfillment_method' => $data['fulfillment_method'],
                'delivery_address' => $data['delivery_address'] ?? null,
                'subtotal' => Money::decimal($subtotal),
                'discount' => Money::decimal($discount),
                'delivery_fee' => Money::decimal($fee),
                'total_amount' => Money::decimal($total),
                'notes' => $data['notes'] ?? null,
            ]);
        }, 3);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order and nuskha items updated successfully.');
    }

    public function show(NuskhaOrder $order)
    {
        $this->ensureClinic($order);

        $order->load([
            'items',
            'payments' => fn ($query) => $query
                ->where('clinic_id', $this->clinicId())
                ->orderBy('paid_at')->orderBy('nuskha_order_payment_id'),
        ]);

        $paid = $this->netPaid($order);

        $gross = $order->payments()
            ->where('clinic_id', $this->clinicId())
            ->where('amount', '>', 0)->sum('amount');

        $refunds = DB::table('nuskha_order_refunds')
            ->where('clinic_id', $this->clinicId())
            ->where('nuskha_order_id', $order->nuskha_order_id)
            ->orderByDesc('refunded_at')->get();

        $refunded = 0;

        foreach ($refunds as $refund) {
            $refunded += Money::scaled($refund->amount);
        }

        return view('orders.show', [
            'order' => $order,
            'clinic' => auth()->user()->clinic,
            'grossAmount' => $gross,
            'refundedAmount' => Money::decimal($refunded),
            'paidAmount' => Money::decimal($paid),
            'dueAmount' => $order->status === 'cancelled'
                ? '0.00'
                : Money::decimal(max(0, Money::scaled($order->total_amount) - $paid)),
            'refunds' => $refunds,
            'statuses' => self::STATUSES,
            'enabledMethods' => $this->enabledMethods(),
            'decimalPlaces' => $this->decimalPlaces(),
        ]);
    }

    public function receivePayment(Request $request, NuskhaOrder $order)
    {
        $this->ensureClinic($order);

        $data = $request->validate([
            'amount' => [...$this->moneyRules(), 'min:0.01'],
            'paid_at' => $this->dateRules(),
            'method' => ['required', Rule::in($this->enabledMethods())],
            'reference_no' => [
                Rule::requiredIf(in_array($request->input('method'), ['bank', 'card'], true)),
                'nullable', 'string', 'max:100',
            ],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($order, $data) {
            $locked = NuskhaOrder::where('clinic_id', $this->clinicId())
                ->whereKey($order->nuskha_order_id)
                ->lockForUpdate()->firstOrFail();

            if ($locked->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'amount' => 'Restore this cancelled order before receiving payment.',
                ]);
            }

            $remaining = max(
                0,
                Money::scaled($locked->total_amount) - $this->netPaid($locked)
            );

            $received = Money::scaled($data['amount']);
            $this->checkPaymentAmount($received, $remaining);

            $locked->payments()->create([
                'clinic_id' => $this->clinicId(),
                'received_by_user_id' => auth()->id(),
                'paid_at' => $this->dateValue($data['paid_at']),
                'amount' => Money::decimal($received),
                'method' => $data['method'],
                'reference_no' => $data['reference_no'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        }, 3);

        return back()->with('success', 'Order payment recorded.');
    }

    public function updateStatus(Request $request, NuskhaOrder $order)
    {
        $this->ensureClinic($order);

        $data = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        $message = DB::transaction(function () use ($order, $data) {
            $locked = NuskhaOrder::where('clinic_id', $this->clinicId())
                ->whereKey($order->nuskha_order_id)
                ->lockForUpdate()->firstOrFail();

            if ($locked->status === $data['status']) {
                return 'Order status is already selected.';
            }

            if ($locked->status === 'cancelled') {
                if ($data['status'] !== 'confirmed') {
                    throw ValidationException::withMessages([
                        'status' => 'Restore the cancelled order to Confirmed first.',
                    ]);
                }

                if ($this->netPaid($locked) > 0) {
                    throw ValidationException::withMessages([
                        'status' => 'This cancelled order still has unrefunded payments.',
                    ]);
                }

                $locked->update(['status' => 'confirmed']);

                return 'Order restored successfully.';
            }

            if ($data['status'] === 'cancelled' && $this->netPaid($locked) > 0) {
                throw ValidationException::withMessages([
                    'status' => 'Refund the remaining received amount before cancelling.',
                ]);
            }

            $wasCompleted = $locked->status === 'completed';
            $locked->update(['status' => $data['status']]);

            return $wasCompleted && $data['status'] !== 'cancelled'
                ? 'Order reopened successfully.'
                : 'Order status updated successfully.';
        }, 3);

        return back()->with('success', $message);
    }

    private function validateOrder(Request $request, ?NuskhaOrder $order = null): array
    {
        $rules = [
            'customer_name' => ['required', 'string', 'max:160'],
            'customer_phone' => [
                'bail', 'required', 'string', 'max:16', new InternationalPhone,
            ],
            'ordered_at' => $this->dateRules(),
            'order_source' => ['required', 'in:walk_in,phone'],
            'fulfillment_method' => ['required', 'in:pickup,delivery'],
            'delivery_address' => [
                'exclude_unless:fulfillment_method,delivery',
                'required', 'string', 'max:500',
            ],
            'discount' => $this->moneyRules(),
            'delivery_fee' => $this->moneyRules(),
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.item_id' => $order
                ? [
                    'nullable', 'integer', 'min:1', 'distinct',
                    Rule::exists('nuskha_order_items', 'nuskha_order_item_id')
                        ->where('nuskha_order_id', $order->nuskha_order_id),
                ]
                : ['prohibited'],
            'items.*.nuskha_template_id' => [
                'nullable', 'integer',
                Rule::exists('nuskha_templates', 'nuskha_template_id')
                    ->where('clinic_id', $this->clinicId()),
            ],
            'items.*.nuskha_name' => ['nullable', 'string', 'max:160'],
            'items.*.quantity' => [
                'required', 'numeric', 'min:0.001', 'max:1000',
                'regex:/^\d{1,4}(?:\.\d{1,3})?$/',
            ],
            'items.*.unit' => ['required', Rule::in(self::UNITS)],
            'items.*.unit_price' => $this->moneyRules(),
            'items.*.instructions' => ['nullable', 'string', 'max:500'],
        ];

        if (! $order) {
            $rules['payment_amount'] = $this->moneyRules();

            $rules['payment_method'] = [
                Rule::requiredIf((float) $request->input('payment_amount', 0) > 0),
                'nullable', Rule::in($this->enabledMethods()),
            ];

            $rules['reference_no'] = [
                Rule::requiredIf(
                    (float) $request->input('payment_amount', 0) > 0
                    && in_array($request->input('payment_method'), ['bank', 'card'], true)
                ),
                'nullable', 'string', 'max:100',
            ];
        }

        return $request->validate($rules);
    }

    private function prepareItems(array $data, $existing = null): array
    {
        $existing ??= collect();
        $items = [];
        $subtotal = 0;

        foreach ($data['items'] as $index => $input) {
            $id = ! empty($input['item_id']) ? (int) $input['item_id'] : null;
            $old = $id ? $existing->get($id) : null;

            if ($id && ! $old) {
                throw ValidationException::withMessages([
                    'items' => 'The order items changed. Reload the edit page.',
                ]);
            }

            $templateId = ! empty($input['nuskha_template_id'])
                ? (int) $input['nuskha_template_id']
                : null;

            $sameTemplate = $old && $templateId !== null
                && (int) $old->nuskha_template_id === $templateId;

            if ($sameTemplate) {
                $name = $old->nuskha_name;
                $snapshot = $old->recipe_snapshot;
            } elseif ($templateId !== null) {
                $template = NuskhaTemplate::where('clinic_id', $this->clinicId())
                    ->where('is_active', true)
                    ->whereKey($templateId)->with('items')->first();

                if (! $template) {
                    throw ValidationException::withMessages([
                        "items.{$index}.nuskha_template_id" => 'Select an active template belonging to your clinic.',
                    ]);
                }

                $name = $template->name;
                $snapshot = [
                    'template_name' => $template->name,
                    'instructions' => $template->instructions,
                    'items' => $template->items->map(fn ($ingredient) => $ingredient->only([
                        'item_name', 'quantity', 'unit', 'dosage',
                        'frequency', 'duration', 'timing',
                        'instructions', 'sort_order',
                    ]))->values()->all(),
                ];
            } else {
                $name = trim($input['nuskha_name'] ?? '');
                $snapshot = null;
            }

            if ($name === '') {
                throw ValidationException::withMessages([
                    "items.{$index}.nuskha_name" => 'Select a template or enter a nuskha name.',
                ]);
            }

            $quantity = Money::scaled($input['quantity'], 3);
            $price = Money::scaled($input['unit_price']);
            $lineTotal = intdiv(($quantity * $price) + 500, 1000);
            $subtotal += $lineTotal;

            if ($subtotal > 999999999999) {
                throw ValidationException::withMessages([
                    'items' => 'The order amount exceeds the supported limit.',
                ]);
            }

            $items[] = [
                ...($id ? ['item_id' => $id] : []),
                'nuskha_template_id' => $templateId,
                'nuskha_name' => $name,
                'recipe_snapshot' => $snapshot,
                'quantity' => Money::decimal($quantity, 3),
                'unit' => $input['unit'],
                'unit_price' => Money::decimal($price),
                'line_total' => Money::decimal($lineTotal),
                'instructions' => $input['instructions'] ?? null,
                'sort_order' => $index + 1,
            ];
        }

        $discount = Money::scaled($data['discount']);

        if ($discount > $subtotal) {
            throw ValidationException::withMessages([
                'discount' => 'Discount cannot exceed the item subtotal.',
            ]);
        }

        $fee = $data['fulfillment_method'] === 'delivery'
            ? Money::scaled($data['delivery_fee'])
            : 0;

        $total = $subtotal - $discount + $fee;

        if ($total <= 0 || $total > 999999999999) {
            throw ValidationException::withMessages([
                'items' => 'Order total must be positive and within the supported limit.',
            ]);
        }

        return [$items, $subtotal, $discount, $fee, $total];
    }

    private function formData(?NuskhaOrder $order = null): array
    {
        $originalTemplateIds = $order
            ? $order->items->pluck('nuskha_template_id')->filter()->all()
            : [];

        $templates = NuskhaTemplate::where('clinic_id', $this->clinicId())
            ->where(fn ($query) => $query->where('is_active', true)
                ->orWhereIn('nuskha_template_id', $originalTemplateIds))
            ->orderBy('name')->get();

        return [
            'clinic' => auth()->user()->clinic,
            'templates' => $templates,
            'units' => self::UNITS,
            'enabledMethods' => $this->enabledMethods(),
            'paidAmount' => $order ? Money::decimal($this->netPaid($order)) : '0.00',
        ];
    }

    private function netPaid(NuskhaOrder $order): int
    {
        return max(0, Money::scaled(
            $order->payments()->where('clinic_id', $this->clinicId())->sum('amount')
        ));
    }

    private function checkPaymentAmount(int $received, int $remaining): void
    {
        if ($received > $remaining) {
            throw ValidationException::withMessages([
                'amount' => 'Payment exceeds the remaining balance.',
                'payment_amount' => 'Payment exceeds the order total.',
            ]);
        }

        $partial = Setting::where('clinic_id', $this->clinicId())
            ->where('setting_key', 'allow_partial_payments')
            ->value('setting_value');

        if ((string) $partial === '0' && $received > 0 && $received < $remaining) {
            throw ValidationException::withMessages([
                'amount' => 'Partial payments are disabled. Pay the full remaining balance.',
                'payment_amount' => 'Partial payments are disabled. Pay the full order total.',
            ]);
        }
    }

    private function enabledMethods(): array
    {
        $settings = Setting::where('clinic_id', $this->clinicId())
            ->whereIn('setting_key', ['cash_enabled', 'bank_enabled', 'card_enabled'])
            ->pluck('setting_value', 'setting_key');

        return array_keys(array_filter([
            'cash' => (string) $settings->get('cash_enabled', '1') === '1',
            'bank' => (string) $settings->get('bank_enabled', '1') === '1',
            'card' => (string) $settings->get('card_enabled', '1') === '1',
        ]));
    }

    private function decimalPlaces(): int
    {
        return (string) Setting::where('clinic_id', $this->clinicId())
            ->where('setting_key', 'decimal_places')
            ->value('setting_value') === '0' ? 0 : 2;
    }

    private function moneyRules(): array
    {
        return [
            'required', 'numeric', 'min:0', 'max:9999999999.99',
            'regex:/^\d{1,10}(?:\.\d{1,2})?$/',
        ];
    }

    private function dateRules(): array
    {
        return [
            'bail', 'required', 'date_format:Y-m-d\TH:i',
            'after_or_equal:1000-01-01',
            'before_or_equal:9999-12-31 23:59:59',
        ];
    }

    private function dateValue(string $value): string
    {
        return str_replace('T', ' ', $value).':00';
    }

    private function clinicId(): int
    {
        return (int) auth()->user()->clinic_id;
    }

    private function ensureClinic(NuskhaOrder $order): void
    {
        abort_unless((int) $order->clinic_id === $this->clinicId(), 404);
    }
}
