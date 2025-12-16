<?php

declare(strict_types=1);

namespace App\Application\Role\Handlers;

use App\Application\Role\Queries\GetRolePermissionsQuery;
use App\Infrastructure\Persistence\Eloquent\RoleModel;

final class GetRolePermissionsHandler
{
    /**
     * @return string[]
     */
    public function handle(GetRolePermissionsQuery $query): array
    {
        $role = RoleModel::with('permissions')->find($query->roleId);

        if (! $role) {
            return [];
        }

        return $role->permissions->pluck('id')->toArray();
    }
}
