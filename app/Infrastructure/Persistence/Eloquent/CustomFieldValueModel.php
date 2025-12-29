<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

final class CustomFieldValueModel extends Model
{
    use HasUuids;

    protected $table = 'custom_field_values';

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
}
