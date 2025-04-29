<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IdentityDocumentTypeSeeder extends Seeder
{
    public function run()
    {
        DB::table('identity_document_types')->insert([
            ['id' => 1, 'name' => 'Registro civil'],
            ['id' => 2, 'name' => 'Tarjeta de identidad'],
            ['id' => 3, 'name' => 'Cédula de ciudadanía'],
            ['id' => 4, 'name' => 'Tarjeta de extranjería'],
            ['id' => 5, 'name' => 'Cédula de extranjería'],
            ['id' => 6, 'name' => 'NIT'],
            ['id' => 7, 'name' => 'Pasaporte'],
            ['id' => 8, 'name' => 'Documento de identificación extranjero'],
            ['id' => 9, 'name' => 'PEP'],
            ['id' => 10, 'name' => 'NIT otro país'],
            ['id' => 11, 'name' => 'NUIP*'],
        ]);
    }
}
