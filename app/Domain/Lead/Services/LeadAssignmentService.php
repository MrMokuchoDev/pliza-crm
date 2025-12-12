<?php

declare(strict_types=1);

namespace App\Domain\Lead\Services;

use App\Infrastructure\Persistence\Eloquent\SiteModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de dominio para asignar leads automáticamente.
 *
 * Implementa dos estrategias de asignación:
 * 1. Usuario por defecto del sitio (si está configurado)
 * 2. Round Robin por sitio (distribución equitativa)
 */
class LeadAssignmentService
{
    /**
     * Obtiene el usuario al que se debe asignar un nuevo lead para un sitio.
     *
     * @param  string  $siteId  ID del sitio de origen
     * @return string|null UUID del usuario asignado, null si no hay usuarios disponibles
     */
    public function getAssignedUserForSite(string $siteId): ?string
    {
        $site = SiteModel::find($siteId);

        if (!$site) {
            return null;
        }

        // Estrategia 1: Usuario por defecto del sitio
        if ($site->hasDefaultUser()) {
            // Verificar que el usuario siga activo
            $defaultUser = $site->defaultUser;
            if ($defaultUser && $defaultUser->is_active) {
                return $site->default_user_id;
            }
            // Si el usuario por defecto ya no está activo, usar round robin
        }

        // Estrategia 2: Round Robin
        return $this->getNextUserByRoundRobin($site);
    }

    /**
     * Obtiene el siguiente usuario usando Round Robin.
     *
     * El índice se mantiene por cada sitio para garantizar
     * distribución equitativa independiente por sitio.
     */
    private function getNextUserByRoundRobin(SiteModel $site): ?string
    {
        // Obtener usuarios activos ordenados por ID para consistencia
        $activeUsers = User::where('is_active', true)
            ->orderBy('id')
            ->pluck('uuid')
            ->toArray();

        if (empty($activeUsers)) {
            return null;
        }

        $totalUsers = count($activeUsers);

        // Usar transacción con lock para evitar race conditions
        return DB::transaction(function () use ($site, $activeUsers, $totalUsers) {
            // Refrescar el modelo con lock
            $site = SiteModel::lockForUpdate()->find($site->id);

            // Calcular el índice actual (circular)
            $currentIndex = $site->round_robin_index % $totalUsers;

            // Obtener el usuario correspondiente
            $assignedUserUuid = $activeUsers[$currentIndex];

            // Incrementar el índice para la próxima asignación
            $site->round_robin_index = $site->round_robin_index + 1;
            $site->save();

            return $assignedUserUuid;
        });
    }

    /**
     * Resetea el índice de round robin para un sitio.
     * Útil cuando se quiere reiniciar la distribución.
     */
    public function resetRoundRobinIndex(string $siteId): bool
    {
        $site = SiteModel::find($siteId);

        if (!$site) {
            return false;
        }

        $site->round_robin_index = 0;
        $site->save();

        return true;
    }

    /**
     * Obtiene estadísticas de asignación para un sitio.
     */
    public function getAssignmentStats(string $siteId): array
    {
        $site = SiteModel::with('defaultUser')->find($siteId);

        if (!$site) {
            return [];
        }

        $activeUsersCount = User::where('is_active', true)->count();

        return [
            'site_id' => $siteId,
            'site_name' => $site->name,
            'assignment_mode' => $site->hasDefaultUser() ? 'default_user' : 'round_robin',
            'default_user' => $site->defaultUser ? [
                'uuid' => $site->default_user_id,
                'name' => $site->defaultUser->name,
                'is_active' => $site->defaultUser->is_active,
            ] : null,
            'round_robin_index' => $site->round_robin_index,
            'active_users_count' => $activeUsersCount,
            'next_round_robin_position' => $activeUsersCount > 0
                ? ($site->round_robin_index % $activeUsersCount) + 1
                : 0,
        ];
    }
}
