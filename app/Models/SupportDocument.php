<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportDocument extends Model
{
    protected $fillable = [
        'reference_code',
        'numbering_range_id',
        'payment_method_code',
        'observation',
        'provider_id',
        'number',
        'status',
        'qr',
        'cuds',
        'validated',
        'discount_rate',
        'discount',
        'gross_value',
        'taxable_amount',
        'tax_amount',
        'total',
        'errors',
        'qr_image',
        'factus_support_document_id', // nuevo campo para almacenar el id retornado por Factus
    ];

    // Relación con NumberingRange
    public function numberingRange()
    {
        return $this->belongsTo(NumberingRange::class, 'numbering_range_id');
    }

    // Método para obtener el rango de numeración
    public function getNumberingRange()
    {
        return $this->numberingRange;
    }

    // Relación con items
    public function items()
    {
        return $this->hasMany(SupportDocumentItem::class);
    }

    // Relación con proveedor
    public function provider()
    {
        return $this->belongsTo(Supplier::class, 'provider_id');
    }
}
