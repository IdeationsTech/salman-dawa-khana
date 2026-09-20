<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NuskhaTemplate extends Model
{
    use HasFactory;

    protected $table = 'nuskha_templates';

    protected $primaryKey = 'nuskha_template_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'clinic_id',
        'name',
        'instructions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function clinic()
    {
        return $this->belongsTo(
            Clinic::class,
            'clinic_id',
            'clinic_id'
        );
    }

    public function items()
    {
        return $this->hasMany(
            NuskhaItem::class,
            'nuskha_template_id',
            'nuskha_template_id'
        )->orderBy('sort_order');
    }
}