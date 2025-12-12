<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Shared\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent para roles.
 *
 * @property string $id
 * @property string $name
 * @property string $display_name
 * @property string|null $description
 * @property int $level
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class RoleModel extends Model
{
    use HasUuid;

    protected $table = 'roles';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'display_name',
        'description',
        'level',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Relación con permisos.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            PermissionModel::class,
            'role_permission',
            'role_id',
            'permission_id'
        )->withTimestamps();
    }

    /**
     * Relación con usuarios.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * Verifica si el rol tiene un permiso específico.
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions->contains('name', $permissionName);
    }

    /**
     * Obtiene el rol por nombre.
     */
    public static function findByName(string $name): ?self
    {
        return self::where('name', $name)->first();
    }
}
