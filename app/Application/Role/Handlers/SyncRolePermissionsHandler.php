<?php

declare(strict_types=1);

namespace App\Application\Role\Handlers;

use App\Application\Role\Commands\SyncRolePermissionsCommand;
use App\Infrastructure\Persistence\Eloquent\RoleModel;

final class SyncRolePermissionsHandler
{
    public function handle(SyncRolePermissionsCommand $command): bool
    {
        $role = RoleModel::find($command->roleId);

        if (! $role) {
            return false;
        }

        $role->permissions()->sync($command->permissionIds);

        return true;
    }
}
