<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\Commands\DeleteCustomFieldGroupCommand;
use App\Domain\CustomField\Repositories\CustomFieldGroupRepositoryInterface;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use Ramsey\Uuid\Uuid;

final class DeleteCustomFieldGroupHandler
{
    public function __construct(
        private readonly CustomFieldGroupRepositoryInterface $groupRepository,
        private readonly CustomFieldRepositoryInterface $fieldRepository,
    ) {}

    public function handle(DeleteCustomFieldGroupCommand $command): void
    {
        $groupId = Uuid::fromString($command->id);

        $group = $this->groupRepository->findById($groupId);

        if (!$group) {
            throw new \DomainException("Custom field group not found: {$command->id}");
        }

        // Obtener campos del grupo
        $fields = $this->fieldRepository->findByGroup($groupId);

        // Si hay campos y se proporcionó un grupo destino, transferirlos
        if (!empty($fields) && $command->targetGroupId !== null) {
            $targetGroupId = Uuid::fromString($command->targetGroupId);
            $targetGroup = $this->groupRepository->findById($targetGroupId);

            if (!$targetGroup) {
                throw new \DomainException("Target group not found: {$command->targetGroupId}");
            }

            foreach ($fields as $field) {
                $field->updateGroup($targetGroupId);
                $this->fieldRepository->save($field);
            }
        } elseif (!empty($fields)) {
            // Si hay campos pero no hay grupo destino, dejar los campos sin grupo
            foreach ($fields as $field) {
                $field->removeGroup();
                $this->fieldRepository->save($field);
            }
        }

        $this->groupRepository->delete($groupId);
    }
}
