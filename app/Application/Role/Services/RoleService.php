<?php

declare(strict_types=1);

namespace App\Application\Role\Services;

use App\Application\Role\Commands\CreateRoleCommand;
use App\Application\Role\Commands\DeleteRoleCommand;
use App\Application\Role\Commands\SyncRolePermissionsCommand;
use App\Application\Role\Commands\UpdateRoleCommand;
use App\Application\Role\DTOs\PermissionDTO;
use App\Application\Role\DTOs\RoleDTO;
use App\Application\Role\Handlers\CheckRoleHasUsersHandler;
use App\Application\Role\Handlers\CreateRoleHandler;
use App\Application\Role\Handlers\DeleteRoleHandler;
use App\Application\Role\Handlers\GetAllRolesHandler;
use App\Application\Role\Handlers\GetDefaultPermissionsForRoleHandler;
use App\Application\Role\Handlers\GetGroupedPermissionsHandler;
use App\Application\Role\Handlers\GetPermissionsByGroupHandler;
use App\Application\Role\Handlers\GetRoleByIdHandler;
use App\Application\Role\Handlers\GetRolePermissionsHandler;
use App\Application\Role\Handlers\RoleExistsByNameHandler;
use App\Application\Role\Handlers\SyncRolePermissionsHandler;
use App\Application\Role\Handlers\UpdateRoleHandler;
use App\Application\Role\Queries\CheckRoleHasUsersQuery;
use App\Application\Role\Queries\GetAllRolesQuery;
use App\Application\Role\Queries\GetDefaultPermissionsForRoleQuery;
use App\Application\Role\Queries\GetGroupedPermissionsQuery;
use App\Application\Role\Queries\GetPermissionsByGroupQuery;
use App\Application\Role\Queries\GetRoleByIdQuery;
use App\Application\Role\Queries\GetRolePermissionsQuery;
use App\Application\Role\Queries\RoleExistsByNameQuery;
use Illuminate\Support\Collection;

final class RoleService
{
    public function __construct(
        private readonly CreateRoleHandler $createHandler,
        private readonly UpdateRoleHandler $updateHandler,
        private readonly DeleteRoleHandler $deleteHandler,
        private readonly SyncRolePermissionsHandler $syncPermissionsHandler,
        private readonly GetAllRolesHandler $getAllHandler,
        private readonly GetRoleByIdHandler $getByIdHandler,
        private readonly GetRolePermissionsHandler $getPermissionsHandler,
        private readonly GetGroupedPermissionsHandler $getGroupedPermissionsHandler,
        private readonly GetPermissionsByGroupHandler $getPermissionsByGroupHandler,
        private readonly GetDefaultPermissionsForRoleHandler $getDefaultPermissionsHandler,
        private readonly CheckRoleHasUsersHandler $checkHasUsersHandler,
        private readonly RoleExistsByNameHandler $existsByNameHandler,
    ) {}

    // ========================================
    // Commands
    // ========================================

    public function create(string $name, ?string $description, int $level): RoleDTO
    {
        $displayName = ucfirst(str_replace('_', ' ', $name));

        $command = new CreateRoleCommand(
            name: $name,
            displayName: $displayName,
            description: $description,
            level: $level,
        );

        return $this->createHandler->handle($command);
    }

    public function update(string $id, string $name, ?string $description, int $level): ?RoleDTO
    {
        $displayName = ucfirst(str_replace('_', ' ', $name));

        $command = new UpdateRoleCommand(
            id: $id,
            name: $name,
            displayName: $displayName,
            description: $description,
            level: $level,
        );

        return $this->updateHandler->handle($command);
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function delete(string $id): array
    {
        $command = new DeleteRoleCommand($id);

        return $this->deleteHandler->handle($command);
    }

    public function syncPermissions(string $roleId, array $permissionIds): bool
    {
        $command = new SyncRolePermissionsCommand($roleId, $permissionIds);

        return $this->syncPermissionsHandler->handle($command);
    }

    // ========================================
    // Queries
    // ========================================

    /**
     * @return Collection<int, RoleDTO>
     */
    public function getAll(string $orderBy = 'level', string $orderDirection = 'desc'): Collection
    {
        $query = new GetAllRolesQuery($orderBy, $orderDirection);

        return $this->getAllHandler->handle($query);
    }

    public function getById(string $id, bool $withPermissions = false): ?RoleDTO
    {
        $query = new GetRoleByIdQuery($id, $withPermissions);

        return $this->getByIdHandler->handle($query);
    }

    public function getFirst(): ?RoleDTO
    {
        return $this->getAll()->first();
    }

    /**
     * @return string[]
     */
    public function getRolePermissionIds(string $roleId): array
    {
        $query = new GetRolePermissionsQuery($roleId);

        return $this->getPermissionsHandler->handle($query);
    }

    /**
     * @return Collection<string, Collection<int, PermissionDTO>>
     */
    public function getGroupedPermissions(): Collection
    {
        $query = new GetGroupedPermissionsQuery();

        return $this->getGroupedPermissionsHandler->handle($query);
    }

    /**
     * @return Collection<int, PermissionDTO>
     */
    public function getPermissionsByGroup(string $group): Collection
    {
        $query = new GetPermissionsByGroupQuery($group);

        return $this->getPermissionsByGroupHandler->handle($query);
    }

    /**
     * @return string[]
     */
    public function getDefaultPermissionIds(string $roleName): array
    {
        $query = new GetDefaultPermissionsForRoleQuery($roleName);

        return $this->getDefaultPermissionsHandler->handle($query);
    }

    /**
     * @return array{hasUsers: bool, count: int}
     */
    public function checkHasUsers(string $roleId): array
    {
        $query = new CheckRoleHasUsersQuery($roleId);

        return $this->checkHasUsersHandler->handle($query);
    }

    public function existsByName(string $name, ?string $excludeId = null): bool
    {
        $query = new RoleExistsByNameQuery($name, $excludeId);

        return $this->existsByNameHandler->handle($query);
    }

    public function isAdminRole(?RoleDTO $role): bool
    {
        return $role?->name === 'admin';
    }
}
