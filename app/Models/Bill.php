<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $table = 'bills';

    protected $primaryKey = 'bill_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'visit_id',
        'created_by_user_id',
        'bill_no',
        'bill_date',
        'subtotal',
        'discount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'bill_date' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
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

    public function visit()
    {
        return $this->belongsTo(Visit::class, 'visit_id', 'visit_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(
            User::class,
            'created_by_user_id',
            'user_id'
        );
    }

    public function items()
    {
        return $this->hasMany(
            BillItem::class,
            'bill_id',
            'bill_id'
        );
    }

    public function payments()
    {
        return $this->hasMany(
            Payment::class,
            'bill_id',
            'bill_id'
        );
    }

    public function ledgerEntries()
    {
        return $this->hasMany(
            LedgerEntry::class,
            'bill_id',
            'bill_id'
        );
    }
}