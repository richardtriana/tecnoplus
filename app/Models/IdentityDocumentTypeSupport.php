<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentityDocumentTypeSupport extends Model
{
    use HasFactory;

    protected $table = 'identity_document_type_supports';

    protected $fillable = [
        'name'
    ];
}
