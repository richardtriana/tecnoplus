<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'no_invoice',
        'total_paid',
        'total_iva_inc',
        'total_iva_exc',
        'total_discount',
        'state'
    ];

    protected $with = [
        'supplier'
    ];

    public function detailBillings()
    {
        return $this->hasMany(DetailBilling::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
