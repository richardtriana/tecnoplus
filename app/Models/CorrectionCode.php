<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionCode extends Model
{
    use HasFactory;

    protected $table = 'correction_codes';

    protected $fillable = [
        'code',
        'description'
    ];
}
