<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NuskhaOrderItem extends Model
{
    protected $table = 'nuskha_order_items';

    protected $primaryKey = 'nuskha_order_item_id';

    protected $fillable = [
        'nuskha_order_id',
        'nuskha_template_id',
        'nuskha_name',
        'instructions',
        'recipe_snapshot',
        'quantity',
        'unit',
        'unit_price',
        'line_total',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'recipe_snapshot' => 'array',
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(
            NuskhaOrder::class,
            'nuskha_order_id',
            'nuskha_order_id'
        );
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(
            NuskhaTemplate::class,
            'nuskha_template_id',
            'nuskha_template_id'
        );
    }
}