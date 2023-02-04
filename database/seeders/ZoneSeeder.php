<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;


class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::create(['guard_name' => 'api', 'name' => 'zone.index', 'description' => 'Listar', 'component' => 'Zonas'])->syncRoles([1]);
        Permission::create(['guard_name' => 'api', 'name' => 'zone.store', 'description' => 'Crear', 'component' => 'Zonas'])->syncRoles([1]);
        Permission::create(['guard_name' => 'api', 'name' => 'zone.update', 'description' => 'Editar', 'component' => 'Zonas'])->syncRoles([1]);
        Permission::create(['guard_name' => 'api', 'name' => 'zone.delete', 'description' => 'Eliminar', 'component' => 'Zonas'])->syncRoles([1]);
        Permission::create(['guard_name' => 'api', 'name' => 'zone.active', 'description' => 'Activar', 'component' => 'Zonas'])->syncRoles([1]);
    }
}
