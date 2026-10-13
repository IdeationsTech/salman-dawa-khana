<?php

namespace App\Http\Controllers;

use App\Models\NuskhaOrder;
use App\Models\NuskhaOrderPayment;
use App\Models\NuskhaTemplate;
use App\Models\Setting;
use App\Rules\InternationalPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NuskhaOrderController extends Controller
{
    private const STATUSES = [
        'confirmed',
        'preparing',
        'ready',
        'completed',
        'cancelled',
    ];

    private const UNITS = [
        'pack',
        'bottle',
        'piece',
        'g',
        'kg',
        'ml',
        'l',
    ];

    /*
    |--------------------------------------------------------------------------
    | Orders — List and Filters
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'status' => ['nullable', Rule::in(['all', ...self::STATUSES])],
            'fulfillment' => ['nullable', 'in:all,pickup,delivery'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $query = NuskhaOrder::query()
            ->where('clinic_id', $this->clinicId())
            ->withSum('payments', 'amount');

        if (filled($filters['search'] ?? null)) {
            $search = trim($filters['search']);

            $query->where(function ($query) use ($search) {
                $query->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('order_no', 'like', "%{$search}%");
            });
        }

        if (
            filled($filters['status'] ?? null) &&
            $filters['status'] !== 'all'
        ) {
            $query->where('status', $filters['status']);
        }

        if (
            filled($filters['fulfillment'] ?? null) &&
            $filters['fulfillment'] !== 'all'
        ) {
            $query->where(
                'fulfillment_method',
                $filters['fulfillment']
            );
        }

        if (filled($filters['from'] ?? null)) {
            $query->where(
                'ordered_at',
                '>=',
                $filters['from'] . ' 00:00:00'
            );
        }

        if (filled($filters['to'] ?? null)) {
            $query->where(
                'ordered_at',
                '<=',
                $filters['to'] . ' 23:59:59'
            );
        }

        $orders = $query
            ->orderByDesc('ordered_at')
            ->orderByDesc('nuskha_order_id')
            ->paginate(50)
            ->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'clinic' => auth()->user()->clinic,
            'statuses' => self::STATUSES,
            'decimalPlaces' => $this->decimalPlaces(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Orders — Create Form
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('orders.create', [
            'clinic' => auth()->user()->clinic,

            'templates' => NuskhaTemplate::query()
                ->where('clinic_id', $this->clinicId())
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),

            'units' => self::UNITS,
            'enabledMethods' => $this->enabledMethods(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Orders — Save Customer, Items and Initial Payment
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:160'],

            'customer_phone' => [
                'bail',
                'required',
                'string',
                'max:16',
                new InternationalPhone(),
            ],

            'ordered_at' => [
                'bail',
                'required',
                'date_format:Y-m-d\TH:i',
                'after_or_equal:1000-01-01',
                'before_or_equal:9999-12-31 23:59:59',
            ],

            'order_source' => ['required', 'in:walk_in,phone'],
            'fulfillment_method' => ['required', 'in:pickup,delivery'],

            'delivery_address' => [
                'exclude_unless:fulfillment_method,delivery',
                'required',
                'string',
                'max:500',
            ],

            'discount' => $this->moneyRules(),
            'delivery_fee' => $this->moneyRules(),
            'notes' => ['nullable', 'string', 'max:5000'],

            'items' => ['required', 'array', 'min:1', 'max:50'],

            'items.*.nuskha_template_id' => [
                'nullable',
                'integer',
                Rule::exists('nuskha_templates', 'nuskha_template_id')
                    ->where('clinic_id', $this->clinicId())
                    ->where('is_active', true),
            ],

            'items.*.nuskha_name' => ['nullable', 'string', 'max:160'],

            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0.001',
                'max:1000',
                'regex:/^\d{1,4}(?:\.\d{1,3})?$/',
            ],

            'items.*.unit' => ['required', Rule::in(self::UNITS)],
            'items.*.unit_price' => $this->moneyRules(),
            'items.*.instructions' => ['nullable', 'string', 'max:500'],

            'payment_amount' => $this->moneyRules(),

            'payment_method' => [
                Rule::requiredIf(
                    (float) $request->input('payment_amount', 0) > 0
                ),
                'nullable',
                Rule::in($this->enabledMethods()),
            ],

            'reference_no' => [
                Rule::requiredIf(
                    (float) $request->input('payment_amount', 0) > 0 &&
                    in_array(
                        $request->input('payment_method'),
                        ['bank', 'card'],
                        true
                    )
                ),
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $order = DB::transaction(function () use ($data) {
            $clinic = DB::table('clinics')
                ->where('clinic_id', $this->clinicId())
                ->lockForUpdate()
                ->first();

            abort_unless($clinic, 404);

            $items = [];
            $subtotal = 0;

            foreach ($data['items'] as $index => $item) {
                $template = null;

                if (! empty($item['nuskha_template_id'])) {
                    $template = NuskhaTemplate::query()
                        ->where('clinic_id', $this->clinicId())
                        ->where('is_active', true)
                        ->whereKey($item['nuskha_template_id'])
                        ->with('items')
                        ->first();

                    if (! $template) {
                        throw ValidationException::withMessages([
                            "items.{$index}.nuskha_template_id" =>
                                'This template is no longer available.',
                        ]);
                    }
                }

                $name = $template
                    ? $template->name
                    : trim($item['nuskha_name'] ?? '');

                if ($name === '') {
                    throw ValidationException::withMessages([
                        "items.{$index}.nuskha_name" =>
                            'Select a template or enter the nuskha name.',
                    ]);
                }

                $quantity = $this->scaled($item['quantity'], 3);
                $unitPrice = $this->scaled($item['unit_price'], 2);

                // Round the line total to the nearest cent.
                $lineTotal = intdiv(
                    ($quantity * $unitPrice) + 500,
                    1000
                );

                $subtotal += $lineTotal;

                if ($subtotal > 999999999999) {
                    throw ValidationException::withMessages([
                        'items' => 'The order amount exceeds the supported limit.',
                    ]);
                }

                $items[] = [
                    'nuskha_template_id' => $template?->nuskha_template_id,
                    'nuskha_name' => $name,
                    'instructions' => $item['instructions'] ?? null,

                    'recipe_snapshot' => $template
                        ? [
                            'template_name' => $template->name,
                            'instructions' => $template->instructions,
                            'items' => $template->items->map(
                                fn ($ingredient) => $ingredient->only([
                                    'item_name',
                                    'quantity',
                                    'unit',
                                    'dosage',
                                    'frequency',
                                    'duration',
                                    'timing',
                                    'instructions',
                                    'sort_order',
                                ])
                            )->values()->all(),
                        ]
                        : null,

                    'quantity' => $this->decimal($quantity, 3),
                    'unit' => $item['unit'],
                    'unit_price' => $this->decimal($unitPrice),
                    'line_total' => $this->decimal($lineTotal),
                    'sort_order' => $index + 1,
                ];
            }

            $discount = $this->scaled($data['discount'], 2);

            if ($discount > $subtotal) {
                throw ValidationException::withMessages([
                    'discount' => 'Discount cannot exceed the item subtotal.',
                ]);
            }

            $deliveryFee = $data['fulfillment_method'] === 'delivery'
                ? $this->scaled($data['delivery_fee'], 2)
                : 0;

            $total = $subtotal - $discount + $deliveryFee;

            if ($total <= 0 || $total > 999999999999) {
                throw ValidationException::withMessages([
                    'discount' => 'The order total must be positive and within the supported limit.',
                ]);
            }

            $received = $this->scaled($data['payment_amount'], 2);

            $this->checkPaymentAmount($received, $total);

            $order = NuskhaOrder::create([
                'clinic_id' => $this->clinicId(),
                'created_by_user_id' => auth()->id(),
                'order_no' => 'TMP-' . (string) \Illuminate\Support\Str::ulid(),
                'ordered_at' => str_replace('T', ' ', $data['ordered_at']) . ':00',
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'order_source' => $data['order_source'],
                'fulfillment_method' => $data['fulfillment_method'],
                'delivery_address' => $data['delivery_address'] ?? null,
                'status' => 'confirmed',
                'currency' => strtoupper($clinic->currency ?: 'AED'),
                'subtotal' => $this->decimal($subtotal),
                'discount' => $this->decimal($discount),
                'delivery_fee' => $this->decimal($deliveryFee),
                'total_amount' => $this->decimal($total),
                'notes' => $data['notes'] ?? null,
            ]);

            $order->update([
                'order_no' => 'NO-' . str_pad(
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
                    'amount' => $this->decimal($received),
                    'method' => $data['payment_method'],
                    'reference_no' => $data['reference_no'] ?? null,
                ]);
            }

            return $order;
        }, 3);

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Nuskha order saved successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Orders — View and Customer Receipt
    |--------------------------------------------------------------------------
    */

    public function show(NuskhaOrder $order)
    {
        $this->ensureClinic($order);

        $order->load([
            'items',
            'payments' => fn ($query) =>
                $query->where('clinic_id', $this->clinicId())
                    ->orderBy('paid_at')
                    ->orderBy('nuskha_order_payment_id'),
        ]);

        $paidCents = $this->scaled(
            $order->payments->sum('amount'),
            2
        );

        return view('orders.show', [
            'order' => $order,
            'clinic' => auth()->user()->clinic,
            'paidAmount' => $this->decimal($paidCents),
            'dueAmount' => $this->decimal(max(
                0,
                $this->scaled($order->total_amount, 2) - $paidCents
            )),
            'statuses' => self::STATUSES,
            'enabledMethods' => $this->enabledMethods(),
            'decimalPlaces' => $this->decimalPlaces(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Orders — Receive Remaining Payment
    |--------------------------------------------------------------------------
    */

    public function receivePayment(Request $request, NuskhaOrder $order)
    {
        $this->ensureClinic($order);

        $data = $request->validate([
            'amount' => [...$this->moneyRules(), 'min:0.01'],

            'paid_at' => [
                'bail',
                'required',
                'date_format:Y-m-d\TH:i',
                'after_or_equal:1000-01-01',
                'before_or_equal:9999-12-31 23:59:59',
            ],

            'method' => ['required', Rule::in($this->enabledMethods())],

            'reference_no' => [
                Rule::requiredIf(
                    in_array($request->input('method'), ['bank', 'card'], true)
                ),
                'nullable',
                'string',
                'max:100',
            ],

            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($order, $data) {
            $locked = NuskhaOrder::query()
                ->where('clinic_id', $this->clinicId())
                ->whereKey($order->nuskha_order_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'amount' => 'A cancelled order cannot receive payment.',
                ]);
            }

            $paid = $this->scaled(
                NuskhaOrderPayment::query()
                    ->where('clinic_id', $this->clinicId())
                    ->where('nuskha_order_id', $locked->nuskha_order_id)
                    ->sum('amount'),
                2
            );

            $remaining = max(
                0,
                $this->scaled($locked->total_amount, 2) - $paid
            );

            $received = $this->scaled($data['amount'], 2);

            $this->checkPaymentAmount($received, $remaining);

            $locked->payments()->create([
                'clinic_id' => $this->clinicId(),
                'received_by_user_id' => auth()->id(),
                'paid_at' => str_replace('T', ' ', $data['paid_at']) . ':00',
                'amount' => $this->decimal($received),
                'method' => $data['method'],
                'reference_no' => $data['reference_no'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        }, 3);

        return back()->with('success', 'Order payment recorded.');
    }

    /*
    |--------------------------------------------------------------------------
    | Orders — Update Preparation/Fulfillment Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(Request $request, NuskhaOrder $order)
    {
        $this->ensureClinic($order);

        $data = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        DB::transaction(function () use ($order, $data) {
            $locked = NuskhaOrder::query()
                ->where('clinic_id', $this->clinicId())
                ->whereKey($order->nuskha_order_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === $data['status']) {
                return;
            }

            if (in_array($locked->status, ['completed', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Completed or cancelled orders cannot be reopened here.',
                ]);
            }

            if (
                $data['status'] === 'cancelled' &&
                $locked->payments()->exists()
            ) {
                throw ValidationException::withMessages([
                    'status' => 'This order has payments. Refund handling is required before cancellation.',
                ]);
            }

            $locked->update(['status' => $data['status']]);
        }, 3);

        return back()->with('success', 'Order status updated.');
    }

    /*
    |--------------------------------------------------------------------------
    | Orders — Payment Preferences and Exact Amount Helpers
    |--------------------------------------------------------------------------
    */

    private function checkPaymentAmount(int $received, int $remaining): void
    {
        if ($received > $remaining) {
            throw ValidationException::withMessages([
                'amount' => 'Payment cannot exceed the remaining balance.',
                'payment_amount' => 'Payment cannot exceed the order total.',
            ]);
        }

        $partial = Setting::query()
            ->where('clinic_id', $this->clinicId())
            ->where('setting_key', 'allow_partial_payments')
            ->value('setting_value');

        if (
            (string) $partial === '0' &&
            $received > 0 &&
            $received < $remaining
        ) {
            throw ValidationException::withMessages([
                'amount' => 'Partial payments are disabled. Pay the full remaining balance.',
                'payment_amount' => 'Partial payments are disabled. Pay the full order total.',
            ]);
        }
    }

    private function enabledMethods(): array
    {
        $settings = Setting::query()
            ->where('clinic_id', $this->clinicId())
            ->whereIn('setting_key', [
                'cash_enabled',
                'bank_enabled',
                'card_enabled',
            ])
            ->pluck('setting_value', 'setting_key');

        return array_keys(array_filter([
            'cash' => (string) $settings->get('cash_enabled', '1') === '1',
            'bank' => (string) $settings->get('bank_enabled', '1') === '1',
            'card' => (string) $settings->get('card_enabled', '1') === '1',
        ]));
    }

    private function decimalPlaces(): int
    {
        $value = Setting::query()
            ->where('clinic_id', $this->clinicId())
            ->where('setting_key', 'decimal_places')
            ->value('setting_value');

        return (string) $value === '0' ? 0 : 2;
    }

    private function moneyRules(): array
    {
        return [
            'required',
            'numeric',
            'min:0',
            'max:9999999999.99',
            'regex:/^\d{1,10}(?:\.\d{1,2})?$/',
        ];
    }

    private function scaled($value, int $places): int
    {
        [$whole, $fraction] = array_pad(
            explode('.', (string) $value, 2),
            2,
            ''
        );

        return ((int) $whole * (10 ** $places))
            + (int) str_pad(
                substr($fraction, 0, $places),
                $places,
                '0'
            );
    }

    private function decimal(int $value, int $places = 2): string
    {
        $factor = 10 ** $places;

        return intdiv($value, $factor)
            . '.'
            . str_pad(
                (string) ($value % $factor),
                $places,
                '0',
                STR_PAD_LEFT
            );
    }

    private function clinicId(): int
    {
        return (int) auth()->user()->clinic_id;
    }

    private function ensureClinic(NuskhaOrder $order): void
    {
        abort_unless(
            (int) $order->clinic_id === $this->clinicId(),
            404
        );
    }
}