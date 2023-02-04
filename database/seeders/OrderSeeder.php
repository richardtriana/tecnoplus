<?php

namespace Database\Seeders;

use App\Models\DetailOrder;
use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Order::factory()
            ->has(DetailOrder::factory()->count(3))
            ->count(5)
            ->create();
    }
}
