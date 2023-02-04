<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $table = 'clients';

    protected $fillable = [
        'name',
        'address',
        'mobile',
        'contact',
        'email',
        'type_person',
        'municipality_id',
        'type_document',
        'document',
        'active',
        'tax'
    ];

    protected $with = [
        'municipality'
    ];
    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }
}
