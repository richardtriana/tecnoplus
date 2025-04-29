<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimConceptCode extends Model
{
    use HasFactory;

    protected $table = 'claim_concept_codes';

    protected $fillable = [
        'code',
        'name'
    ];
}
