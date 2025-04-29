<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjustmentNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'adjustment_note_id',
        'service_id',
        'code_reference',
        'name',
        'quantity',
        'discount_rate',
        'price',
        'unit_measure_id',
        'standard_code_id',
        'withholding_taxes',
    ];

    public function adjustmentNote()
    {
        return $this->belongsTo(AdjustmentNote::class);
    }
}
