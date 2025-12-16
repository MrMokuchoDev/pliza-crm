<?php

declare(strict_types=1);

namespace App\Application\Role\Handlers;

use App\Application\Role\Queries\GetDefaultPermissionsForRoleQuery;
use App\Infrastructure\Persistence\Eloquent\PermissionModel;

final class GetDefaultPermissionsForRoleHandler
{
    /**
     * @return string[]
     */
    public function handle(GetDefaultPermissionsForRoleQuery $query): array
    {
        $permissionNames = $this->getDefaultPermissionNames($query->roleName);

        if (empty($permissionNames)) {
            return [];
        }

        return PermissionModel::whereIn('name', $permissionNames)
            ->pluck('id')
            ->toArray();
    }

    /**
     * @return string[]
     */
    private function getDefaultPermissionNames(string $roleName): array
    {
        return match ($roleName) {
            'admin' => PermissionModel::pluck('name')->toArray(),

            'manager' => [
                'leads.view_all',
                'leads.view_own',
                'leads.create',
                'leads.update_all',
                'leads.delete_all',
                'leads.assign',
                'deals.view_all',
                'deals.view_own',
                'deals.create',
                'deals.update_all',
                'deals.delete_all',
                'deals.assign',
                'phases.manage',
                'reports.view_all',
            ],

            'sales' => [
                'leads.view_own',
                'leads.create',
                'leads.update_own',
                'leads.delete_own',
                'deals.view_own',
                'deals.create',
                'deals.update_own',
                'deals.delete_own',
                'reports.view_own',
            ],

            default => [],
        };
    }
}
