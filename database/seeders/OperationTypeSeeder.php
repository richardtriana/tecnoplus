<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OperationType;

class OperationTypeSeeder extends Seeder
{
    public function run()
    {
        OperationType::create(['code' => '20', 'description' => 'Nota Crédito que referencia una factura electrónica.']);
        OperationType::create(['code' => '22', 'description' => 'Nota Crédito sin referencia a una factura electrónica.']);
    }
}
