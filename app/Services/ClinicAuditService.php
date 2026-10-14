<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClinicAuditService
{
    private const SAFE_VALUES = [
        'name',
        'full_name',
        'email',
        'phone',
        'patient_code',
        'prescription_no',
        'bill_no',
        'order_no',
        'role_id',
        'is_active',
        'status',
        'patient_id',
        'visit_id',
        'doctor_user_id',
        'nuskha_template_id',
        'nuskha_name',
        'quantity',
        'unit',
        'unit_price',
        'line_total',
        'subtotal',
        'discount',
        'delivery_fee',
        'total_amount',
        'paid_amount',
        'due_amount',
        'amount',
        'currency',
        'country_code',
        'method',
        'payment_method',
        'fulfillment_method',
        'order_source',
        'setting_key',
    ];

    public function record(
        int $clinicId,
        string $action,
        string $entityType,
        ?int $entityId,
        array $metadata = [],
        ?int $actorId = null
    ): void {
        $actorId ??= Auth::guard('web')->id();

        if (!$actorId) {
            return;
        }

        $belongsToClinic = DB::table('users')
            ->where('user_id', $actorId)
            ->where('clinic_id', $clinicId)
            ->exists();

        if (!$belongsToClinic) {
            return;
        }

        DB::table('audit_logs')->insert([
            'clinic_id' => $clinicId,
            'user_id' => $actorId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'metadata' => json_encode(
                $this->clean($metadata),
                JSON_THROW_ON_ERROR
            ),
            'created_at' => now(),
        ]);

        if (app()->bound('request')) {
            request()->attributes->set('clinic_model_audited', true);
        }
    }

    public function capture(Model $model, string $event): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        if (request()->routeIs('password.update')) {
            return;
        }

        $actor = Auth::guard('web')->user();

        if (!$actor) {
            return;
        }

        $clinicId = $this->modelClinicId($model);

        if (!$clinicId || $clinicId !== (int) $actor->clinic_id) {
            return;
        }

        $attributes = $model->getAttributes();

        $changed = $event === 'updated'
            ? $model->getChanges()
            : $attributes;

        unset($changed['created_at'], $changed['updated_at'], $changed['remember_token']);

        if ($event === 'updated' && $changed === []) {
            return;
        }

        $keys = array_keys($changed);

        $newValues = Arr::only(
            $attributes,
            array_intersect($keys, self::SAFE_VALUES)
        );

        $oldValues = $event === 'created'
            ? []
            : Arr::only(
                $model->getRawOriginal(),
                array_intersect($keys, self::SAFE_VALUES)
            );

        $this->record(
            $clinicId,
            $model->getTable() . '.' . $event,
            $model->getTable(),
            (int) $model->getKey(),
            [
                'changed_fields' => $keys,
                'old' => $oldValues,
                'new' => $event === 'deleted' ? [] : $newValues,
            ]
        );
    }

    private function modelClinicId(Model $model): ?int
    {
        if ($model->getTable() === 'clinics') {
            return (int) $model->getKey();
        }

        if ($model->getAttribute('clinic_id') !== null) {
            return (int) $model->getAttribute('clinic_id');
        }

        $parent = match ($model->getTable()) {
            'bill_items' => ['bills', 'bill_id'],
            'prescription_items' => ['prescriptions', 'prescription_id'],
            'nuskha_items' => ['nuskha_templates', 'nuskha_template_id'],
            'nuskha_order_items' => ['nuskha_orders', 'nuskha_order_id'],
            default => null,
        };

        if (!$parent) {
            return null;
        }

        [$table, $key] = $parent;

        $clinicId = DB::table($table)
            ->where($key, $model->getAttribute($key))
            ->value('clinic_id');

        return $clinicId !== null ? (int) $clinicId : null;
    }

    private function clean(array $values): array
    {
        $blocked = [
            'password',
            'password_confirmation',
            'remember_token',
            'token',
            'reset_token',
            'secret',
            'api_key',
            'recipe_snapshot',
        ];

        $clean = [];

        foreach ($values as $key => $value) {
            if (in_array(strtolower((string) $key), $blocked, true)) {
                continue;
            }

            $clean[$key] = is_array($value)
                ? $this->clean($value)
                : $value;
        }

        return $clean;
    }
}