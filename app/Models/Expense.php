<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $table = 'expenses';

    protected $primaryKey = 'expense_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'clinic_id',
        'recorded_by_user_id',
        'expense_date',
        'category',
        'description',
        'amount',
        'payment_method',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'clinic_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(
            User::class,
            'recorded_by_user_id',
            'user_id'
        );
    }

    public function ledgerEntries()
    {
        return $this->hasMany(
            LedgerEntry::class,
            'expense_id',
            'expense_id'
        );
    }
}