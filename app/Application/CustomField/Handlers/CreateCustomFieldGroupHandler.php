<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\Commands\CreateCustomFieldGroupCommand;
use App\Application\CustomField\DTOs\CustomFieldGroupData;
use App\Domain\CustomField\Entities\CustomFieldGroup;
use App\Domain\CustomField\Repositories\CustomFieldGroupRepositoryInterface;
use App\Domain\CustomField\ValueObjects\EntityType;
use Ramsey\Uuid\Uuid;

final class CreateCustomFieldGroupHandler
{
    public function __construct(
        private readonly CustomFieldGroupRepositoryInterface $groupRepository,
    ) {}

    public function handle(CreateCustomFieldGroupCommand $command): CustomFieldGroupData
    {
        $entityType = EntityType::from($command->entityType);

        $order = $command->order ?? $this->groupRepository->getNextOrder($entityType);

        $group = CustomFieldGroup::create(
            id: Uuid::uuid4(),
            entityType: $entityType,
            name: $command->name,
            order: $order,
        );

        $this->groupRepository->save($group);

        return CustomFieldGroupData::fromEntity($group);
    }
}
