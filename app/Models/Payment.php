<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $primaryKey = 'payment_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'bill_id',
        'received_by_user_id',
        'payment_date',
        'amount',
        'method',
        'reference_no',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'clinic_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id', 'bill_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(
            User::class,
            'received_by_user_id',
            'user_id'
        );
    }

    public function ledgerEntries()
    {
        return $this->hasMany(
            LedgerEntry::class,
            'payment_id',
            'payment_id'
        );
    }
}