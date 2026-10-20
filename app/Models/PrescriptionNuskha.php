<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionNuskha extends Model
{
    public const UNITS = [
        'g' => 'g — Gram',
        'kg' => 'kg — Kilogram',
        'mg' => 'mg — Milligram',
        'mcg' => 'mcg — Microgram',
        'l' => 'l — Litre',
        'ml' => 'ml — Millilitre',
        'bottle' => 'Bottle',
        'pack' => 'Pack',
        'sachet' => 'Sachet',
        'tablet' => 'Tablet',
        'capsule' => 'Capsule',
        'drop' => 'Drop',
        'piece' => 'Piece',
    ];

    protected $table = 'prescription_nuskhas';

    protected $primaryKey = 'prescription_nuskha_id';

    protected $fillable = [
        'prescription_id', 'nuskha_template_id', 'nuskha_name',
        'quantity', 'unit', 'frequency', 'duration', 'timing',
        'instructions', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'sort_order' => 'integer',
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NuskhaTemplate::class, 'nuskha_template_id');
    }
}
