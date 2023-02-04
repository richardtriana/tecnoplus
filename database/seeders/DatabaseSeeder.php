<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(RoleSeeder::class);
        // $this->call(ConfigurationSeeder::class);
        // // $this->call(CategorySeeder::class);
        // // $this->call(TaxSeeder::class);
        // // $this->call(BrandSeeder::class);
        // $this->call(SupplierSeeder::class);
        // $this->call(ClientSeeder::class);
        // $this->call(UserSeeder::class);
        // // $this->call(ProductTableSeeder::class);
        // // $this->call(OrderSeeder::class);
        // $this->call(ZoneSeeder::class);
        $this->call(TableSeeder::class);
    }
}
