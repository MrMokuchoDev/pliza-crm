<?php

declare(strict_types=1);

namespace App\Application\Authorization\Services;

use App\Domain\User\ValueObjects\Permission;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use App\Models\User;

/**
 * Servicio de autorización para verificar permisos de acceso.
 */
class AuthorizationService
{
    /**
     * Verifica si el usuario puede ver todos los registros de una entidad.
     */
    public function canViewAll(User $user, string $entity): bool
    {
        return match ($entity) {
            'leads' => $user->hasPermission(Permission::LEADS_VIEW_ALL),
            'deals' => $user->hasPermission(Permission::DEALS_VIEW_ALL),
            'reports' => $user->hasPermission(Permission::REPORTS_VIEW_ALL),
            default => false,
        };
    }

    /**
     * Verifica si el usuario puede acceder a un lead específico.
     */
    public function canAccessLead(User $user, LeadModel|string $lead): bool
    {
        // Admin y Manager pueden ver todos
        if ($user->canViewAllLeads()) {
            return true;
        }

        // Vendedor solo puede ver los asignados a él
        $leadModel = $lead instanceof LeadModel ? $lead : LeadModel::find($lead);

        if (! $leadModel) {
            return false;
        }

        // Si el lead no está asignado, solo admin/manager pueden verlo
        if ($leadModel->assigned_to === null) {
            return false;
        }

        return $leadModel->assigned_to === $user->uuid;
    }

    /**
     * Verifica si el usuario puede acceder a un deal específico.
     */
    public function canAccessDeal(User $user, DealModel|string $deal): bool
    {
        // Admin y Manager pueden ver todos
        if ($user->canViewAllDeals()) {
            return true;
        }

        // Vendedor solo puede ver los asignados a él
        $dealModel = $deal instanceof DealModel ? $deal : DealModel::find($deal);

        if (! $dealModel) {
            return false;
        }

        // Si el deal no está asignado, solo admin/manager pueden verlo
        if ($dealModel->assigned_to === null) {
            return false;
        }

        return $dealModel->assigned_to === $user->uuid;
    }

    /**
     * Verifica si el usuario puede editar un lead específico.
     */
    public function canEditLead(User $user, LeadModel|string $lead): bool
    {
        // Puede editar todos
        if ($user->hasPermission(Permission::LEADS_UPDATE_ALL)) {
            return true;
        }

        // Puede editar los propios
        if ($user->hasPermission(Permission::LEADS_UPDATE_OWN)) {
            return $this->canAccessLead($user, $lead);
        }

        return false;
    }

    /**
     * Verifica si el usuario puede editar un deal específico.
     */
    public function canEditDeal(User $user, DealModel|string $deal): bool
    {
        // Puede editar todos
        if ($user->hasPermission(Permission::DEALS_UPDATE_ALL)) {
            return true;
        }

        // Puede editar los propios
        if ($user->hasPermission(Permission::DEALS_UPDATE_OWN)) {
            return $this->canAccessDeal($user, $deal);
        }

        return false;
    }

    /**
     * Verifica si el usuario puede eliminar un lead específico.
     */
    public function canDeleteLead(User $user, LeadModel|string $lead): bool
    {
        // Puede eliminar todos
        if ($user->hasPermission(Permission::LEADS_DELETE_ALL)) {
            return true;
        }

        // Puede eliminar los propios
        if ($user->hasPermission(Permission::LEADS_DELETE_OWN)) {
            return $this->canAccessLead($user, $lead);
        }

        return false;
    }

    /**
     * Verifica si el usuario puede eliminar un deal específico.
     */
    public function canDeleteDeal(User $user, DealModel|string $deal): bool
    {
        // Puede eliminar todos
        if ($user->hasPermission(Permission::DEALS_DELETE_ALL)) {
            return true;
        }

        // Puede eliminar los propios
        if ($user->hasPermission(Permission::DEALS_DELETE_OWN)) {
            return $this->canAccessDeal($user, $deal);
        }

        return false;
    }

    /**
     * Verifica si el usuario puede asignar leads.
     */
    public function canAssignLead(User $user): bool
    {
        return $user->hasPermission(Permission::LEADS_ASSIGN);
    }

    /**
     * Verifica si el usuario puede asignar deals.
     */
    public function canAssignDeal(User $user): bool
    {
        return $user->hasPermission(Permission::DEALS_ASSIGN);
    }

    /**
     * Obtiene el filtro de usuario para queries de leads.
     *
     * @return array{user_uuid: string|null, only_own: bool}
     */
    public function getLeadQueryFilter(User $user): array
    {
        if ($user->canViewAllLeads()) {
            return [
                'user_uuid' => null,
                'only_own' => false,
            ];
        }

        return [
            'user_uuid' => $user->uuid,
            'only_own' => true,
        ];
    }

    /**
     * Obtiene el filtro de usuario para queries de deals.
     *
     * @return array{user_uuid: string|null, only_own: bool}
     */
    public function getDealQueryFilter(User $user): array
    {
        if ($user->canViewAllDeals()) {
            return [
                'user_uuid' => null,
                'only_own' => false,
            ];
        }

        return [
            'user_uuid' => $user->uuid,
            'only_own' => true,
        ];
    }
}
