<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortionHistory extends Model
{
    use HasFactory;

    protected $table = 'portion_histories';

    protected $fillable = [
        'portion_id',
        'movement', // "in", "out", "adjustment", "order" etc 
        'quantity'
    ];

    public function portion()
    {
        return $this->belongsTo(Portion::class, 'portion_id');
    }
}
