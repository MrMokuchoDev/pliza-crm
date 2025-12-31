<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class EntityTypeModel extends Model
{
    protected $table = 'entity_types';

    protected $primaryKey = 'name';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'label',
        'model_class',
        'allows_custom_fields',
        'is_active',
        'order',
    ];

    protected $casts = [
        'allows_custom_fields' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Scope para obtener solo entidades activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para obtener solo entidades que permiten custom fields
     */
    public function scopeAllowsCustomFields($query)
    {
        return $query->where('allows_custom_fields', true);
    }
}
