<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\User\ValueObjects\Permission;
use App\Domain\User\ValueObjects\Role;
use App\Infrastructure\Persistence\Eloquent\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\RoleModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createPermissions();
        $this->createRoles();
        $this->assignPermissionsToRoles();
    }

    /**
     * Crea todos los permisos del sistema.
     */
    private function createPermissions(): void
    {
        foreach (Permission::cases() as $permission) {
            PermissionModel::updateOrCreate(
                ['name' => $permission->value],
                [
                    'display_name' => $permission->label(),
                    'group' => $permission->groupCode(),
                    'description' => null,
                ]
            );
        }

        $this->command->info('Permisos creados: '.count(Permission::cases()));
    }

    /**
     * Crea los roles del sistema.
     */
    private function createRoles(): void
    {
        foreach (Role::cases() as $role) {
            RoleModel::updateOrCreate(
                ['name' => $role->value],
                [
                    'display_name' => $role->label(),
                    'description' => $role->description(),
                    'level' => $role->level(),
                ]
            );
        }

        $this->command->info('Roles creados: '.count(Role::cases()));
    }

    /**
     * Asigna los permisos predeterminados a cada rol.
     */
    private function assignPermissionsToRoles(): void
    {
        foreach (Role::cases() as $role) {
            $roleModel = RoleModel::where('name', $role->value)->first();

            if (! $roleModel) {
                continue;
            }

            $permissionNames = array_map(
                fn (Permission $p) => $p->value,
                $role->defaultPermissions()
            );

            $permissionIds = PermissionModel::whereIn('name', $permissionNames)
                ->pluck('id')
                ->toArray();

            $roleModel->permissions()->sync($permissionIds);

            $this->command->info("Rol '{$role->label()}': ".count($permissionIds).' permisos asignados');
        }
    }
}
