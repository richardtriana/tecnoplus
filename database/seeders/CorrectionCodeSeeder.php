<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CorrectionCode;

class CorrectionCodeSeeder extends Seeder
{
    public function run()
    {
        CorrectionCode::create(['code' => '1', 'description' => 'Devolución parcial de los bienes y/o no aceptación parcial del servicio.']);
        CorrectionCode::create(['code' => '2', 'description' => 'Anulación de factura electrónica.']);
        CorrectionCode::create(['code' => '3', 'description' => 'Rebaja o descuento parcial o total.']);
        CorrectionCode::create(['code' => '4', 'description' => 'Ajuste de precio.']);
        CorrectionCode::create(['code' => '5', 'description' => 'Descuento comercial por pronto pago.']);
        CorrectionCode::create(['code' => '6', 'description' => 'Descuento comercial por volumen de ventas.']);
    }
}
