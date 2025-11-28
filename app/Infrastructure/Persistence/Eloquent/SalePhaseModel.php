<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalePhaseModel extends Model
{
    use HasUuid;

    protected $table = 'sale_phases';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'order',
        'color',
        'is_closed',
        'is_won',
        'is_default',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_closed' => 'boolean',
            'is_won' => 'boolean',
            'is_default' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function leads(): HasMany
    {
        return $this->hasMany(LeadModel::class, 'sale_phase_id');
    }
}
