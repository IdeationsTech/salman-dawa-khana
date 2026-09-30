<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaasCustomer extends Model
{
    protected $table = 'saas_customers';

    protected $primaryKey = 'saas_customer_id';

    protected $fillable = [
        'contact_name',
        'company_name',
        'email',
        'phone',
        'country_code',
        'status',
        'notes',
    ];

    public function clinics(): HasMany
    {
        return $this->hasMany(
            Clinic::class,
            'saas_customer_id',
            'saas_customer_id'
        );
    }
}