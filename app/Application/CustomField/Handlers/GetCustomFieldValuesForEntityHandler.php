<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\DTOs\CustomFieldValueData;
use App\Application\CustomField\Queries\GetCustomFieldValuesForEntityQuery;
use App\Domain\CustomField\Repositories\CustomFieldValueRepositoryInterface;
use App\Domain\CustomField\ValueObjects\EntityType;
use Ramsey\Uuid\Uuid;

final class GetCustomFieldValuesForEntityHandler
{
    public function __construct(
        private readonly CustomFieldValueRepositoryInterface $valueRepository,
    ) {}

    /**
     * @return CustomFieldValueData[]
     */
    public function handle(GetCustomFieldValuesForEntityQuery $query): array
    {
        $entityType = EntityType::from($query->entityType);
        $entityId = Uuid::fromString($query->entityId);

        $values = $this->valueRepository->findByEntity($entityType, $entityId);

        return array_map(
            fn($value) => CustomFieldValueData::fromEntity($value),
            $values
        );
    }
}
