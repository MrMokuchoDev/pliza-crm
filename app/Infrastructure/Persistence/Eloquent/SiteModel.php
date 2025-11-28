<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
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
        'settings',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function leads(): HasMany
    {
        return $this->hasMany(LeadModel::class, 'source_site_id');
    }
}
