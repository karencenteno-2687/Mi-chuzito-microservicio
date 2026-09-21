<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permisosCliente = [
            'perfil.ver', 'perfil.editar',
            'pedidos.crear', 'pedidos.ver',
        ];

        $permisosAdmin = [
            'usuarios.gestionar', 'roles.asignar',
            'inventario.gestionar', 'reportes.ver',
            'pedidos.gestionar',
        ];

        // Crear roles
        $admin      = Role::firstOrCreate(['name' => 'administrador']);
        $cliente    = Role::firstOrCreate(['name' => 'cliente']);
        $repartidor = Role::firstOrCreate(['name' => 'repartidor']);
        $vendedor   = Role::firstOrCreate(['name' => 'vendedor']);

        // Crear permisos
        foreach ($permisosCliente as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        foreach ($permisosAdmin as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // Asignar permisos a roles
        $cliente->givePermissionTo($permisosCliente);
        $admin->givePermissionTo(Permission::all());

        // Crear usuario administrador por defecto
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@sistema.com'],
            [
                'name'     => 'Administrador',
                'password' => bcrypt('Admin.1234'),
            ]
        );

        $adminUser->assignRole('administrador');
    }
}