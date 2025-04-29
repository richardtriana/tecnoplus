<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdjustmentNoteReasonSeeder extends Seeder
{
    public function run()
    {
        DB::table('adjustment_note_reasons')->insert([
            ['code' => '1', 'description' => 'Devolución parcial de los bienes y/o no aceptación parcial del servicio'],
            ['code' => '2', 'description' => 'Anulación del documento soporte en adquisiciones efectuadas a sujetos no obligados a expedir factura de venta o documento equivalente'],
            ['code' => '3', 'description' => 'Rebaja o descuento parcial o total'],
            ['code' => '4', 'description' => 'Ajuste de precio'],
            ['code' => '5', 'description' => 'Otros'],
        ]);
    }
}
