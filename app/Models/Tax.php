<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $table = 'taxes';

    // Actualizamos el array fillable para incluir code, name, porcentaje, active y description
    protected $fillable = [
        'code',
        'name',
        'porcentaje',
        'active',
        'description'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
