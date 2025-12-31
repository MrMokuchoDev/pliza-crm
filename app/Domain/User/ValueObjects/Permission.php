<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

/**
 * Enum que representa los permisos disponibles en el sistema.
 */
enum Permission: string
{
    // Contactos (Leads)
    case LEADS_VIEW_ALL = 'leads.view_all';
    case LEADS_VIEW_OWN = 'leads.view_own';
    case LEADS_CREATE = 'leads.create';
    case LEADS_UPDATE_ALL = 'leads.update_all';
    case LEADS_UPDATE_OWN = 'leads.update_own';
    case LEADS_DELETE_ALL = 'leads.delete_all';
    case LEADS_DELETE_OWN = 'leads.delete_own';
    case LEADS_ASSIGN = 'leads.assign';

    // Negocios (Deals)
    case DEALS_VIEW_ALL = 'deals.view_all';
    case DEALS_VIEW_OWN = 'deals.view_own';
    case DEALS_CREATE = 'deals.create';
    case DEALS_UPDATE_ALL = 'deals.update_all';
    case DEALS_UPDATE_OWN = 'deals.update_own';
    case DEALS_DELETE_ALL = 'deals.delete_all';
    case DEALS_DELETE_OWN = 'deals.delete_own';
    case DEALS_ASSIGN = 'deals.assign';

    // Usuarios
    case USERS_VIEW = 'users.view';
    case USERS_CREATE = 'users.create';
    case USERS_UPDATE = 'users.update';
    case USERS_DELETE = 'users.delete';
    case USERS_ASSIGN_ROLE = 'users.assign_role';

    // Fases de venta
    case PHASES_MANAGE = 'phases.manage';

    // Sitios
    case SITES_MANAGE = 'sites.manage';

    // Reportes
    case REPORTS_VIEW_ALL = 'reports.view_all';
    case REPORTS_VIEW_OWN = 'reports.view_own';

    // Campos Personalizados
    case CUSTOM_FIELDS_VIEW = 'custom_fields.view';
    case CUSTOM_FIELDS_CREATE = 'custom_fields.create';
    case CUSTOM_FIELDS_UPDATE = 'custom_fields.update';
    case CUSTOM_FIELDS_DELETE = 'custom_fields.delete';

    // Sistema
    case SYSTEM_MAINTENANCE = 'system.maintenance';
    case SYSTEM_UPDATES = 'system.updates';

    /**
     * Obtiene el nombre para mostrar del permiso.
     */
    public function label(): string
    {
        return match ($this) {
            // Leads
            self::LEADS_VIEW_ALL => 'Ver todos los contactos',
            self::LEADS_VIEW_OWN => 'Ver contactos propios',
            self::LEADS_CREATE => 'Crear contactos',
            self::LEADS_UPDATE_ALL => 'Editar todos los contactos',
            self::LEADS_UPDATE_OWN => 'Editar contactos propios',
            self::LEADS_DELETE_ALL => 'Eliminar todos los contactos',
            self::LEADS_DELETE_OWN => 'Eliminar contactos propios',
            self::LEADS_ASSIGN => 'Asignar contactos',

            // Deals
            self::DEALS_VIEW_ALL => 'Ver todos los negocios',
            self::DEALS_VIEW_OWN => 'Ver negocios propios',
            self::DEALS_CREATE => 'Crear negocios',
            self::DEALS_UPDATE_ALL => 'Editar todos los negocios',
            self::DEALS_UPDATE_OWN => 'Editar negocios propios',
            self::DEALS_DELETE_ALL => 'Eliminar todos los negocios',
            self::DEALS_DELETE_OWN => 'Eliminar negocios propios',
            self::DEALS_ASSIGN => 'Asignar negocios',

            // Users
            self::USERS_VIEW => 'Ver usuarios',
            self::USERS_CREATE => 'Crear usuarios',
            self::USERS_UPDATE => 'Editar usuarios',
            self::USERS_DELETE => 'Eliminar usuarios',
            self::USERS_ASSIGN_ROLE => 'Asignar roles',

            // Phases
            self::PHASES_MANAGE => 'Gestionar fases de venta',

            // Sites
            self::SITES_MANAGE => 'Gestionar sitios web',

            // Reports
            self::REPORTS_VIEW_ALL => 'Ver todos los reportes',
            self::REPORTS_VIEW_OWN => 'Ver reportes propios',

            // Custom Fields
            self::CUSTOM_FIELDS_VIEW => 'Ver campos personalizados',
            self::CUSTOM_FIELDS_CREATE => 'Crear campos personalizados',
            self::CUSTOM_FIELDS_UPDATE => 'Editar campos personalizados',
            self::CUSTOM_FIELDS_DELETE => 'Eliminar campos personalizados',

            // System
            self::SYSTEM_MAINTENANCE => 'Acceso a mantenimiento',
            self::SYSTEM_UPDATES => 'Gestionar actualizaciones',
        };
    }

    /**
     * Obtiene el grupo al que pertenece el permiso.
     */
    public function group(): string
    {
        return match (true) {
            str_starts_with($this->value, 'leads.') => 'Contactos',
            str_starts_with($this->value, 'deals.') => 'Negocios',
            str_starts_with($this->value, 'users.') => 'Usuarios',
            str_starts_with($this->value, 'phases.') => 'Fases',
            str_starts_with($this->value, 'sites.') => 'Sitios',
            str_starts_with($this->value, 'reports.') => 'Reportes',
            str_starts_with($this->value, 'custom_fields.') => 'Campos Personalizados',
            str_starts_with($this->value, 'system.') => 'Sistema',
            default => 'Otros',
        };
    }

    /**
     * Obtiene el código del grupo.
     */
    public function groupCode(): string
    {
        return match (true) {
            str_starts_with($this->value, 'leads.') => 'leads',
            str_starts_with($this->value, 'deals.') => 'deals',
            str_starts_with($this->value, 'users.') => 'users',
            str_starts_with($this->value, 'phases.') => 'phases',
            str_starts_with($this->value, 'sites.') => 'sites',
            str_starts_with($this->value, 'reports.') => 'reports',
            str_starts_with($this->value, 'custom_fields.') => 'custom_fields',
            str_starts_with($this->value, 'system.') => 'system',
            default => 'other',
        };
    }

    /**
     * Obtiene los permisos predeterminados para un rol.
     *
     * @return self[]
     */
    public static function forRole(Role $role): array
    {
        return match ($role) {
            Role::ADMIN => self::cases(),

            Role::MANAGER => [
                self::LEADS_VIEW_ALL,
                self::LEADS_VIEW_OWN,
                self::LEADS_CREATE,
                self::LEADS_UPDATE_ALL,
                self::LEADS_DELETE_ALL,
                self::LEADS_ASSIGN,
                self::DEALS_VIEW_ALL,
                self::DEALS_VIEW_OWN,
                self::DEALS_CREATE,
                self::DEALS_UPDATE_ALL,
                self::DEALS_DELETE_ALL,
                self::DEALS_ASSIGN,
                self::PHASES_MANAGE,
                self::REPORTS_VIEW_ALL,
                self::CUSTOM_FIELDS_VIEW, // Solo ver para llenar formularios
            ],

            Role::SALES => [
                self::LEADS_VIEW_OWN,
                self::LEADS_CREATE,
                self::LEADS_UPDATE_OWN,
                self::LEADS_DELETE_OWN,
                self::DEALS_VIEW_OWN,
                self::DEALS_CREATE,
                self::DEALS_UPDATE_OWN,
                self::DEALS_DELETE_OWN,
                self::REPORTS_VIEW_OWN,
                self::CUSTOM_FIELDS_VIEW, // Solo ver para llenar formularios
            ],
        };
    }

    /**
     * Obtiene todos los permisos agrupados.
     *
     * @return array<string, self[]>
     */
    public static function grouped(): array
    {
        $grouped = [];

        foreach (self::cases() as $permission) {
            $group = $permission->group();
            if (! isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][] = $permission;
        }

        return $grouped;
    }
}
