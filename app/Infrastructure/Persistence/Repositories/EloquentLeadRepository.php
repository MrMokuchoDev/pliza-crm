<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\CustomField\ValueObjects\SystemCustomFields;
use App\Domain\Lead\Entities\Lead;
use App\Domain\Lead\Repositories\LeadRepositoryInterface;
use App\Domain\Lead\ValueObjects\SourceType;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentLeadRepository implements LeadRepositoryInterface
{
    public function findById(string $id): ?Lead
    {
        $model = LeadModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findAll(array $filters = []): array
    {
        $query = $this->applyFilters(LeadModel::query(), $filters);

        return $query->orderByDesc('created_at')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->toArray();
    }

    public function findPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->applyFilters(LeadModel::query(), $filters);

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function findByPhase(string $salePhaseId): array
    {
        return LeadModel::where('sale_phase_id', $salePhaseId)
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->toArray();
    }

    public function findBySourceType(SourceType $sourceType): array
    {
        return LeadModel::where('source_type', $sourceType->value)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->toArray();
    }

    public function findBySite(string $siteId): array
    {
        return LeadModel::where('source_site_id', $siteId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->toArray();
    }

    public function save(Lead $lead): void
    {
        // Los campos name, email, phone, message ahora son custom fields
        // y se guardan automáticamente via el trait HasCustomFields
        LeadModel::updateOrCreate(
            ['id' => $lead->id],
            [
                'source_type' => $lead->sourceType->value,
                'source_site_id' => $lead->sourceSiteId,
                'source_url' => $lead->sourceUrl,
                'metadata' => $lead->metadata,
            ]
        );
    }

    public function updatePhase(string $id, string $newPhaseId): void
    {
        LeadModel::where('id', $id)->update([
            'sale_phase_id' => $newPhaseId,
            'updated_at' => now(),
        ]);
    }

    public function delete(string $id): void
    {
        LeadModel::destroy($id);
    }

    public function forceDelete(string $id): void
    {
        LeadModel::withTrashed()->where('id', $id)->forceDelete();
    }

    public function restore(string $id): void
    {
        LeadModel::withTrashed()->where('id', $id)->restore();
    }

    public function transferToPhase(string $fromPhaseId, string $toPhaseId): int
    {
        return LeadModel::where('sale_phase_id', $fromPhaseId)
            ->update(['sale_phase_id' => $toPhaseId]);
    }

    public function countByPhase(string $salePhaseId): int
    {
        return LeadModel::where('sale_phase_id', $salePhaseId)->count();
    }

    public function countBySite(string $siteId): int
    {
        return LeadModel::where('source_site_id', $siteId)->count();
    }

    private function applyFilters($query, array $filters)
    {
        if (! empty($filters['search'])) {
            $query->searchInCustomFields(
                $filters['search'],
                SystemCustomFields::getLeadSearchableFields()
            );
        }

        if (! empty($filters['sale_phase_id'])) {
            $query->where('sale_phase_id', $filters['sale_phase_id']);
        }

        if (! empty($filters['source_type'])) {
            $query->where('source_type', $filters['source_type']);
        }

        if (! empty($filters['source_site_id'])) {
            $query->where('source_site_id', $filters['source_site_id']);
        }

        if (! empty($filters['date_from'])) {
            $dateField = $filters['date_field'] ?? 'created_at';
            $query->where($dateField, '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $dateField = $filters['date_field'] ?? 'created_at';
            $query->where($dateField, '<=', $filters['date_to']);
        }

        return $query;
    }

    private function toDomain(LeadModel $model): Lead
    {
        // Los campos name, email, phone, message ahora son custom fields
        // El trait HasCustomFields los mapea automáticamente via getAttribute
        return new Lead(
            id: $model->id,
            name: $model->getAttribute('name'),        // cf_lead_1
            email: $model->getAttribute('email'),      // cf_lead_2
            phone: $model->getAttribute('phone'),      // cf_lead_3
            message: $model->getAttribute('message'),  // cf_lead_4
            sourceType: $model->source_type,
            sourceSiteId: $model->source_site_id,
            sourceUrl: $model->source_url,
            metadata: $model->metadata,
            createdAt: $model->created_at ? \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()) : null,
            updatedAt: $model->updated_at ? \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()) : null,
            deletedAt: $model->deleted_at ? \DateTimeImmutable::createFromMutable($model->deleted_at->toDateTime()) : null,
        );
    }
}
