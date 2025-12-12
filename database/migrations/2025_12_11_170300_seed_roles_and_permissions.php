<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Roles y permisos por defecto del sistema.
     * Se insertan directamente en la migración para garantizar
     * que existan después de una actualización.
     *
     * IMPORTANTE: Los nombres deben coincidir EXACTAMENTE con los valores
     * del Enum Permission en app/Domain/User/ValueObjects/Permission.php
     */
    public function up(): void
    {
        // Definir permisos: [name => [display_name, group, description]]
        // Estos DEBEN coincidir con el Enum Permission::cases()
        $permissions = [
            // Leads (Contactos)
            'leads.view_all' => ['Ver todos los contactos', 'leads', 'Ver todos los leads del sistema'],
            'leads.view_own' => ['Ver contactos propios', 'leads', 'Ver leads asignados al usuario'],
            'leads.create' => ['Crear contactos', 'leads', 'Crear nuevos leads'],
            'leads.update_all' => ['Editar todos los contactos', 'leads', 'Editar cualquier lead'],
            'leads.update_own' => ['Editar contactos propios', 'leads', 'Editar leads asignados al usuario'],
            'leads.delete_all' => ['Eliminar todos los contactos', 'leads', 'Eliminar cualquier lead'],
            'leads.delete_own' => ['Eliminar contactos propios', 'leads', 'Eliminar leads asignados al usuario'],
            'leads.assign' => ['Asignar contactos', 'leads', 'Asignar leads a otros usuarios'],

            // Deals (Negocios)
            'deals.view_all' => ['Ver todos los negocios', 'deals', 'Ver todos los negocios del sistema'],
            'deals.view_own' => ['Ver negocios propios', 'deals', 'Ver negocios asignados al usuario'],
            'deals.create' => ['Crear negocios', 'deals', 'Crear nuevos negocios'],
            'deals.update_all' => ['Editar todos los negocios', 'deals', 'Editar cualquier negocio'],
            'deals.update_own' => ['Editar negocios propios', 'deals', 'Editar negocios asignados al usuario'],
            'deals.delete_all' => ['Eliminar todos los negocios', 'deals', 'Eliminar cualquier negocio'],
            'deals.delete_own' => ['Eliminar negocios propios', 'deals', 'Eliminar negocios asignados al usuario'],
            'deals.assign' => ['Asignar negocios', 'deals', 'Asignar negocios a otros usuarios'],

            // Users (Usuarios)
            'users.view' => ['Ver usuarios', 'users', 'Ver lista de usuarios'],
            'users.create' => ['Crear usuarios', 'users', 'Crear nuevos usuarios'],
            'users.update' => ['Editar usuarios', 'users', 'Editar usuarios existentes'],
            'users.delete' => ['Eliminar usuarios', 'users', 'Eliminar usuarios'],
            'users.assign_role' => ['Asignar roles', 'users', 'Asignar roles a usuarios'],

            // Phases (Fases de venta)
            'phases.manage' => ['Gestionar fases de venta', 'phases', 'Crear, editar y eliminar fases de venta'],

            // Sites (Sitios web)
            'sites.manage' => ['Gestionar sitios web', 'sites', 'Crear, editar y eliminar sitios web'],

            // Reports (Reportes)
            'reports.view_all' => ['Ver todos los reportes', 'reports', 'Ver reportes de todo el sistema'],
            'reports.view_own' => ['Ver reportes propios', 'reports', 'Ver reportes de datos propios'],

            // System (Sistema)
            'system.maintenance' => ['Acceso a mantenimiento', 'system', 'Acceso al panel de mantenimiento'],
            'system.updates' => ['Gestionar actualizaciones', 'system', 'Gestionar actualizaciones del sistema'],
        ];

        // Insertar permisos si no existen
        foreach ($permissions as $name => $data) {
            $exists = DB::table('permissions')->where('name', $name)->exists();
            if (!$exists) {
                DB::table('permissions')->insert([
                    'id' => Str::uuid()->toString(),
                    'name' => $name,
                    'display_name' => $data[0],
                    'group' => $data[1],
                    'description' => $data[2],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Definir roles: [name => [display_name, description, level]]
        // IMPORTANTE: Usar 'sales' para coincidir con el Enum Role::SALES
        $roles = [
            'admin' => ['Administrador', 'Acceso total al sistema', 100],
            'manager' => ['Gerente', 'Acceso a todos los datos sin configuración del sistema', 50],
            'sales' => ['Vendedor', 'Acceso solo a sus propios leads y negocios', 10],
        ];

        $roleIds = [];
        foreach ($roles as $name => $data) {
            $existing = DB::table('roles')->where('name', $name)->first();
            if ($existing) {
                $roleIds[$name] = $existing->id;
            } else {
                $id = Str::uuid()->toString();
                DB::table('roles')->insert([
                    'id' => $id,
                    'name' => $name,
                    'display_name' => $data[0],
                    'description' => $data[1],
                    'level' => $data[2],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $roleIds[$name] = $id;
            }
        }

        // Definir permisos por rol (debe coincidir con Permission::forRole())
        $rolePermissions = [
            'admin' => array_keys($permissions), // Todos los permisos

            'manager' => [
                'leads.view_all', 'leads.view_own', 'leads.create', 'leads.update_all', 'leads.delete_all', 'leads.assign',
                'deals.view_all', 'deals.view_own', 'deals.create', 'deals.update_all', 'deals.delete_all', 'deals.assign',
                'phases.manage',
                'reports.view_all',
            ],

            'sales' => [
                'leads.view_own', 'leads.create', 'leads.update_own', 'leads.delete_own',
                'deals.view_own', 'deals.create', 'deals.update_own', 'deals.delete_own',
                'reports.view_own',
            ],
        ];

        // Asignar permisos a roles
        foreach ($rolePermissions as $roleName => $permissionNames) {
            $roleId = $roleIds[$roleName] ?? null;
            if (!$roleId) {
                continue;
            }

            foreach ($permissionNames as $permName) {
                $permission = DB::table('permissions')->where('name', $permName)->first();
                if ($permission) {
                    $exists = DB::table('role_permission')
                        ->where('role_id', $roleId)
                        ->where('permission_id', $permission->id)
                        ->exists();

                    if (!$exists) {
                        DB::table('role_permission')->insert([
                            'role_id' => $roleId,
                            'permission_id' => $permission->id,
                        ]);
                    }
                }
            }
        }

        // Asignar rol admin al primer usuario que no tenga rol
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        if ($adminRole) {
            $usersWithoutRole = DB::table('users')
                ->whereNull('role_id')
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($usersWithoutRole as $index => $user) {
                // El primer usuario sin rol será admin, los demás sales
                $assignRole = $index === 0 ? $adminRole->id : $roleIds['sales'];
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['role_id' => $assignRole]);
            }
        }
    }

    /**
     * No se hace rollback de los datos sembrados.
     */
    public function down(): void
    {
        // Los datos se mantienen, solo las tablas se eliminan en sus respectivas migraciones
    }
};
