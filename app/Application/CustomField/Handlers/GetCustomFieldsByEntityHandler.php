<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\DTOs\CustomFieldData;
use App\Application\CustomField\Queries\GetCustomFieldsByEntityQuery;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use App\Domain\CustomField\ValueObjects\EntityType;
use Ramsey\Uuid\Uuid;

final class GetCustomFieldsByEntityHandler
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $fieldRepository,
    ) {}

    /**
     * @return CustomFieldData[]
     */
    public function handle(GetCustomFieldsByEntityQuery $query): array
    {
        $entityType = EntityType::from($query->entityType);

        if ($query->groupId !== null) {
            $fields = $this->fieldRepository->findByGroup(Uuid::fromString($query->groupId));
        } else {
            $fields = $this->fieldRepository->findByEntityType($entityType, $query->activeOnly);
        }

        return array_map(
            fn($field) => CustomFieldData::fromEntity($field),
            $fields
        );
    }
}
