<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    use HasFactory;

    protected $table = 'ledger_entries';

    protected $primaryKey = 'ledger_entry_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'bill_id',
        'payment_id',
        'expense_id',
        'created_by_user_id',
        'entry_date',
        'entry_type',
        'debit',
        'credit',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'datetime',
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'clinic_id');
    }

    public function patient()
    {
        return $this->belongsTo(
            Patient::class,
            'patient_id',
            'patient_id'
        );
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id', 'bill_id');
    }

    public function payment()
    {
        return $this->belongsTo(
            Payment::class,
            'payment_id',
            'payment_id'
        );
    }

    public function expense()
    {
        return $this->belongsTo(
            Expense::class,
            'expense_id',
            'expense_id'
        );
    }

    public function createdBy()
    {
        return $this->belongsTo(
            User::class,
            'created_by_user_id',
            'user_id'
        );
    }
}