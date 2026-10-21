<?php

namespace App\Models;

use App\Casts\ClinicDateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NuskhaOrder extends Model
{
    protected $table = 'nuskha_orders';

    protected $primaryKey = 'nuskha_order_id';

    protected $fillable = [
        'clinic_id',
        'created_by_user_id',
        'order_no',
        'ordered_at',
        'customer_name',
        'customer_phone',
        'order_source',
        'fulfillment_method',
        'delivery_address',
        'status',
        'currency',
        'subtotal',
        'discount',
        'delivery_fee',
        'total_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'ordered_at' => ClinicDateTime::class,
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(
            Clinic::class,
            'clinic_id',
            'clinic_id'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by_user_id',
            'user_id'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            NuskhaOrderItem::class,
            'nuskha_order_id',
            'nuskha_order_id'
        )->orderBy('sort_order')
            ->orderBy('nuskha_order_item_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            NuskhaOrderPayment::class,
            'nuskha_order_id',
            'nuskha_order_id'
        );
    }
}
