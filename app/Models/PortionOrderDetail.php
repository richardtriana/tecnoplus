<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortionOrderDetail extends Model
{
    use HasFactory;

    protected $table = 'portion_order_details';

    protected $fillable = [
        'portion_order_id',
        'portion_id',
        'movement', // "ingreso", "salida", "adjustment"
        'quantity'
    ];

    public function portionOrder()
    {
        return $this->belongsTo(PortionOrder::class, 'portion_order_id');
    }

    public function portion()
    {
        return $this->belongsTo(Portion::class, 'portion_id');
    }
}
