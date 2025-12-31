<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\Commands\UpdateCustomFieldCommand;
use App\Application\CustomField\DTOs\CustomFieldData;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use App\Domain\CustomField\Services\CustomFieldOptionsTableManagerInterface;
use App\Domain\CustomField\ValueObjects\FieldName;
use Ramsey\Uuid\Uuid;

final class UpdateCustomFieldHandler
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $fieldRepository,
        private readonly CustomFieldOptionsTableManagerInterface $optionsTableManager,
    ) {}

    public function handle(UpdateCustomFieldCommand $command): CustomFieldData
    {
        $field = $this->fieldRepository->findById(Uuid::fromString($command->id));

        if (!$field) {
            throw new \DomainException("Custom field not found: {$command->id}");
        }

        if ($command->label !== null) {
            $field->updateLabel($command->label);
        }

        // Detectar cambio de grupo para reordenar
        $originalGroupId = $field->groupId();
        $groupChanged = false;

        if ($command->groupId !== null) {
            $newGroupId = $command->groupId === '' ? null : Uuid::fromString($command->groupId);

            // Verificar si el grupo realmente cambió
            $groupChanged = ($originalGroupId?->toString() ?? null) !== ($newGroupId?->toString() ?? null);

            if ($groupChanged) {
                $originalOrder = $field->order();
                $entityType = $field->entityType();

                if ($command->groupId === '') {
                    $field->removeGroup();
                } else {
                    // Obtener campos del grupo destino para calcular el siguiente orden
                    $targetGroupFields = $this->fieldRepository->findByGroup($newGroupId);
                    $maxOrder = 0;
                    foreach ($targetGroupFields as $targetField) {
                        if ($targetField->order() > $maxOrder) {
                            $maxOrder = $targetField->order();
                        }
                    }

                    // Actualizar grupo y asignar orden al final
                    $field->updateGroup($newGroupId);
                    $field->updateOrder($maxOrder + 1);
                }

                // Reordenar campos del grupo original (cerrar el hueco)
                $remainingFields = $this->fieldRepository->findByEntityType($entityType);
                $fieldsToReorder = array_filter($remainingFields, function ($remainingField) use ($originalGroupId, $command) {
                    // Filtrar campos del grupo original, excluyendo el campo que se está moviendo
                    if ($originalGroupId === null) {
                        return $remainingField->groupId() === null && $remainingField->id()->toString() !== $command->id;
                    }
                    return $remainingField->groupId()?->toString() === $originalGroupId->toString()
                        && $remainingField->id()->toString() !== $command->id;
                });

                // Reordenar campos que estaban después del que se movió
                foreach ($fieldsToReorder as $remainingField) {
                    if ($remainingField->order() > $originalOrder) {
                        $remainingField->updateOrder($remainingField->order() - 1);
                        $this->fieldRepository->save($remainingField);
                    }
                }
            }
        }

        // Siempre actualizar defaultValue en modo edición (incluso si es null para limpiar el valor)
        $field->updateDefaultValue($command->defaultValue);

        if ($command->isRequired !== null) {
            $field->updateRequired($command->isRequired);
        }

        if ($command->isActive !== null) {
            if ($command->isActive) {
                $field->activate();
            } else {
                $field->deactivate();
            }
        }

        if ($command->validationRules !== null) {
            $field->updateValidationRules($command->validationRules);
        }

        if ($command->order !== null) {
            $field->updateOrder($command->order);
        }

        // Actualizar opciones si se proporcionaron
        if ($command->options !== null && $field->type()->requiresOptions()) {
            $fieldName = FieldName::fromString($field->name()->value());

            // Si la tabla de opciones existe, actualizarla; si no, crearla
            if (!$this->optionsTableManager->tableExists($fieldName)) {
                $this->optionsTableManager->createTable($fieldName);
            } else {
                // Obtener opciones actuales para eliminarlas
                $currentOptions = $this->optionsTableManager->getOptions($fieldName);
                foreach ($currentOptions as $option) {
                    $this->optionsTableManager->deleteOption($fieldName, Uuid::fromString($option['id']));
                }
            }

            // Insertar nuevas opciones
            foreach ($command->options as $index => $option) {
                $this->optionsTableManager->addOption(
                    fieldName: $fieldName,
                    id: Uuid::uuid4(),
                    label: $option['label'],
                    value: $option['value'],
                    order: $option['order'] ?? $index,
                );
            }
        }

        $this->fieldRepository->save($field);

        return CustomFieldData::fromEntity($field);
    }
}
