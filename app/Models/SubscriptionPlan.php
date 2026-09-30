<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $table = 'subscription_plans';

    protected $primaryKey = 'subscription_plan_id';

    protected $fillable = [
        'plan_code',
        'name',
        'term_type',
        'duration_days',
        'price',
        'currency',
        'is_active',
        'sort_order',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function clinicLicenses(): HasMany
    {
        return $this->hasMany(
            ClinicLicense::class,
            'subscription_plan_id',
            'subscription_plan_id'
        );
    }
}