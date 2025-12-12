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
     * Verifica si el usuario puede gestionar usuarios.
     */
    public function canManageUsers(): bool
    {
        return $this->hasPermission(Permission::USERS_VIEW);
    }

    /**
     * Verifica si el usuario puede gestionar fases.
     */
    public function canManagePhases(): bool
    {
        return $this->hasPermission(Permission::PHASES_MANAGE);
    }

    /**
     * Verifica si el usuario puede gestionar sitios.
     */
    public function canManageSites(): bool
    {
        return $this->hasPermission(Permission::SITES_MANAGE);
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
