<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $fillable = [
        'codigo',
        'name',
        'active'
    ];

    // Si quieres que 'active' sea boolean, puedes usar casts:
    protected $casts = [
        'active' => 'boolean'
    ];
}
