<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NuskhaItem extends Model
{
    use HasFactory;

    protected $table = 'nuskha_items';

    protected $primaryKey = 'nuskha_item_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'nuskha_template_id',
        'item_name',
        'dosage',
        'frequency',
        'duration',
        'timing',
        'instructions',
        'sort_order',
    ];

    public function template()
    {
        return $this->belongsTo(
            NuskhaTemplate::class,
            'nuskha_template_id',
            'nuskha_template_id'
        );
    }
}