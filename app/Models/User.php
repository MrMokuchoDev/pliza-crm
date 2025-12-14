<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\User\ValueObjects\Permission;
use App\Infrastructure\Persistence\Eloquent\RoleModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'uuid',
        'role_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relación con el rol.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(RoleModel::class, 'role_id');
    }

    /**
     * Verifica si el usuario tiene un permiso específico.
     */
    public function hasPermission(string|Permission $permission): bool
    {
        $permissionName = $permission instanceof Permission ? $permission->value : $permission;

        return $this->role?->hasPermission($permissionName) ?? false;
    }

    /**
     * Verifica si el usuario tiene alguno de los permisos especificados.
     *
     * @param array<string|Permission> $permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica si el usuario tiene un rol específico.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role?->name === $roleName;
    }

    /**
     * Verifica si el usuario es administrador.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Verifica si el usuario es gerente.
     */
    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    /**
     * Verifica si el usuario es vendedor.
     */
    public function isSales(): bool
    {
        return $this->hasRole('sales');
    }

    /**
     * Verifica si el usuario puede acceder al módulo de contactos.
     * Tiene acceso si tiene cualquier permiso de leads (ver, crear, editar, eliminar, asignar).
     */
    public function canAccessLeads(): bool
    {
        return $this->hasAnyPermission([
            Permission::LEADS_VIEW_ALL,
            Permission::LEADS_VIEW_OWN,
            Permission::LEADS_CREATE,
            Permission::LEADS_UPDATE_ALL,
            Permission::LEADS_UPDATE_OWN,
            Permission::LEADS_DELETE_ALL,
            Permission::LEADS_DELETE_OWN,
            Permission::LEADS_ASSIGN,
        ]);
    }

    /**
     * Verifica si el usuario puede ver todos los leads.
     */
    public function canViewAllLeads(): bool
    {
        return $this->hasPermission(Permission::LEADS_VIEW_ALL);
    }

    /**
     * Verifica si el usuario puede crear leads.
     */
    public function canCreateLeads(): bool
    {
        return $this->hasPermission(Permission::LEADS_CREATE);
    }

    /**
     * Verifica si el usuario puede editar leads (todos o propios).
     */
    public function canEditLeads(): bool
    {
        return $this->hasAnyPermission([
            Permission::LEADS_UPDATE_ALL,
            Permission::LEADS_UPDATE_OWN,
        ]);
    }

    /**
     * Verifica si el usuario puede eliminar leads (todos o propios).
     */
    public function canDeleteLeads(): bool
    {
        return $this->hasAnyPermission([
            Permission::LEADS_DELETE_ALL,
            Permission::LEADS_DELETE_OWN,
        ]);
    }

    /**
     * Verifica si el usuario puede acceder al módulo de negocios.
     * Tiene acceso si tiene cualquier permiso de deals (ver, crear, editar, eliminar, asignar).
     */
    public function canAccessDeals(): bool
    {
        return $this->hasAnyPermission([
            Permission::DEALS_VIEW_ALL,
            Permission::DEALS_VIEW_OWN,
            Permission::DEALS_CREATE,
            Permission::DEALS_UPDATE_ALL,
            Permission::DEALS_UPDATE_OWN,
            Permission::DEALS_DELETE_ALL,
            Permission::DEALS_DELETE_OWN,
            Permission::DEALS_ASSIGN,
        ]);
    }

    /**
     * Verifica si el usuario puede ver todos los negocios.
     */
    public function canViewAllDeals(): bool
    {
        return $this->hasPermission(Permission::DEALS_VIEW_ALL);
    }

    /**
     * Verifica si el usuario puede crear negocios.
     */
    public function canCreateDeals(): bool
    {
        return $this->hasPermission(Permission::DEALS_CREATE);
    }

    /**
     * Verifica si el usuario puede editar negocios (todos o propios).
     */
    public function canEditDeals(): bool
    {
        return $this->hasAnyPermission([
            Permission::DEALS_UPDATE_ALL,
            Permission::DEALS_UPDATE_OWN,
        ]);
    }

    /**
     * Verifica si el usuario puede eliminar negocios (todos o propios).
     */
    public function canDeleteDeals(): bool
    {
        return $this->hasAnyPermission([
            Permission::DEALS_DELETE_ALL,
            Permission::DEALS_DELETE_OWN,
        ]);
    }

    /**
     * Verifica si el usuario puede editar un deal específico.
     * Considera si es propio o si tiene permiso de editar todos.
     */
    public function canEditDeal(?string $dealOwnerId): bool
    {
        if ($this->hasPermission(Permission::DEALS_UPDATE_ALL)) {
            return true;
        }

        if ($this->hasPermission(Permission::DEALS_UPDATE_OWN) && $dealOwnerId === $this->uuid) {
            return true;
        }

        return false;
    }

    /**
     * Verifica si el usuario puede eliminar un deal específico.
     * Considera si es propio o si tiene permiso de eliminar todos.
     */
    public function canDeleteDeal(?string $dealOwnerId): bool
    {
        if ($this->hasPermission(Permission::DEALS_DELETE_ALL)) {
            return true;
        }

        if ($this->hasPermission(Permission::DEALS_DELETE_OWN) && $dealOwnerId === $this->uuid) {
            return true;
        }

        return false;
    }

    /**
     * Verifica si el usuario puede editar un lead específico.
     * Considera si es propio o si tiene permiso de editar todos.
     */
    public function canEditLead(?string $leadOwnerId): bool
    {
        if ($this->hasPermission(Permission::LEADS_UPDATE_ALL)) {
            return true;
        }

        if ($this->hasPermission(Permission::LEADS_UPDATE_OWN) && $leadOwnerId === $this->uuid) {
            return true;
        }

        return false;
    }

    /**
     * Verifica si el usuario puede eliminar un lead específico.
     * Considera si es propio o si tiene permiso de eliminar todos.
     */
    public function canDeleteLead(?string $leadOwnerId): bool
    {
        if ($this->hasPermission(Permission::LEADS_DELETE_ALL)) {
            return true;
        }

        if ($this->hasPermission(Permission::LEADS_DELETE_OWN) && $leadOwnerId === $this->uuid) {
            return true;
        }

        return false;
    }

    /**
     * Verifica si el usuario puede asignar leads.
     */
    public function canAssignLeads(): bool
    {
        return $this->hasPermission(Permission::LEADS_ASSIGN);
    }

    /**
     * Verifica si el usuario puede asignar negocios.
     */
    public function canAssignDeals(): bool
    {
        return $this->hasPermission(Permission::DEALS_ASSIGN);
    }

    /**
     * Verifica si el usuario puede acceder al módulo de usuarios.
     * Tiene acceso si tiene cualquier permiso de usuarios (ver, crear, editar, eliminar, asignar roles).
     */
    public function canManageUsers(): bool
    {
        return $this->hasAnyPermission([
            Permission::USERS_VIEW,
            Permission::USERS_CREATE,
            Permission::USERS_UPDATE,
            Permission::USERS_DELETE,
            Permission::USERS_ASSIGN_ROLE,
        ]);
    }

    /**
     * Verifica si el usuario puede crear usuarios.
     */
    public function canCreateUsers(): bool
    {
        return $this->hasPermission(Permission::USERS_CREATE);
    }

    /**
     * Verifica si el usuario puede editar usuarios.
     */
    public function canUpdateUsers(): bool
    {
        return $this->hasPermission(Permission::USERS_UPDATE);
    }

    /**
     * Verifica si el usuario puede eliminar usuarios.
     */
    public function canDeleteUsers(): bool
    {
        return $this->hasPermission(Permission::USERS_DELETE);
    }

    /**
     * Verifica si el usuario puede gestionar fases.
     * Considera tanto el permiso general como los granulares.
     */
    public function canManagePhases(): bool
    {
        return $this->hasAnyPermission([
            Permission::PHASES_MANAGE,
            'phases.manage',
            'sale_phases.view',
            'sale_phases.create',
            'sale_phases.update',
            'sale_phases.delete',
        ]);
    }

    /**
     * Verifica si el usuario puede ver sitios (acceso al menú).
     * Considera tanto el permiso general como los granulares.
     */
    public function canManageSites(): bool
    {
        return $this->hasAnyPermission([
            Permission::SITES_MANAGE,
            'sites.manage',
            'sites.view',
            'sites.create',
            'sites.update',
            'sites.delete',
        ]);
    }

    /**
     * Verifica si el usuario puede crear sitios.
     */
    public function canCreateSites(): bool
    {
        return $this->hasAnyPermission([
            Permission::SITES_MANAGE,
            'sites.manage',
            'sites.create',
        ]);
    }

    /**
     * Verifica si el usuario puede editar sitios.
     */
    public function canUpdateSites(): bool
    {
        return $this->hasAnyPermission([
            Permission::SITES_MANAGE,
            'sites.manage',
            'sites.update',
        ]);
    }

    /**
     * Verifica si el usuario puede eliminar sitios.
     */
    public function canDeleteSites(): bool
    {
        return $this->hasAnyPermission([
            Permission::SITES_MANAGE,
            'sites.manage',
            'sites.delete',
        ]);
    }

    /**
     * Verifica si el usuario puede acceder al mantenimiento.
     */
    public function canAccessMaintenance(): bool
    {
        return $this->hasPermission(Permission::SYSTEM_MAINTENANCE);
    }

    /**
     * Verifica si el usuario puede gestionar actualizaciones.
     */
    public function canManageUpdates(): bool
    {
        return $this->hasPermission(Permission::SYSTEM_UPDATES);
    }

    /**
     * Verifica si el usuario puede gestionar roles y permisos.
     */
    public function canManageRoles(): bool
    {
        return $this->hasPermission(Permission::USERS_ASSIGN_ROLE);
    }

    /**
     * Verifica si el usuario tiene al menos un permiso de configuración.
     * Se usa para determinar si mostrar el menú de configuración.
     * Considera tanto permisos del enum como los granulares de la BD.
     */
    public function hasAnyConfigPermission(): bool
    {
        return $this->hasAnyPermission([
            // Permisos del enum
            Permission::PHASES_MANAGE,
            Permission::SITES_MANAGE,
            Permission::USERS_VIEW,
            Permission::USERS_ASSIGN_ROLE,
            Permission::SYSTEM_MAINTENANCE,
            Permission::SYSTEM_UPDATES,
            // Permisos granulares de fases
            'sale_phases.view',
            'sale_phases.create',
            'sale_phases.update',
            'sale_phases.delete',
            // Permisos granulares de sitios
            'sites.view',
            'sites.create',
            'sites.update',
            'sites.delete',
            // Permisos granulares de usuarios (strings)
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.assign_role',
            // Permisos de sistema (strings)
            'system.maintenance',
            'system.updates',
        ]);
    }

    /**
     * Obtiene el nivel jerárquico del usuario.
     */
    public function getRoleLevel(): int
    {
        return $this->role?->level ?? 0;
    }

    /**
     * Verifica si el usuario puede gestionar a otro usuario.
     */
    public function canManageUser(User $otherUser): bool
    {
        return $this->getRoleLevel() > $otherUser->getRoleLevel();
    }
}
