<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\Commands\DeleteCustomFieldGroupCommand;
use App\Domain\CustomField\Repositories\CustomFieldGroupRepositoryInterface;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use Illuminate\Support\Facades\DB;
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

        // Transacción atómica: Todo o nada
        DB::transaction(function () use ($groupId, $group, $command) {
            // Obtener campos del grupo a eliminar
            $fields = $this->fieldRepository->findByGroup($groupId);

            // Si hay campos y se proporcionó un grupo destino, transferirlos
            if (!empty($fields) && $command->targetGroupId !== null) {
                $targetGroupId = Uuid::fromString($command->targetGroupId);
                $targetGroup = $this->groupRepository->findById($targetGroupId);

                if (!$targetGroup) {
                    throw new \DomainException("Target group not found: {$command->targetGroupId}");
                }

                // Obtener campos existentes en el grupo destino para calcular el siguiente orden
                $targetGroupFields = $this->fieldRepository->findByGroup($targetGroupId);
                $maxOrder = 0;
                foreach ($targetGroupFields as $targetField) {
                    if ($targetField->order() > $maxOrder) {
                        $maxOrder = $targetField->order();
                    }
                }

                // Transferir campos al grupo destino, asignándoles orden consecutivo al final
                $newOrder = $maxOrder + 1;
                foreach ($fields as $field) {
                    $field->updateGroup($targetGroupId);
                    $field->updateOrder($newOrder);
                    $this->fieldRepository->save($field);
                    $newOrder++;
                }
            } elseif (!empty($fields)) {
                // Si hay campos pero no hay grupo destino, dejar los campos sin grupo
                foreach ($fields as $field) {
                    $field->removeGroup();
                    $this->fieldRepository->save($field);
                }
            }

            // Obtener el orden del grupo a eliminar
            $deletedOrder = $group->order();

            // Eliminar el grupo
            $this->groupRepository->delete($groupId);

            // Reordenar los grupos restantes de la misma entidad
            $remainingGroups = $this->groupRepository->findByEntityType($group->entityType());

            // Actualizar el orden de los grupos que están después del eliminado
            foreach ($remainingGroups as $remainingGroup) {
                if ($remainingGroup->order() > $deletedOrder) {
                    $remainingGroup->changeOrder($remainingGroup->order() - 1);
                    $this->groupRepository->save($remainingGroup);
                }
            }
        });
    }
}
