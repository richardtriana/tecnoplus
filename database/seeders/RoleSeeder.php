<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Crear o tomar los roles
        $admin = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'Admin']);
        $atm   = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'Atm']);

        // Helper para crear o actualizar permisos y asignarlos a roles
        $add = function(array $perm, array $roles) {
            $permission = Permission::updateOrCreate(
                ['guard_name' => $perm['guard_name'], 'name' => $perm['name']],
                ['description' => $perm['description'], 'component' => $perm['component']]
            );
            // Asigna el permiso a cada rol sin afectar otros permisos
            foreach ($roles as $role) {
                $role->givePermissionTo($permission);
            }
        };

        // Permisos de Categorías
        $add(['guard_name'=>'api','name'=>'category.index','description'=>'Listar','component'=>'Categorías'],        [$admin]);
        $add(['guard_name'=>'api','name'=>'category.store','description'=>'Crear','component'=>'Categorías'],        [$admin, $atm]);
        $add(['guard_name'=>'api','name'=>'category.update','description'=>'Editar','component'=>'Categorías'],       [$admin, $atm]);
        $add(['guard_name'=>'api','name'=>'category.delete','description'=>'Eliminar','component'=>'Categorías'],     [$admin]);
        $add(['guard_name'=>'api','name'=>'category.active','description'=>'Activar','component'=>'Categorías'],      [$admin, $atm]);

        // Permisos de IVA
        $add(['guard_name'=>'api','name'=>'tax.index','description'=>'Listar','component'=>'Iva'],                   [$admin, $atm]);
        $add(['guard_name'=>'api','name'=>'tax.store','description'=>'Crear','component'=>'Iva'],                   [$admin, $atm]);
        $add(['guard_name'=>'api','name'=>'tax.update','description'=>'Editar','component'=>'Iva'],                 [$admin]);
        $add(['guard_name'=>'api','name'=>'tax.delete','description'=>'Eliminar','component'=>'Iva'],               [$admin]);
        $add(['guard_name'=>'api','name'=>'tax.active','description'=>'Activar','component'=>'Iva'],                [$admin]);

        // Permisos de Productos
        $add(['guard_name'=>'api','name'=>'product.index','description'=>'Listar','component'=>'Productos'],        [$admin, $atm]);
        $add(['guard_name'=>'api','name'=>'product.store','description'=>'Crear','component'=>'Productos'],        [$admin, $atm]);
        $add(['guard_name'=>'api','name'=>'product.update','description'=>'Editar','component'=>'Productos'],       [$admin]);
        $add(['guard_name'=>'api','name'=>'product.delete','description'=>'Eliminar','component'=>'Productos'],     [$admin]);
        $add(['guard_name'=>'api','name'=>'product.active','description'=>'Activar','component'=>'Productos'],      [$admin]);

        // Permisos de Clientes
        $add(['guard_name'=>'api','name'=>'client.index','description'=>'Listar','component'=>'Clientes'],          [$admin]);
        $add(['guard_name'=>'api','name'=>'client.store','description'=>'Crear','component'=>'Clientes'],          [$admin]);
        $add(['guard_name'=>'api','name'=>'client.update','description'=>'Editar','component'=>'Clientes'],         [$admin]);
        $add(['guard_name'=>'api','name'=>'client.delete','description'=>'Eliminar','component'=>'Clientes'],       [$admin]);
        $add(['guard_name'=>'api','name'=>'client.active','description'=>'Activar','component'=>'Clientes'],        [$admin]);

        // Permisos de Proveedores
        $add(['guard_name'=>'api','name'=>'supplier.index','description'=>'Listar','component'=>'Proveedores'],     [$admin, $atm]);
        $add(['guard_name'=>'api','name'=>'supplier.store','description'=>'Crear','component'=>'Proveedores'],     [$admin, $atm]);
        $add(['guard_name'=>'api','name'=>'supplier.update','description'=>'Editar','component'=>'Proveedores'],    [$admin]);
        $add(['guard_name'=>'api','name'=>'supplier.delete','description'=>'Eliminar','component'=>'Proveedores'],  [$admin]);
        $add(['guard_name'=>'api','name'=>'supplier.active','description'=>'Activar','component'=>'Proveedores'],   [$admin]);

        // Permisos de Marcas
        $add(['guard_name'=>'api','name'=>'brand.index','description'=>'Listar','component'=>'Marcas'],            [$admin, $atm]);
        $add(['guard_name'=>'api','name'=>'brand.store','description'=>'Crear','component'=>'Marcas'],            [$admin, $atm]);
        $add(['guard_name'=>'api','name'=>'brand.update','description'=>'Editar','component'=>'Marcas'],           [$admin]);
        $add(['guard_name'=>'api','name'=>'brand.delete','description'=>'Eliminar','component'=>'Marcas'],         [$admin]);
        $add(['guard_name'=>'api','name'=>'brand.active','description'=>'Activar','component'=>'Marcas'],          [$admin]);

        // Permisos de Órdenes
        $add(['guard_name'=>'api','name'=>'order.index','description'=>'Listar','component'=>'Ordenes'],          [$admin, $atm]);
        $add(['guard_name'=>'api','name'=>'order.store','description'=>'Crear','component'=>'Ordenes'],          [$admin, $atm]);
        $add(['guard_name'=>'api','name'=>'order.update','description'=>'Editar','component'=>'Ordenes'],         [$admin]);
        $add(['guard_name'=>'api','name'=>'order.delete','description'=>'Eliminar','component'=>'Ordenes'],       [$admin]);
        $add(['guard_name'=>'api','name'=>'order.active','description'=>'Activar','component'=>'Ordenes'],        [$admin]);

        // Permisos de Usuarios
        $add(['guard_name'=>'api','name'=>'user.index','description'=>'Listar','component'=>'Usuarios'],          [$admin]);
        $add(['guard_name'=>'api','name'=>'user.store','description'=>'Crear','component'=>'Usuarios'],          [$admin]);
        $add(['guard_name'=>'api','name'=>'user.update','description'=>'Editar','component'=>'Usuarios'],         [$admin]);
        $add(['guard_name'=>'api','name'=>'user.delete','description'=>'Eliminar','component'=>'Usuarios'],       [$admin]);
        $add(['guard_name'=>'api','name'=>'user.active','description'=>'Activar','component'=>'Usuarios'],        [$admin]);

        // Permisos de Roles
        $add(['guard_name'=>'api','name'=>'rol.index','description'=>'Listar','component'=>'Roles'],             [$admin]);
        $add(['guard_name'=>'api','name'=>'rol.store','description'=>'Crear','component'=>'Roles'],             [$admin]);
        $add(['guard_name'=>'api','name'=>'rol.update','description'=>'Editar','component'=>'Roles'],            [$admin]);
        $add(['guard_name'=>'api','name'=>'rol.delete','description'=>'Eliminar','component'=>'Roles'],          [$admin]);
        $add(['guard_name'=>'api','name'=>'rol.active','description'=>'Activar','component'=>'Roles'],           [$admin]);

        // Permiso de Configuración
        $add(['guard_name'=>'api','name'=>'configuration','description'=>'Configuracion','component'=>'Configuracion'], [$admin]);

        // Permisos de Facturación
        $add(['guard_name'=>'api','name'=>'billing.index','description'=>'Listar','component'=>'Facturación'],    [$admin]);
        $add(['guard_name'=>'api','name'=>'billing.store','description'=>'Crear','component'=>'Facturación'],    [$admin]);
        $add(['guard_name'=>'api','name'=>'billing.update','description'=>'Editar','component'=>'Facturación'],  [$admin]);
        $add(['guard_name'=>'api','name'=>'billing.delete','description'=>'Eliminar','component'=>'Facturación'],[$admin]);
        $add(['guard_name'=>'api','name'=>'billing.active','description'=>'Activar','component'=>'Facturación'], [$admin]);

        // Permisos de Caja
        $add(['guard_name'=>'api','name'=>'box.index','description'=>'Listar','component'=>'Caja'],             [$admin]);
        $add(['guard_name'=>'api','name'=>'box.store','description'=>'Crear','component'=>'Caja'],             [$admin]);
        $add(['guard_name'=>'api','name'=>'box.update','description'=>'Editar','component'=>'Caja'],            [$admin]);
        $add(['guard_name'=>'api','name'=>'box.delete','description'=>'Eliminar','component'=>'Caja'],          [$admin]);
        $add(['guard_name'=>'api','name'=>'box.active','description'=>'Activar','component'=>'Caja'],           [$admin]);

        // Permiso de Arqueo de Caja
        $add(['guard_name'=>'api','name'=>'cash.reconciliation','description'=>'Arqueo de Caja','component'=>'Caja'], [$admin]);

        // Permisos de Créditos
        $add(['guard_name'=>'api','name'=>'credit.index','description'=>'Listar','component'=>'Créditos'],      [$admin]);
        $add(['guard_name'=>'api','name'=>'credit.store','description'=>'Crear','component'=>'Créditos'],      [$admin]);
        $add(['guard_name'=>'api','name'=>'credit.update','description'=>'Editar','component'=>'Créditos'],     [$admin]);
        $add(['guard_name'=>'api','name'=>'credit.delete','description'=>'Eliminar','component'=>'Créditos'],   [$admin]);
        $add(['guard_name'=>'api','name'=>'credit.active','description'=>'Activar','component'=>'Créditos'],    [$admin]);
    }
}
