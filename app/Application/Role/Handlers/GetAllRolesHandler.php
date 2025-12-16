<?php

declare(strict_types=1);

namespace App\Application\Role\Handlers;

use App\Application\Role\DTOs\RoleDTO;
use App\Application\Role\Queries\GetAllRolesQuery;
use App\Infrastructure\Persistence\Eloquent\RoleModel;
use Illuminate\Support\Collection;

final class GetAllRolesHandler
{
    /**
     * @return Collection<int, RoleDTO>
     */
    public function handle(GetAllRolesQuery $query): Collection
    {
        return RoleModel::orderBy($query->orderBy, $query->orderDirection)
            ->get()
            ->map(fn ($role) => RoleDTO::fromArray($role->toArray()));
    }
}
