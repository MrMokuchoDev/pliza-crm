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

        if ($command->groupId !== null) {
            if ($command->groupId === '') {
                $field->removeGroup();
            } else {
                $field->updateGroup(Uuid::fromString($command->groupId));
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
