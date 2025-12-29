<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Shared\Traits\HasCustomFields;
use App\Domain\Shared\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DealModel extends Model
{
    use HasUuid;
    use SoftDeletes;
    use HasCustomFields;

    protected $table = 'deals';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $with = ['customFieldValues'];

    protected $fillable = [
        'id',
        'lead_id',
        'sale_phase_id',
        // name, value, description, estimated_close_date -> ahora son custom fields
        'close_date',
        'assigned_to',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            // value y estimated_close_date -> ahora son custom fields, NO cast aquí
            'close_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(LeadModel::class, 'lead_id');
    }

    public function salePhase(): BelongsTo
    {
        return $this->belongsTo(SalePhaseModel::class, 'sale_phase_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DealCommentModel::class, 'deal_id')->orderByDesc('created_at');
    }

    public function getDaysInPhaseAttribute(): int
    {
        return (int) $this->updated_at->diffInDays(now());
    }

    public function isOpen(): bool
    {
        return $this->close_date === null && ! $this->salePhase?->is_closed;
    }

    public function getFormattedValueAttribute(): ?string
    {
        if ($this->value === null) {
            return null;
        }

        return '$' . number_format((float) $this->value, 0, ',', '.');
    }

    /**
     * Relación con el usuario asignado.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'uuid');
    }

    /**
     * Relación con el usuario que creó el deal.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'uuid');
    }

    /**
     * Mapeo de propiedades a custom fields.
     */
    public function getCustomFieldMapping(): array
    {
        return [
            'name' => 'cf_deal_1',
            'value' => 'cf_deal_2',
            'description' => 'cf_deal_3',
            'estimated_close_date' => 'cf_deal_4',
        ];
    }

    /**
     * Tipo de entidad para custom fields.
     */
    protected function getEntityType(): string
    {
        return 'deal';
    }
}
