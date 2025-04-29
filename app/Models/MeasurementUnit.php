<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeasurementUnit extends Model
{
    use HasFactory;

    protected $table = 'measurement_units';

    protected $fillable = [
        'id', // Agregar este campo para permitir asignación masiva
        'code',
        'name'
    ];
}
