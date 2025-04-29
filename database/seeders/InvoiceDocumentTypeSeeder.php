<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvoiceDocumentType;

class InvoiceDocumentTypeSeeder extends Seeder
{
    public function run()
    {
        InvoiceDocumentType::create([
            'code' => '01',
            'description' => 'Factura electrónica de venta.'
        ]);

        InvoiceDocumentType::create([
            'code' => '03',
            'description' => 'Instrumento electrónico de transmisión - tipo 03.'
        ]);
    }
}
