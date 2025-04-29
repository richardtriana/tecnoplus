<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTribute extends Model
{
    use HasFactory;

    protected $table = 'product_tributes';

    protected $fillable = [
        'id', // Permitir asignación masiva del id desde la API
        'code',
        'name',
        'description'
    ];
}
