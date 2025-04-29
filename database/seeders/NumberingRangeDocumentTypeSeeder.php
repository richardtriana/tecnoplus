<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NumberingRangeDocumentType;

class NumberingRangeDocumentTypeSeeder extends Seeder
{
    public function run()
    {
        NumberingRangeDocumentType::create(['code' => '21', 'description' => 'Factura de Venta']);
        NumberingRangeDocumentType::create(['code' => '22', 'description' => 'Nota Crédito']);
        NumberingRangeDocumentType::create(['code' => '23', 'description' => 'Nota Débito']);
        NumberingRangeDocumentType::create(['code' => '24', 'description' => 'Documento Soporte']);
        NumberingRangeDocumentType::create(['code' => '25', 'description' => 'Nota de Ajuste Documento Soporte']);
        NumberingRangeDocumentType::create(['code' => '26', 'description' => 'Nómina']);
        NumberingRangeDocumentType::create(['code' => '27', 'description' => 'Nota de Ajuste Nómina']);
        NumberingRangeDocumentType::create(['code' => '28', 'description' => 'Nota de eliminación de nómina']);
        NumberingRangeDocumentType::create(['code' => '30', 'description' => 'Factura de talonario y de papel']);
    }
}
