<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'user_id',
        'bill_number',
        'no_invoice',
        'reference_code',
        'cufe',
        'qr',
        'validated',
        'qr_image',
        'numbering_range_id',
        'document_code',
        'total_paid',
        'total_iva_inc',
        'total_iva_exc',
        'total_discount',
        'total_cost_price_tax_inc',
        'state',
        'payment_date',
        'payment_methods',
        'box_id',
        'observations',
        'payment_form_id',
        'payment_method_id',
        'factus_response',
        'factus_bill_id',
        'factus_status',
        'factus_bill_number',
        'factus_validated',
        'factus_qr',
        'payment_method_code',
        'invoiced_by',
        'table_id',
        'proccess',
        'status_dian',
    ];

    protected $casts = [
        'payment_methods' => 'array',
        'status_dian'     => 'integer',
    ];

    protected $appends = [
        'paid_payment',
    ];

    protected $with = [
        'client',
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

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function numberingRange()
    {
        return $this->belongsTo(NumberingRange::class, 'numbering_range_id');
    }

    public function getNumberingRange()
    {
        return $this->numberingRange;
    }

    public function consecutiveBox()
    {
        if (! $this->box || ! $this->bill_number) {
            return null;
        }
        $prefix = $this->box->prefix;
        $consecutive = intval(str_replace($prefix, '', $this->bill_number));

        return $this->box->numberingRanges()
            ->where('from', '<=', $consecutive)
            ->where('to', '>=', $consecutive)
            ->orderBy('from', 'asc')
            ->first();
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
        return $this->hasManyThrough(
            Product::class,
            DetailOrder::class,
            'order_id',
            'id',
            'id',
            'product_id'
        );
    }

    public function printers()
    {
        return $this->products()
            ->with('zones')
            ->get()
            ->pluck('zones')
            ->flatten()
            ->unique()
            ->values();
    }

    public function invoicedBy()
    {
        return $this->belongsTo(User::class, 'invoiced_by');
    }

    public function paymentForm()
    {
        return $this->belongsTo(PaymentForm::class, 'payment_form_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}
