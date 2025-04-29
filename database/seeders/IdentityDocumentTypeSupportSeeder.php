<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IdentityDocumentTypeSupportSeeder extends Seeder
{
    public function run()
    {
        DB::table('identity_document_type_supports')->insert([
            ['id' => 4, 'name' => 'Tarjeta de extranjería'],
            ['id' => 5, 'name' => 'Cédula de extranjería'],
            ['id' => 6, 'name' => 'NIT'],
            ['id' => 7, 'name' => 'Pasaporte'],
            ['id' => 8, 'name' => 'Documento de identificación extranjero'],
            ['id' => 9, 'name' => 'PEP'],
            ['id' => 10, 'name' => 'NIT otro país'],
        ]);
    }
}
