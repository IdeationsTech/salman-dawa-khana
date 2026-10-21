<?php

namespace App\Models;

use App\Casts\ClinicDateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NuskhaOrderPayment extends Model
{
    protected $table = 'nuskha_order_payments';

    protected $primaryKey = 'nuskha_order_payment_id';

    protected $fillable = [
        'clinic_id',
        'nuskha_order_id',
        'received_by_user_id',
        'paid_at',
        'amount',
        'method',
        'reference_no',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => ClinicDateTime::class,
            'amount' => 'decimal:2',
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(
            NuskhaOrder::class,
            'nuskha_order_id',
            'nuskha_order_id'
        );
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'received_by_user_id',
            'user_id'
        );
    }
}
