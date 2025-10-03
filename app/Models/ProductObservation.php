<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductObservation extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada.
     *
     * @var string
     */
    protected $table = 'product_observations';

    /**
     * Atributos asignables masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'observation',
    ];

    /**
     * Relación inversa: cada observación pertenece a un producto.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
