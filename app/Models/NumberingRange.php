<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NumberingRange extends Model
{
    protected $table = 'numbering_ranges';

    // Campos asignables en masa
    protected $fillable = [
        'document',
        'prefix',
        'from',
        'to',
        'current',
        'resolution_number',
        'start_date',
        'end_date',
        'technical_key',
        'is_expired',
        'is_active',
        'enviado_dian',
    ];
}
