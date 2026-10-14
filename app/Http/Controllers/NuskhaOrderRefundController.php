<?php

namespace App\Http\Controllers;

use App\Models\NuskhaOrder;
use App\Services\ClinicAuditService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NuskhaOrderRefundController extends Controller
{
    public function create(NuskhaOrder $order)
    {
        $this->ensureClinic($order);

        $available = Money::scaled(
            $order->payments()
                ->where('clinic_id', auth()->user()->clinic_id)
                ->sum('amount')
        );

        if ($available <= 0 || $order->status === 'cancelled') {
            return redirect()->route('orders.show', $order)->withErrors([
                'refund' => 'This order has no available amount to refund.',
            ]);
        }

        return view('orders.refund', [
            'order' => $order,
            'availableAmount' => Money::decimal($available),
            'requestKey' => (string) Str::uuid(),
        ]);
    }

    public function store(Request $request, NuskhaOrder $order)
    {
        $this->ensureClinic($order);

        $data = $request->validate([
            'request_key' => ['required', 'uuid'],
            'amount' => [
                'required', 'numeric', 'min:0.01',
                'max:9999999999.99', 'regex:/^\d{1,10}(?:\.\d{1,2})?$/',
            ],
            'refunded_at' => [
                'bail', 'required', 'date_format:Y-m-d\TH:i',
                'after_or_equal:1000-01-01',
                'before_or_equal:9999-12-31 23:59:59',
            ],
            'method' => ['required', Rule::in(['cash', 'bank', 'card'])],
            'reference_no' => [
                Rule::requiredIf(in_array($request->input('method'), ['bank', 'card'], true)),
                'nullable', 'string', 'max:100',
            ],
            'reason' => ['required', 'string', 'max:1000'],
            'cancel_order' => ['required', 'boolean'],
        ]);

        $clinicId = (int) $request->user()->clinic_id;

        $message = DB::transaction(function () use ($order, $data, $clinicId) {
            $locked = NuskhaOrder::where('clinic_id', $clinicId)
                ->whereKey($order->nuskha_order_id)
                ->lockForUpdate()->firstOrFail();

            $previous = DB::table('nuskha_order_refunds')
                ->where('clinic_id', $clinicId)
                ->where('request_key', $data['request_key'])
                ->first();

            if ($previous) {
                if ((int) $previous->nuskha_order_id !== (int) $locked->nuskha_order_id) {
                    throw ValidationException::withMessages([
                        'refund' => 'This refund request belongs to another order.',
                    ]);
                }

                return 'This refund was already recorded. No duplicate refund was created.';
            }

            if ($locked->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'refund' => 'This order is already cancelled.',
                ]);
            }

            $available = Money::scaled(
                $locked->payments()->where('clinic_id', $clinicId)->sum('amount')
            );

            $refund = Money::scaled($data['amount']);

            if ($refund > $available || $available <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Refund cannot exceed the net amount received.',
                ]);
            }

            if ((bool) $data['cancel_order'] && $refund !== $available) {
                throw ValidationException::withMessages([
                    'amount' => 'Refund the full available amount to cancel this order.',
                ]);
            }

            $refundedAt = str_replace('T', ' ', $data['refunded_at']) . ':00';

            $latestPayment = $locked->payments()
                ->where('clinic_id', $clinicId)
                ->where('amount', '>', 0)
                ->max('paid_at');

            if ($latestPayment && $refundedAt < $latestPayment) {
                throw ValidationException::withMessages([
                    'refunded_at' => 'Refund date cannot be before the latest received payment.',
                ]);
            }

            $adjustment = $locked->payments()->create([
                'clinic_id' => $clinicId,
                'received_by_user_id' => auth()->id(),
                'paid_at' => $refundedAt,
                'amount' => Money::decimal(-$refund),
                'method' => $data['method'],
                'reference_no' => $data['reference_no'] ?? null,
                'notes' => Str::limit('Refund: ' . $data['reason'], 500, ''),
            ]);

            $refundId = DB::table('nuskha_order_refunds')->insertGetId([
                'clinic_id' => $clinicId,
                'nuskha_order_id' => $locked->nuskha_order_id,
                'refunded_by_user_id' => auth()->id(),
                'refund_payment_id' => $adjustment->nuskha_order_payment_id,
                'request_key' => $data['request_key'],
                'refunded_at' => $refundedAt,
                'amount' => Money::decimal($refund),
                'currency' => $locked->currency,
                'method' => $data['method'],
                'reference_no' => $data['reference_no'] ?? null,
                'reason' => $data['reason'],
                'created_at' => now(),
                'updated_at' => now(),
            ], 'nuskha_order_refund_id');

            if ((bool) $data['cancel_order']) {
                $locked->update(['status' => 'cancelled']);
            }

            app(ClinicAuditService::class)->record(
                $clinicId,
                'order.refunded',
                'nuskha_order_refunds',
                $refundId,
                [
                    'order_id' => $locked->nuskha_order_id,
                    'amount' => Money::decimal($refund),
                    'currency' => $locked->currency,
                    'method' => $data['method'],
                    'reason' => $data['reason'],
                    'order_cancelled' => (bool) $data['cancel_order'],
                ]
            );

            return (bool) $data['cancel_order']
                ? 'Refund recorded and order cancelled.'
                : 'Refund recorded successfully.';
        }, 3);

        return redirect()->route('orders.show', $order)
            ->with('success', $message);
    }

    private function ensureClinic(NuskhaOrder $order): void
    {
        abort_unless(
            (int) $order->clinic_id === (int) auth()->user()->clinic_id,
            404
        );
    }
}