<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\Commands\DeleteCustomFieldCommand;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use App\Domain\CustomField\Repositories\CustomFieldValueRepositoryInterface;
use App\Domain\CustomField\Services\CustomFieldOptionsTableManagerInterface;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

final class DeleteCustomFieldHandler
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $fieldRepository,
        private readonly CustomFieldValueRepositoryInterface $valueRepository,
        private readonly CustomFieldOptionsTableManagerInterface $optionsTableManager,
    ) {}

    public function handle(DeleteCustomFieldCommand $command): void
    {
        $fieldId = Uuid::fromString($command->id);

        $field = $this->fieldRepository->findById($fieldId);

        if (!$field) {
            throw new \DomainException("Custom field not found: {$command->id}");
        }

        // Prevenir eliminación de campos del sistema
        if ($field->isSystem()) {
            throw new \DomainException("Cannot delete system field: {$field->label()}");
        }

        $deletedOrder = $field->order();
        $entityType = $field->entityType();
        $groupId = $field->groupId();
        $hasOptionsTable = $field->type()->requiresOptions();
        $fieldName = $field->name();

        // Transacción atómica: Todo o nada (excepto DROP TABLE que debe ir fuera)
        DB::transaction(function () use ($fieldId, $field, $deletedOrder, $entityType, $groupId) {
            // 1. Eliminar todos los valores asociados
            $this->valueRepository->deleteByField($fieldId);

            // 2. Eliminar el campo
            $this->fieldRepository->delete($fieldId);

            // 3. Reordenar los campos restantes de la misma entidad y grupo
            $remainingFields = $this->fieldRepository->findByEntityType($entityType);

            // Filtrar solo los campos del mismo grupo (o sin grupo si el eliminado tampoco tenía)
            $fieldsToReorder = array_filter($remainingFields, function ($remainingField) use ($groupId) {
                if ($groupId === null) {
                    return $remainingField->groupId() === null;
                }
                return $remainingField->groupId()?->toString() === $groupId->toString();
            });

            // Actualizar el orden de los campos que están después del eliminado
            foreach ($fieldsToReorder as $remainingField) {
                if ($remainingField->order() > $deletedOrder) {
                    $remainingField->updateOrder($remainingField->order() - 1);
                    $this->fieldRepository->save($remainingField);
                }
            }
        });

        // Eliminar tabla de opciones DESPUÉS de la transacción
        // (DROP TABLE hace commit implícito en MySQL y rompe la transacción)
        if ($hasOptionsTable) {
            $this->optionsTableManager->dropTable($fieldName);
        }
    }
}
