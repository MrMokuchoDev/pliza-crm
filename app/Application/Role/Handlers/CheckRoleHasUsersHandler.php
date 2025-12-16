<?php

declare(strict_types=1);

namespace App\Application\Role\Handlers;

use App\Application\Role\Queries\CheckRoleHasUsersQuery;
use App\Models\User;

final class CheckRoleHasUsersHandler
{
    /**
     * @return array{hasUsers: bool, count: int}
     */
    public function handle(CheckRoleHasUsersQuery $query): array
    {
        $count = User::where('role_id', $query->roleId)->count();

        return [
            'hasUsers' => $count > 0,
            'count' => $count,
        ];
    }
}
