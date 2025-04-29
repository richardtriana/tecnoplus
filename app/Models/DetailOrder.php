<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'product_id',
        'barcode',
        'discount_percentage',
        'discount_price',
        'price_tax_exc',
        'price_tax_inc',
        'price_tax_inc_total',
        'quantity',
        'product',
        'cost_price_tax_inc',
        'cost_price_tax_inc_total',
        'observaciones', // Campo de observaciones
        'tax_rate'      // Nuevo campo para almacenar el porcentaje de impuesto aplicado
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
