<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitProduct extends Model
{
    use HasFactory;

    protected $table = 'kit_products';

    protected $fillable = [
        'product_parent_id',
        'product_child_id',
        'barcode',
        'product',
        'quantity'
    ];

    public function parent()
    {
        return $this->belongsTo(Product::class, 'product_parent_id');
    }

    // public function child()
    // {
    //     return $this->belongsTo(Product::class, 'product_child_id');
    // }
}
