<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    /**
     * Los atributos asignables masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'barcode',
        'product',
        'type',
        'state',
        'cost_price_tax_exc',
        'cost_price_tax_inc',
        'cost_tax_value',                      
        'gain',
        'sale_price_tax_exc',
        'sale_price_tax_inc',
        'sale_tax_value',                      
        'wholesale_price_tax_exc',
        'wholesale_price_tax_inc',
        'stock',
        'quantity',
        'minimum',
        'maximum',
        'expiration_date',                     
        'category_id',
        'tax_id',
        'measurement_unit_id',                 
        'product_identification_standard_id',  
        'uses_portions'                        
    ];

    /**
     * Relaciones a cargar por defecto.
     *
     * @var array
     */
    protected $with = [
        'category',
        'tax',
        'measurementUnit',
        'productIdentificationStandard',
        'portions' // Se carga la relación de porciones automáticamente
    ];

    /**
     * Relación con la categoría.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Relación con el impuesto.
     */
    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    /**
     * Relación con la unidad de medida.
     */
    public function measurementUnit()
    {
        return $this->belongsTo(MeasurementUnit::class, 'measurement_unit_id');
    }

    /**
     * Relación con el estándar de identificación del producto.
     */
    public function productIdentificationStandard()
    {
        return $this->belongsTo(ProductIdentificationStandard::class, 'product_identification_standard_id');
    }

    /**
     * Relación con las zonas (muchos a muchos).
     */
    public function zones()
    {
        return $this->belongsToMany(Zone::class, 'product_zones');
    }

    /**
     * Relación con las porciones asociadas al producto.
     */
    /**
     * Relación con el modelo Portion.
     */
    public function portions()
    {
        return $this->hasMany(ProductPortion::class, 'product_id')->with('portion');
    }
}
