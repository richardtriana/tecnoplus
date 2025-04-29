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

    protected $fillable = [
        'name',
        'printer'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    
    protected $appends = [
        'process'
    ];

    /**
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
     */
    public function numberingRanges()
    {
        return $this->belongsToMany(NumberingRange::class, 'box_numbering_range');
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
