<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $table = 'prescriptions';

    protected $primaryKey = 'prescription_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'visit_id',
        'created_by_user_id',
        'prescription_no',
        'prescribed_at',
        'general_instructions',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'prescribed_at' => 'datetime',
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
            PrescriptionItem::class,
            'prescription_id',
            'prescription_id'
        )->orderBy('sort_order');
    }
}