<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientTributeSeeder extends Seeder
{
    public function run()
    {
        DB::table('client_tributes')->insert([
            ['id' => 18, 'name' => 'IVA'],
            ['id' => 21, 'name' => 'No aplica *'],
        ]);
    }
}
