<?php

declare(strict_types=1);

namespace App\Application\Role\Handlers;

use App\Application\Role\DTOs\PermissionDTO;
use App\Application\Role\Queries\GetPermissionsByGroupQuery;
use App\Infrastructure\Persistence\Eloquent\PermissionModel;
use Illuminate\Support\Collection;

final class GetPermissionsByGroupHandler
{
    /**
     * @return Collection<int, PermissionDTO>
     */
    public function handle(GetPermissionsByGroupQuery $query): Collection
    {
        return PermissionModel::where('group', $query->group)
            ->orderBy('name')
            ->get()
            ->map(fn ($permission) => PermissionDTO::fromArray($permission->toArray()));
    }
}
