<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\Commands\SetCustomFieldValueCommand;
use App\Application\CustomField\DTOs\CustomFieldValueData;
use App\Domain\CustomField\Entities\CustomFieldValue;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use App\Domain\CustomField\Repositories\CustomFieldValueRepositoryInterface;
use App\Domain\CustomField\Services\CustomFieldValueNormalizer;
use App\Domain\CustomField\ValueObjects\EntityType;
use Ramsey\Uuid\Uuid;

final class SetCustomFieldValueHandler
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $fieldRepository,
        private readonly CustomFieldValueRepositoryInterface $valueRepository,
        private readonly CustomFieldValueNormalizer $valueNormalizer,
    ) {}

    public function handle(SetCustomFieldValueCommand $command): CustomFieldValueData
    {
        $customFieldId = Uuid::fromString($command->customFieldId);
        $entityType = EntityType::from($command->entityType);
        $entityId = Uuid::fromString($command->entityId);

        // Verificar que el campo existe
        $field = $this->fieldRepository->findById($customFieldId);
        if (!$field) {
            throw new \DomainException("Custom field not found: {$command->customFieldId}");
        }

        // Verificar que el tipo de entidad coincide (usando método de dominio)
        if (!$field->acceptsEntityType($entityType)) {
            throw new \DomainException("Entity type mismatch");
        }

        // Buscar valor existente
        $existingValue = $this->valueRepository->findByFieldAndEntity(
            $customFieldId,
            $entityType,
            $entityId
        );

        // Normalizar valor usando servicio de dominio
        $normalizedValue = $this->valueNormalizer->normalize($field, $command->value);

        if ($existingValue) {
            // Actualizar valor existente
            $existingValue->changeValue($normalizedValue);
            $this->valueRepository->save($existingValue);
            return CustomFieldValueData::fromEntity($existingValue);
        }

        // Crear nuevo valor
        $value = CustomFieldValue::create(
            id: Uuid::uuid4(),
            customFieldId: $customFieldId,
            entityType: $entityType,
            entityId: $entityId,
            value: $normalizedValue,
        );

        $this->valueRepository->save($value);

        return CustomFieldValueData::fromEntity($value);
    }
}
