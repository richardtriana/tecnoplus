<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portion extends Model
{
    use HasFactory;

    protected $table = 'portions';

    protected $fillable = [
        'description',
        'quantity',
        'type',    // Ej.: "warehouse", "shelf"
        'status'   // 1 = active, 0 = inactive
    ];
}
