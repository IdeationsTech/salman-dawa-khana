<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    use HasFactory;

    protected $table = 'prescription_items';

    protected $primaryKey = 'prescription_item_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'prescription_id',
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

    public function prescription()
    {
        return $this->belongsTo(
            Prescription::class,
            'prescription_id',
            'prescription_id'
        );
    }
}