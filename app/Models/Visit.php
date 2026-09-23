<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $table = 'visits';

    protected $primaryKey = 'visit_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    const UPDATED_AT = null;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'recorded_by_user_id',
        'visit_date',
        'visit_reason',
        'general_notes',
        'diagnosis_name',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'datetime',
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

    public function recordedBy()
    {
        return $this->belongsTo(
            User::class,
            'recorded_by_user_id',
            'user_id'
        );
    }

    public function prescription()
    {
        return $this->hasOne(
            Prescription::class,
            'visit_id',
            'visit_id'
        );
    }

    public function bill()
    {
        return $this->hasOne(
            Bill::class,
            'visit_id',
            'visit_id'
        );
    }
}