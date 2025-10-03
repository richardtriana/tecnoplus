<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use HasFactory;

    protected $table = 'configurations';

    protected $fillable = [
        'name',
        'legal_representative',
        'nit',
        'address',
        'email',
        'tax_regime',
        'telephone',
        'mobile',
        'logo',
        'printer',
        'condition_order',
        'condition_quotation',
        'shipments',            // ← Añadido aquí
    ];

    protected $casts = [
        'shipments' => 'boolean',  // ← Para que siempre venga como bool
    ];
}
