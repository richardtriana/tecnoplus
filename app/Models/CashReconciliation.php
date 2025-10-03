<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashReconciliation extends Model
{
    use HasFactory;

    protected $table = 'cash_reconciliations';

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'box_id',
        'opening_user_id',
        'closing_user_id',
        'opening_balance',
        'entries',
        'exits',
        'credits',
        'computed_balance',
        'reported_balance',
        'difference',
        'is_open',
        'opened_at',
        'closed_at',
    ];

    /**
     * Casts de atributos a tipos nativos.
     */
    protected $casts = [
        'opening_balance'   => 'decimal:2',
        'entries'           => 'decimal:2',
        'exits'             => 'decimal:2',
        'credits'           => 'decimal:2',
        'computed_balance'  => 'decimal:2',
        'reported_balance'  => 'decimal:2',
        'difference'        => 'decimal:2',
        'is_open'           => 'boolean',
        'opened_at'         => 'datetime',
        'closed_at'         => 'datetime',
    ];

    /**
     * Relación con la caja.
     */
    public function box()
    {
        return $this->belongsTo(Box::class);
    }

    /**
     * Usuario que abrió la caja.
     */
    public function openingUser()
    {
        return $this->belongsTo(User::class, 'opening_user_id');
    }

    /**
     * Usuario que cerró la caja.
     */
    public function closingUser()
    {
        return $this->belongsTo(User::class, 'closing_user_id');
    }
}
