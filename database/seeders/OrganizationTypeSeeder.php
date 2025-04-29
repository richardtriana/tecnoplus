<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationTypeSeeder extends Seeder
{
    public function run()
    {
        DB::table('organization_types')->insert([
            ['id' => 1, 'name' => 'Persona Jurídica'],
            ['id' => 2, 'name' => 'Persona Natural'],
        ]);
    }
}
