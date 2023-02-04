<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailBilling extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_id',
        'product_id',
        'barcode',
        'discount_percentage',
        'discount_price',
        'price_tax_exc',
        'price_tax_inc',
        'price_tax_inc_total',
        'quantity',
        'product',
    ];

    public function billing()
    {
        return $this->belongsTo(Billing::class, 'billing_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
