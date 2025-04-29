<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditNoteDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_note_id',
        'code_reference',
        'name',
        'quantity',
        'discount_rate',
        'price',
        'tax_rate',
        'unit_measure_id',
        'standard_code_id',
        'is_excluded',
        'tribute_id',
        'withholding_taxes',
        // Campos opcionales que puedas guardar de la respuesta de Factus:
        'discount',
        'gross_value',
        'taxable_amount',
        'tax_amount',
        'total'
    ];

    protected $casts = [
        'withholding_taxes' => 'array'
    ];

    // Relación con la nota crédito
    public function creditNote()
    {
        return $this->belongsTo(CreditNote::class);
    }
}
