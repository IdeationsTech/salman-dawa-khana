<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PlatformAdmin extends Authenticatable
{
    protected $table = 'platform_admins';

    protected $primaryKey = 'platform_admin_id';

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function licensesIssued(): HasMany
    {
        return $this->hasMany(
            ClinicLicense::class,
            'issued_by_platform_admin_id',
            'platform_admin_id'
        );
    }

    public function paymentsRecorded(): HasMany
    {
        return $this->hasMany(
            LicensePayment::class,
            'recorded_by_platform_admin_id',
            'platform_admin_id'
        );
    }

    public function paymentsVerified(): HasMany
    {
        return $this->hasMany(
            LicensePayment::class,
            'verified_by_platform_admin_id',
            'platform_admin_id'
        );
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(
            PlatformAuditLog::class,
            'platform_admin_id',
            'platform_admin_id'
        );
    }
}