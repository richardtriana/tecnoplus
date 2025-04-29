<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClaimConceptCode;

class ClaimConceptCodeSeeder extends Seeder
{
    public function run()
    {
        ClaimConceptCode::create(['code' => '1', 'name' => 'Documento con inconsistencias']);
        ClaimConceptCode::create(['code' => '2', 'name' => 'Mercancía no entregada']);
        ClaimConceptCode::create(['code' => '3', 'name' => 'Mercancía entregada parcialmente']);
        ClaimConceptCode::create(['code' => '4', 'name' => 'Servicio no prestado']);
    }
}
