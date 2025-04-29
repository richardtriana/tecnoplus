<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCredit extends Model
{
    // Only the actual columns in your order_credits table:
    protected $fillable = [
        'order_id',
        'total_credit',
        'balance',
        'status',
    ];

    protected $casts = [
        'total_credit' => 'decimal:2',
        'balance'      => 'decimal:2',
    ];

    /**
     * The order this credit belongs to.
     */
    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class, 'order_id');
    }

    /**
     * All the individual payments made against this credit.
     */
    public function payments()
    {
        return $this->hasMany(\App\Models\PaymentCredit::class, 'order_id');
    }
}
