<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\NumberingRange;
use App\Models\Order;
use Carbon\Carbon;

class Box extends Model
{
    use HasFactory;

    /**
     * Los atributos que pueden asignarse masivamente.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
<<<<<<< HEAD
        'printer'
=======
        'printer',
        'active',
        'is_open',
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    ];

    /**
     * Los atributos que se ocultan en los arrays/JSON.
     *
     * @var array<int,string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Los atributos que se castean automáticamente.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'active'  => 'boolean',
        'is_open' => 'boolean',
    ];

    /**
     * Atributos virtuales que se añaden al JSON.
     *
     * @var array<int,string>
     */
    protected $appends = [
        'process',
    ];

    /**
<<<<<<< HEAD
     * Atributo virtual que indica si la caja tiene alguna orden (basado en los números de factura generados).
     */
    public function getProcessAttribute()
    {
        $hasOrder = false;
        // Recorremos los rangos de numeración asociados a la caja
        foreach ($this->numberingRanges as $range) {
            // Construimos el bill_number usando el prefijo y el valor "from" (puedes modificar esta lógica)
            $bill_number = $range->prefix . $range->from;
            // Verificamos si existe alguna orden con ese bill_number y con esta caja
            $ordersCount = Order::where('bill_number', $bill_number)
                ->where('box_id', $this->id)
                ->count();
            if ($ordersCount > 0) {
                $hasOrder = true;
                break;
            }
        }
        return $hasOrder;
    }

    /**
     * Relación muchos a muchos con los rangos de numeración.
     * Se usa la tabla intermedia "box_numbering_range".
=======
     * Atributo virtual que indica si la caja tiene alguna orden
     * (basado en los números de factura generados).
     *
     * @return bool
     */
    public function getProcessAttribute()
    {
        foreach ($this->numberingRanges as $range) {
            // Construimos el bill_number usando el prefijo y el valor "from"
            $bill_number = $range->prefix . $range->from;
            // Verificamos si existe alguna orden con ese bill_number y con esta caja
            $exists = Order::where('bill_number', $bill_number)
                ->where('box_id', $this->id)
                ->exists();
            if ($exists) {
                return true;
            }
        }
        return false;
    }

    /**
     * Scope para filtrar sólo cajas activas.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Relación muchos a muchos con los rangos de numeración.
     * Se usa la tabla intermedia "box_numbering_range".
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
     */
    public function numberingRanges()
    {
        return $this->belongsToMany(NumberingRange::class, 'box_numbering_range');
<<<<<<< HEAD
=======
    }

    /**
     * Método para obtener un rango de numeración para la caja.
     * Por defecto se retorna el primer rango asociado.
     *
     * @return \App\Models\NumberingRange|null
     */
    public function consecutiveBox()
    {
        return $this->numberingRanges()->first();
    }

    /**
     * Relación uno a muchos con BoxUser.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function boxUsers()
    {
        return $this->hasMany(BoxUser::class);
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    }

    /**
     * Método para obtener un rango de numeración para la caja.
     * Por defecto se retorna el primer rango asociado.
     *
     * Si necesitas lógica más compleja (por ejemplo, filtrar por fecha o por comprobante
     * seleccionado) deberás ajustar este método.
     */
    public function consecutiveBox()
    {
        return $this->numberingRanges()->first();
    }

    /**
     * Relación uno a muchos con BoxUser.
     */
    public function boxUsers()
    {
        return $this->hasMany(BoxUser::class);
    }

    


}
