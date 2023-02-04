<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        Client::create(['id' => 1, 'name' => 'Sin cliente', 'type_document' => 'CC', 'document' => '00000000', 'active' => 1, 'tax' => 1]);

        // Client::factory()
        //     ->count(10)
        //     ->create();
    }
}
