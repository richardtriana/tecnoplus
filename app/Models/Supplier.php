<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'first_name',
        'second_name',
        'first_lastname',
        'second_lastname',
        'razon_social',
        'address',
        'phone',
        'email',
        'document',
        'div_verification',
        'municipality_id',
        'client_tribute_id',
        'identity_document_type_id',
        'organization_type_id',
        'active'
    ];

    // Relaciones opcionales
    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function clientTribute()
    {
        return $this->belongsTo(ClientTribute::class);
    }

    public function identityDocumentType()
    {
        return $this->belongsTo(IdentityDocumentType::class);
    }

    public function organizationType()
    {
        return $this->belongsTo(OrganizationType::class);
    }
}
