<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportDocumentItem extends Model
{
    protected $fillable = [
        'support_document_id',
        'service_id',
        'code_reference',
        'name',
        'quantity',
        'discount_rate',
        'discount',
        'gross_value',
        'tax_rate',
        'taxable_amount',
        'tax_amount',
        'price',
        'is_excluded',
        'unit_measure_id',
        'standard_code_id',
        'total',
        'withholding_taxes',
    ];

    // Convierte el campo 'withholding_taxes' a un array automáticamente
    protected $casts = [
        'withholding_taxes' => 'array',
    ];

    /**
     * Relación: un item pertenece a un documento soporte.
     */
    public function document()
    {
        return $this->belongsTo(SupportDocument::class, 'support_document_id');
    }
}
