<?php

declare(strict_types=1);

namespace App\Application\Role\Handlers;

use App\Application\Role\Commands\CreateRoleCommand;
use App\Application\Role\DTOs\RoleDTO;
use App\Infrastructure\Persistence\Eloquent\RoleModel;

final class CreateRoleHandler
{
    public function handle(CreateRoleCommand $command): RoleDTO
    {
        $role = RoleModel::create([
            'name' => $command->name,
            'display_name' => $command->displayName,
            'description' => $command->description,
            'level' => $command->level,
        ]);

        return RoleDTO::fromArray($role->toArray());
    }
}
