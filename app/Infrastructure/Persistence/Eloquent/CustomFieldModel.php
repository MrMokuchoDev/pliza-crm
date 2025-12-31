<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

final class CustomFieldModel extends Model
{
    use HasUuids;

    protected $table = 'custom_fields';

    protected $fillable = [
        'id',
        'entity_type',
        'group_id',
        'name',
        'label',
        'type',
        'default_value',
        'is_required',
        'validation_rules',
        'order',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'validation_rules' => 'array',
        'order' => 'integer',
    ];

    public function group()
    {
        return $this->belongsTo(CustomFieldGroupModel::class, 'group_id');
    }

    public function values()
    {
        return $this->hasMany(CustomFieldValueModel::class, 'custom_field_id');
    }
}
