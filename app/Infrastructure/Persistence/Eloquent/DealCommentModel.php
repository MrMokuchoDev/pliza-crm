<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealCommentModel extends Model
{
    use HasUuid;

    protected $table = 'deal_comments';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'deal_id',
        'content',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(DealModel::class, 'deal_id');
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'call' => 'phone',
            'whatsapp' => 'whatsapp',
            'email' => 'email',
            default => 'comment',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'call' => 'Llamada',
            'whatsapp' => 'WhatsApp',
            'email' => 'Email',
            default => 'Comentario',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'call' => 'blue',
            'whatsapp' => 'green',
            'email' => 'purple',
            default => 'gray',
        };
    }
}
