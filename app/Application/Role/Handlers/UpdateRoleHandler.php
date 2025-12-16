<?php

declare(strict_types=1);

namespace App\Application\Role\Handlers;

use App\Application\Role\Commands\UpdateRoleCommand;
use App\Application\Role\DTOs\RoleDTO;
use App\Infrastructure\Persistence\Eloquent\RoleModel;

final class UpdateRoleHandler
{
    public function handle(UpdateRoleCommand $command): ?RoleDTO
    {
        $role = RoleModel::find($command->id);

        if (! $role) {
            return null;
        }

        // No permitir cambiar nivel del admin
        $level = $role->name === 'admin' ? 100 : $command->level;

        $role->update([
            'name' => $command->name,
            'display_name' => $command->displayName,
            'description' => $command->description,
            'level' => $level,
        ]);

        return RoleDTO::fromArray($role->fresh()->toArray());
    }
}
