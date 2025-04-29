<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjustmentNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_code',
        'numbering_range_id',
        'payment_method_code',
        'support_document_id',
        'correction_concept_code',
        'observation',
        'total',
        'status',
        'number',
        'qr',
        'cuds',
        'validated',
        'qr_image',
        'errors',
    ];

    // Relación con el documento soporte
    public function supportDocument()
    {
        return $this->belongsTo(SupportDocument::class, 'support_document_id');
    }

    // Relación con los ítems de la nota de ajuste
    public function items()
    {
        return $this->hasMany(AdjustmentNoteItem::class);
    }
}
