<?php

declare(strict_types=1);

namespace App\Application\Role\Handlers;

use App\Application\Role\DTOs\RoleDTO;
use App\Application\Role\Queries\GetRoleByIdQuery;
use App\Infrastructure\Persistence\Eloquent\RoleModel;

final class GetRoleByIdHandler
{
    public function handle(GetRoleByIdQuery $query): ?RoleDTO
    {
        $roleQuery = RoleModel::query();

        if ($query->withPermissions) {
            $roleQuery->with('permissions');
        }

        $role = $roleQuery->find($query->id);

        if (! $role) {
            return null;
        }

        $data = $role->toArray();

        if ($query->withPermissions) {
            $data['permission_ids'] = $role->permissions->pluck('id')->toArray();
        }

        return RoleDTO::fromArray($data);
    }
}
