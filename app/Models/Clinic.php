<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Clinic extends Model
{
    use HasFactory;

    protected $table = 'clinics';

    protected $primaryKey = 'clinic_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'currency',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'clinic_id', 'clinic_id');
    }

    public function settings()
    {
        return $this->hasMany(Setting::class, 'clinic_id', 'clinic_id');
    }

    public function patients()
    {
        return $this->hasMany(Patient::class, 'clinic_id', 'clinic_id');
    }

    public function visits()
    {
        return $this->hasMany(Visit::class, 'clinic_id', 'clinic_id');
    }

    public function nuskhaTemplates()
    {
        return $this->hasMany(NuskhaTemplate::class, 'clinic_id', 'clinic_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'clinic_id', 'clinic_id');
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'clinic_id', 'clinic_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'clinic_id', 'clinic_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'clinic_id', 'clinic_id');
    }

    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class, 'clinic_id', 'clinic_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'clinic_id', 'clinic_id');
    }

    public function saasCustomer(): BelongsTo
    {
        return $this->belongsTo(
            SaasCustomer::class,
            'saas_customer_id',
            'saas_customer_id'
        );
    }

    public function license(): HasOne
    {
        return $this->hasOne(
            ClinicLicense::class,
            'clinic_id',
            'clinic_id'
        );
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(
            PlatformAdmin::class,
            'reviewed_by_platform_admin_id',
            'platform_admin_id'
        );
    }
}