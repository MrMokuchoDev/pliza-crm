<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

final class CustomFieldGroupModel extends Model
{
    use HasUuids;

    protected $table = 'custom_field_groups';

    protected $fillable = [
        'id',
        'entity_type',
        'name',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function fields()
    {
        return $this->hasMany(CustomFieldModel::class, 'group_id');
    }
}
