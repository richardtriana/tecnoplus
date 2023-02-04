<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsecutiveBox extends Model
{
    use HasFactory;

    protected $fillable = [
        'box_id',
        'from_nro', 
        'until_nro', 
        'from_date', 
        'until_date'
    ];
}
