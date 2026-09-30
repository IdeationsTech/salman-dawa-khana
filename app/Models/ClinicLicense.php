<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicLicense extends Model
{
    protected $table = 'clinic_licenses';

    protected $primaryKey = 'clinic_license_id';

    protected $fillable = [
        'clinic_id',
        'subscription_plan_id',
        'issued_by_platform_admin_id',
        'status',
        'grant_type',
        'starts_at',
        'ends_at',
        'grant_reason',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
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

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(
            SubscriptionPlan::class,
            'subscription_plan_id',
            'subscription_plan_id'
        );
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(
            PlatformAdmin::class,
            'issued_by_platform_admin_id',
            'platform_admin_id'
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            LicensePayment::class,
            'clinic_license_id',
            'clinic_license_id'
        );
    }
}