<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductIdentificationStandard extends Model
{
    use HasFactory;

    protected $table = 'product_identification_standards';

    protected $fillable = [
        'name'
    ];
}
