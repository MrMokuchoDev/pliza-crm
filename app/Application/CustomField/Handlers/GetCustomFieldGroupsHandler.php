<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\DTOs\CustomFieldGroupData;
use App\Application\CustomField\Queries\GetCustomFieldGroupsQuery;
use App\Domain\CustomField\Repositories\CustomFieldGroupRepositoryInterface;
use App\Domain\CustomField\ValueObjects\EntityType;

final class GetCustomFieldGroupsHandler
{
    public function __construct(
        private readonly CustomFieldGroupRepositoryInterface $groupRepository,
    ) {}

    /**
     * @return CustomFieldGroupData[]
     */
    public function handle(GetCustomFieldGroupsQuery $query): array
    {
        $entityType = EntityType::from($query->entityType);

        $groups = $this->groupRepository->findByEntityType($entityType);

        return array_map(
            fn($group) => CustomFieldGroupData::fromEntity($group),
            $groups
        );
    }
}
