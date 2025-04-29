<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPortion extends Model
{
    use HasFactory;

    protected $table = 'product_portions';

    protected $fillable = [
        'product_id',
        'portion_id',
        'quantity'
    ];

    /**
     * Relación con el modelo Portion (para poder acceder a su 'description').
     */
    public function portion()
    {
        return $this->belongsTo(Portion::class, 'portion_id');
    }

    /**
     * Relación con el modelo Product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
