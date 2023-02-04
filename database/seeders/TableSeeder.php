<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::create(['guard_name' => 'api', 'name' => 'table.index', 'description' => 'Listar', 'component' => 'Mesas'])->syncRoles([1]);
        Permission::create(['guard_name' => 'api', 'name' => 'table.store', 'description' => 'Crear', 'component' => 'Mesas'])->syncRoles([1]);
        Permission::create(['guard_name' => 'api', 'name' => 'table.update', 'description' => 'Editar', 'component' => 'Mesas'])->syncRoles([1]);
        Permission::create(['guard_name' => 'api', 'name' => 'table.delete', 'description' => 'Eliminar', 'component' => 'Mesas'])->syncRoles([1]);
        Permission::create(['guard_name' => 'api', 'name' => 'table.active', 'description' => 'Activar', 'component' => 'Mesas'])->syncRoles([1]);
    }
}
