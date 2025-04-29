<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EventCode;

class EventCodeSeeder extends Seeder
{
    public function run()
    {
        EventCode::create(['code' => '030', 'name' => 'Acuse de recibo de Factura Electrónica de Venta']);
        EventCode::create(['code' => '031', 'name' => 'Reclamo de la Factura Electrónica de Venta']);
        EventCode::create(['code' => '032', 'name' => 'Recibo del bien y/o prestación del servicio']);
        EventCode::create(['code' => '033', 'name' => 'Aceptación expresa']);
        EventCode::create(['code' => '034', 'name' => 'Aceptación tácita']);
    }
}
