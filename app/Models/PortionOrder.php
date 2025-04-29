<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortionOrder extends Model
{
    use HasFactory;

    protected $table = 'portion_orders';

    protected $fillable = [
        'detail',
        'user_id',
        'date',
        'movement' // Valores: "in", "out", "adjustment"
    ];

    // Relación con User
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    // Relación con los detalles de la orden
    public function details()
    {
        return $this->hasMany(PortionOrderDetail::class, 'portion_order_id');
    }
}
