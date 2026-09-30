<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformAuditLog extends Model
{
    protected $table = 'platform_audit_logs';

    protected $primaryKey = 'platform_audit_log_id';

    public const UPDATED_AT = null;

    protected $fillable = [
        'platform_admin_id',
        'clinic_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function platformAdmin(): BelongsTo
    {
        return $this->belongsTo(
            PlatformAdmin::class,
            'platform_admin_id',
            'platform_admin_id'
        );
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(
            Clinic::class,
            'clinic_id',
            'clinic_id'
        );
    }
}