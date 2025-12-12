<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Modelo Eloquent para permisos.
 *
 * @property string $id
 * @property string $name
 * @property string $display_name
 * @property string $group
 * @property string|null $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PermissionModel extends Model
{
    use HasUuid;

    protected $table = 'permissions';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'display_name',
        'group',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Relación con roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            RoleModel::class,
            'role_permission',
            'permission_id',
            'role_id'
        )->withTimestamps();
    }

    /**
     * Obtiene el permiso por nombre.
     */
    public static function findByName(string $name): ?self
    {
        return self::where('name', $name)->first();
    }

    /**
     * Obtiene permisos agrupados.
     *
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, self>>
     */
    public static function getGrouped(): \Illuminate\Support\Collection
    {
        return self::all()->groupBy('group');
    }
}
