<?php

declare(strict_types=1);

namespace App\Application\Role\Commands;

final readonly class SyncRolePermissionsCommand
{
    /**
     * @param string[] $permissionIds
     */
    public function __construct(
        public string $roleId,
        public array $permissionIds,
    ) {}
}
