<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicensePayment extends Model
{
    protected $table = 'license_payments';

    protected $primaryKey = 'license_payment_id';

    protected $fillable = [
        'clinic_license_id',
        'recorded_by_platform_admin_id',
        'verified_by_platform_admin_id',
        'amount',
        'currency',
        'payment_method',
        'reference_no',
        'paid_at',
        'status',
        'receipt_path',
        'verified_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function clinicLicense(): BelongsTo
    {
        return $this->belongsTo(
            ClinicLicense::class,
            'clinic_license_id',
            'clinic_license_id'
        );
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(
            PlatformAdmin::class,
            'recorded_by_platform_admin_id',
            'platform_admin_id'
        );
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(
            PlatformAdmin::class,
            'verified_by_platform_admin_id',
            'platform_admin_id'
        );
    }
}