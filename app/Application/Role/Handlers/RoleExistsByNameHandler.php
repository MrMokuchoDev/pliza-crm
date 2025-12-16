<?php

declare(strict_types=1);

namespace App\Application\Role\Handlers;

use App\Application\Role\Queries\RoleExistsByNameQuery;
use App\Infrastructure\Persistence\Eloquent\RoleModel;

final class RoleExistsByNameHandler
{
    public function handle(RoleExistsByNameQuery $query): bool
    {
        $queryBuilder = RoleModel::where('name', $query->name);

        if ($query->excludeId) {
            $queryBuilder->where('id', '!=', $query->excludeId);
        }

        return $queryBuilder->exists();
    }
}
