<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Lead\ValueObjects\SourceType;
use App\Domain\Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadModel extends Model
{
    use HasUuid;
    use SoftDeletes;

    protected $table = 'leads';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'email',
        'phone',
        'message',
        'source_type',
        'source_site_id',
        'source_url',
        'sale_phase_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'source_type' => SourceType::class,
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function salePhase(): BelongsTo
    {
        return $this->belongsTo(SalePhaseModel::class, 'sale_phase_id');
    }

    public function sourceSite(): BelongsTo
    {
        return $this->belongsTo(SiteModel::class, 'source_site_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(NoteModel::class, 'lead_id')->orderByDesc('created_at');
    }

    public function getDaysInPhaseAttribute(): int
    {
        return (int) $this->updated_at->diffInDays(now());
    }
}
