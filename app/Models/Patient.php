<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $table = 'patients';

    protected $primaryKey = 'patient_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'clinic_id',
        'patient_code',
        'full_name',
        'father_or_husband_name',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'notes',
        'status',
        'marital_status',
        'has_children',
        'children_count',
    ];

    protected function casts(): array
    {
        return [
        'date_of_birth' => 'date',
        'has_children' => 'boolean',
        'children_count' => 'integer',
        ];
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'clinic_id');
    }

    public function visits()
    {
        return $this->hasMany(Visit::class, 'patient_id', 'patient_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(
            Prescription::class,
            'patient_id',
            'patient_id'
        );
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'patient_id', 'patient_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'patient_id', 'patient_id');
    }

    public function ledgerEntries()
    {
        return $this->hasMany(
            LedgerEntry::class,
            'patient_id',
            'patient_id'
        );
    }
}