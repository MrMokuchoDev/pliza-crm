<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\Commands\CreateCustomFieldCommand;
use App\Application\CustomField\DTOs\CustomFieldData;
use App\Domain\CustomField\Entities\CustomField;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use App\Domain\CustomField\Services\CustomFieldNameGeneratorInterface;
use App\Domain\CustomField\Services\CustomFieldOptionsTableManagerInterface;
use App\Domain\CustomField\ValueObjects\EntityType;
use App\Domain\CustomField\ValueObjects\FieldType;
use App\Domain\CustomField\ValueObjects\ValidationRules;
use Ramsey\Uuid\Uuid;

final class CreateCustomFieldHandler
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $fieldRepository,
        private readonly CustomFieldNameGeneratorInterface $nameGenerator,
        private readonly CustomFieldOptionsTableManagerInterface $optionsTableManager,
    ) {}

    public function handle(CreateCustomFieldCommand $command): CustomFieldData
    {
        $entityType = EntityType::from($command->entityType);
        $fieldType = FieldType::from($command->type);

        // Generar nombre único incremental (cf_lead_1, cf_deal_2, etc.)
        $fieldName = $this->nameGenerator->generateNext($entityType);

        $groupId = $command->groupId ? Uuid::fromString($command->groupId) : null;

        // Calcular orden: si se especifica usar ese, si no calcular el siguiente orden DEL GRUPO
        $order = $command->order ?? $this->fieldRepository->getNextOrder($entityType, $groupId);

        $field = CustomField::create(
            id: Uuid::uuid4(),
            entityType: $entityType,
            name: $fieldName,
            label: $command->label,
            type: $fieldType,
            groupId: $groupId,
            defaultValue: $command->defaultValue,
            isRequired: $command->isRequired,
            validationRules: ValidationRules::fromArray($command->validationRules ?? []),
            order: $order,
        );

        $this->fieldRepository->save($field);

        // Si el tipo de campo requiere opciones, crear la tabla dinámica
        if ($fieldType->requiresOptions()) {
            $this->optionsTableManager->createTable($fieldName);

            // Si se proporcionaron opciones iniciales, insertarlas
            if (!empty($command->options)) {
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
        }

        return CustomFieldData::fromEntity($field);
    }
}
