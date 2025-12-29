<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Lead\ValueObjects\SourceType;
use App\Domain\Shared\Traits\HasCustomFields;
use App\Domain\Shared\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadModel extends Model
{
    use HasUuid;
    use SoftDeletes;
    use HasCustomFields;

    protected $table = 'leads';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $with = ['customFieldValues'];

    protected $fillable = [
        'id',
        // name, email, phone, message -> ahora son custom fields
        'source_type',
        'source_site_id',
        'source_url',
        'metadata',
        'assigned_to',
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

    /**
     * Relación con el usuario asignado.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'uuid');
    }

    /**
     * Mapeo de propiedades a custom fields.
     */
    public function getCustomFieldMapping(): array
    {
        return [
            'name' => 'cf_lead_1',
            'email' => 'cf_lead_2',
            'phone' => 'cf_lead_3',
            'message' => 'cf_lead_4',
        ];
    }

    /**
     * Tipo de entidad para custom fields.
     */
    protected function getEntityType(): string
    {
        return 'lead';
    }
}
