<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductIdentificationStandardSeeder extends Seeder
{
    public function run()
    {
        DB::table('product_identification_standards')->insert([
            ['id' => 1, 'name' => 'Estándar de adopción del contribuyente'],
            ['id' => 2, 'name' => 'UNSPSC'],
            ['id' => 3, 'name' => 'Partida Arancelaria'],
            ['id' => 4, 'name' => 'GTIN']
        ]);
    }
}
