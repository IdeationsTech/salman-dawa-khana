<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NuskhaItem extends Model
{
    use HasFactory;

    public const UNITS = [
        'mg' => 'mg',
        'g' => 'g',
        'kg' => 'kg',
        'ml' => 'ml',
        'l' => 'L',
        'piece' => 'Piece(s)',
        'tablet' => 'Tablet(s)',
        'capsule' => 'Capsule(s)',
        'sachet' => 'Sachet(s)',
        'bottle' => 'Bottle(s)',
    ];

    public const ITEM_FIELDS = [
        'item_name',
        'quantity',
        'unit',
        'dosage',
        'frequency',
        'duration',
        'timing',
        'instructions',
    ];

    protected $table = 'nuskha_items';

    protected $primaryKey = 'nuskha_item_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'nuskha_template_id',
        'item_name',
        'quantity',
        'unit',
        'dosage',
        'frequency',
        'duration',
        'timing',
        'instructions',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'sort_order' => 'integer',
        ];
    }

    public function template()
    {
        return $this->belongsTo(
            NuskhaTemplate::class,
            'nuskha_template_id',
            'nuskha_template_id'
        );
    }
}