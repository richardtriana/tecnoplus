<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        User::create(['id' => 1, 'name' => 'Administrador', 'email' => 'administrador@mail.com','username' => 'Administrador', 'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi']);
        User::create(['id' => 2, 'name' => 'Cajero', 'email' => 'cajero@mail.com', 'username' => 'Cajero', 'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi']);
        User::find(1)->assignRole('Admin');
        User::find(2)->assignRole('Atm');
        //User::factory(10)->create();
    }
}
