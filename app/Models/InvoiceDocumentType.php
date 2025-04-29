<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDocumentType extends Model
{
    use HasFactory;

    protected $table = 'invoice_document_types';

    protected $fillable = [
        'code',
        'description'
    ];
}
