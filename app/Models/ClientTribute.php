<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientTribute extends Model
{
    use HasFactory;

    protected $table = 'client_tributes';

    protected $fillable = [
        'name'
    ];
}
