<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'no_invoice',
        'total_paid',
        'total_iva_inc',
        'total_iva_exc',
        'total_cost_price_tax_inc',
        'total_discount',
        'payment_date',
        'state',
        'box_id',
        'payment_methods'
    ];

    protected $casts = [
        'payment_methods' => 'array'
    ];

    protected $appends = [
        'paid_payment'
    ];

    protected $with = [
        'client'
    ];

    public function detailOrders()
    {
        return $this->hasMany(DetailOrder::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function box()
    {
        return $this->belongsTo(Box::class);
    }

    public function consecutiveBox()
    {

        $consecutive = str_replace($this->box->prefix, '', $this->bill_number);
        $consecutive = intval($consecutive);

        $consecutiveBox = $this->box->consecutiveBox()->where([
            ['from_nro', '<=', $consecutive],
            ['until_nro', '>=', $consecutive]
        ])
            ->orderBy('from_nro')
            ->first();

        return $consecutiveBox;
    }

    public function paymentCredits()
    {
        return $this->hasMany(PaymentCredit::class);
    }

    public function getPaidPaymentAttribute()
    {
        return $this->paymentCredits->sum('pay');
    }

    public function products()
    {
        return $this->hasManyThrough(Product::class, DetailOrder::class, 'order_id', 'id', 'id', 'product_id');
    }

    public function printers(){
        return $this->products()->with('zone')->get()->pluck('zone');
    }
}
