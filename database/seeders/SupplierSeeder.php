<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Supplier::create(['id' => 1, 'name' => 'Sin proveedor', 'type_document' => 1, 'document' => '00000000', 'active' => 1, 'tax' => 1]);
        // Supplier::factory()
        // ->count(10)
        // ->create();
    }
}
