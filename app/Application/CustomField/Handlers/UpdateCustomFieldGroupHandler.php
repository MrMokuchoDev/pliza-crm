<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\Commands\UpdateCustomFieldGroupCommand;
use App\Application\CustomField\DTOs\CustomFieldGroupData;
use App\Domain\CustomField\Repositories\CustomFieldGroupRepositoryInterface;
use Ramsey\Uuid\Uuid;

final class UpdateCustomFieldGroupHandler
{
    public function __construct(
        private readonly CustomFieldGroupRepositoryInterface $groupRepository,
    ) {}

    public function handle(UpdateCustomFieldGroupCommand $command): CustomFieldGroupData
    {
        $group = $this->groupRepository->findById(Uuid::fromString($command->id));

        if (!$group) {
            throw new \DomainException("Custom field group not found: {$command->id}");
        }

        $oldOrder = $group->order();
        $group->changeName($command->name);

        if ($command->order !== null && $command->order !== $oldOrder) {
            // Obtener todos los grupos de la misma entidad
            $allGroups = $this->groupRepository->findByEntityType($group->entityType());

            // Reordenar grupos
            foreach ($allGroups as $otherGroup) {
                if ($otherGroup->id()->toString() === $group->id()->toString()) {
                    continue;
                }

                $otherOrder = $otherGroup->order();

                // Si el nuevo orden es menor que el anterior (moviendo hacia arriba)
                if ($command->order < $oldOrder) {
                    if ($otherOrder >= $command->order && $otherOrder < $oldOrder) {
                        $otherGroup->changeOrder($otherOrder + 1);
                        $this->groupRepository->save($otherGroup);
                    }
                }
                // Si el nuevo orden es mayor que el anterior (moviendo hacia abajo)
                else {
                    if ($otherOrder > $oldOrder && $otherOrder <= $command->order) {
                        $otherGroup->changeOrder($otherOrder - 1);
                        $this->groupRepository->save($otherGroup);
                    }
                }
            }

            $group->changeOrder($command->order);
        }

        $this->groupRepository->save($group);

        return CustomFieldGroupData::fromEntity($group);
    }
}
