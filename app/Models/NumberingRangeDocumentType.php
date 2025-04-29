<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumberingRangeDocumentType extends Model
{
    use HasFactory;

    protected $table = 'numbering_range_document_types';

    protected $fillable = [
        'code',
        'description'
    ];
}
