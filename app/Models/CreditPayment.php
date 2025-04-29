<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPayment extends Model
{
    protected $fillable = [
        'order_credit_id',
        'user_id',
        'amount',
        'payment_method',
        'reference',
    ];

    public function credit()
    {
        return $this->belongsTo(OrderCredit::class, 'order_credit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
