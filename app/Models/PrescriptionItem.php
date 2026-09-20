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
        'dosage',
        'frequency',
        'duration',
        'timing',
        'instructions',
        'sort_order',
    ];

    public function prescription()
    {
        return $this->belongsTo(
            Prescription::class,
            'prescription_id',
            'prescription_id'
        );
    }
}