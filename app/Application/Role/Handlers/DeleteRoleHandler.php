<?php

declare(strict_types=1);

namespace App\Application\Role\Handlers;

use App\Application\Role\Commands\DeleteRoleCommand;
use App\Infrastructure\Persistence\Eloquent\RoleModel;

final class DeleteRoleHandler
{
    /**
     * @return array{success: bool, message: string}
     */
    public function handle(DeleteRoleCommand $command): array
    {
        $role = RoleModel::find($command->id);

        if (! $role) {
            return [
                'success' => false,
                'message' => 'Rol no encontrado',
            ];
        }

        if ($role->name === 'admin') {
            return [
                'success' => false,
                'message' => 'No puedes eliminar el rol de administrador',
            ];
        }

        // Verificar usuarios asignados
        $usersCount = \App\Models\User::where('role_id', $role->id)->count();
        if ($usersCount > 0) {
            return [
                'success' => false,
                'message' => "No puedes eliminar este rol porque tiene {$usersCount} usuario(s) asignado(s)",
            ];
        }

        // Eliminar permisos y rol
        $role->permissions()->detach();
        $role->delete();

        return [
            'success' => true,
            'message' => 'Rol eliminado correctamente',
        ];
    }
}
