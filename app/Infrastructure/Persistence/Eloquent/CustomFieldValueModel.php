<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

final class CustomFieldValueModel extends Model
{
    use HasUuids;

    protected $table = 'custom_field_values';

    protected $with = ['customField'];

    protected $fillable = [
        'id',
        'custom_field_id',
        'entity_type',
        'entity_id',
        'value',
    ];

    public function customField()
    {
        return $this->belongsTo(CustomFieldModel::class, 'custom_field_id');
    }

    /**
     * Accessor para decodificar valores JSON automáticamente (multiselect).
     */
    public function getValueAttribute($value): mixed
    {
        // Intentar decodificar JSON (para campos multiselect)
        if (is_string($value) && str_starts_with($value, '[')) {
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }
        return $value;
    }

    /**
     * Mutator para codificar arrays a JSON automáticamente (multiselect).
     */
    public function setValueAttribute($value): void
    {
        $this->attributes['value'] = is_array($value) ? json_encode($value) : $value;
    }
}
