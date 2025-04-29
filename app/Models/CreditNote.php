<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'client_id',
        'numbering_range_id',
        'correction_concept_code',
        'customization_id',
        'bill_id',
        'reference_code',
        'observation',
        'payment_method_code',
        'items',
        'factus_response'
    ];

    protected $casts = [
        'items' => 'array',
        'factus_response' => 'array'
    ];

    // Relación con la orden asociada
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Relación con el cliente (generalmente, el mismo de la orden)
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relación con los detalles de la nota crédito
    public function details()
    {
        return $this->hasMany(CreditNoteDetail::class);
    }
}
