<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Shared\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteModel extends Model
{
    use HasUuid;

    protected $table = 'sites';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'domain',
        'api_key',
        'is_active',
        'default_user_id',
        'round_robin_index',
        'settings',
        'privacy_policy_url',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
            'created_at' => 'datetime',
            'round_robin_index' => 'integer',
        ];
    }

    /**
     * Usuario por defecto para asignar leads.
     */
    public function defaultUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'default_user_id', 'uuid');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(LeadModel::class, 'source_site_id');
    }

    /**
     * Verifica si tiene un usuario por defecto configurado.
     */
    public function hasDefaultUser(): bool
    {
        return $this->default_user_id !== null;
    }
}
