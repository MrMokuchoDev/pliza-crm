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

    public function deals(): HasMany
    {
        return $this->hasMany(DealModel::class, 'lead_id')->orderByDesc('created_at');
    }

    public function activeDeals(): HasMany
    {
        return $this->hasMany(DealModel::class, 'lead_id')
            ->whereHas('salePhase', fn ($q) => $q->where('is_closed', false))
            ->orderByDesc('created_at');
    }

    public function sourceSite(): BelongsTo
    {
        return $this->belongsTo(SiteModel::class, 'source_site_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(NoteModel::class, 'lead_id')->orderByDesc('created_at');
    }

    public function hasOpenDeal(?string $excludeDealId = null): bool
    {
        $query = $this->activeDeals();

        if ($excludeDealId) {
            $query->where('id', '!=', $excludeDealId);
        }

        return $query->exists();
    }
}
