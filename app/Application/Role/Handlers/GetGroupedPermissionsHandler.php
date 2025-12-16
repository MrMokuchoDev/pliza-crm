<?php

declare(strict_types=1);

namespace App\Application\Role\Handlers;

use App\Application\Role\DTOs\PermissionDTO;
use App\Application\Role\Queries\GetGroupedPermissionsQuery;
use App\Infrastructure\Persistence\Eloquent\PermissionModel;
use Illuminate\Support\Collection;

final class GetGroupedPermissionsHandler
{
    /**
     * @return Collection<string, Collection<int, PermissionDTO>>
     */
    public function handle(GetGroupedPermissionsQuery $query): Collection
    {
        return PermissionModel::orderBy('group')
            ->orderBy('name')
            ->get()
            ->map(fn ($permission) => PermissionDTO::fromArray($permission->toArray()))
            ->groupBy('group');
    }
}
