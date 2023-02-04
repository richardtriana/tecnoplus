<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCredit extends Model
{
    use HasFactory;

    protected $hidden = [
        'updated_at'
    ];
    
    protected $fillable = [
        'user_id', 
        'order_id',
        'pay'
    ];
    
}
