<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $primaryKey = 'setting_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'clinic_id',
        'setting_key',
        'setting_value',
    ];

    public function clinic()
    {
        return $this->belongsTo(
            Clinic::class,
            'clinic_id',
            'clinic_id'
        );
    }
}