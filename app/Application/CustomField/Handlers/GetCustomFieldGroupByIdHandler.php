<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\DTOs\CustomFieldGroupData;
use App\Application\CustomField\Queries\GetCustomFieldGroupByIdQuery;
use App\Domain\CustomField\Repositories\CustomFieldGroupRepositoryInterface;
use Ramsey\Uuid\Uuid;

final class GetCustomFieldGroupByIdHandler
{
    public function __construct(
        private readonly CustomFieldGroupRepositoryInterface $groupRepository,
    ) {}

    public function handle(GetCustomFieldGroupByIdQuery $query): ?CustomFieldGroupData
    {
        $group = $this->groupRepository->findById(Uuid::fromString($query->id));

        if (!$group) {
            return null;
        }

        return CustomFieldGroupData::fromEntity($group);
    }
}
