<?php

declare(strict_types=1);

namespace App\Domain\Lead\Repositories;

use App\Domain\Lead\Entities\Lead;
use App\Domain\Lead\ValueObjects\SourceType;

interface LeadRepositoryInterface
{
    public function findById(string $id): ?Lead;

    /** @return Lead[] */
    public function findAll(array $filters = []): array;

    public function findPaginated(int $perPage = 15, array $filters = []): mixed;

    /** @return Lead[] */
    public function findByPhase(string $salePhaseId): array;

    /** @return Lead[] */
    public function findBySourceType(SourceType $sourceType): array;

    /** @return Lead[] */
    public function findBySite(string $siteId): array;

    public function save(Lead $lead): void;

    public function updatePhase(string $id, string $newPhaseId): void;

    public function delete(string $id): void;

    public function forceDelete(string $id): void;

    public function restore(string $id): void;

    public function transferToPhase(string $fromPhaseId, string $toPhaseId): int;

    public function countByPhase(string $salePhaseId): int;

    public function countBySite(string $siteId): int;
}
