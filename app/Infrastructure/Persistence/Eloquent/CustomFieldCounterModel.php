<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class CustomFieldCounterModel extends Model
{
    protected $table = 'custom_field_counters';

    protected $primaryKey = 'entity_type';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'entity_type',
        'counter',
    ];

    protected $casts = [
        'counter' => 'integer',
    ];
}
