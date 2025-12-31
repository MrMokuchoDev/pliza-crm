<?php

declare(strict_types=1);

namespace App\Domain\CustomField\Repositories;

use App\Domain\CustomField\Entities\CustomFieldValue;
use App\Domain\CustomField\ValueObjects\EntityType;
use Ramsey\Uuid\UuidInterface;

interface CustomFieldValueRepositoryInterface
{
    public function save(CustomFieldValue $value): void;

    public function findById(UuidInterface $id): ?CustomFieldValue;

    public function findByEntity(EntityType $entityType, UuidInterface $entityId): array;

    public function findByFieldAndEntity(UuidInterface $customFieldId, EntityType $entityType, UuidInterface $entityId): ?CustomFieldValue;

    public function delete(UuidInterface $id): void;

    public function deleteByEntity(EntityType $entityType, UuidInterface $entityId): void;

    public function deleteByField(UuidInterface $customFieldId): void;

    /**
     * Guardar múltiples valores de campos personalizados para una entidad
     */
    public function saveBulk(EntityType $entityType, UuidInterface $entityId, array $values): void;

    /**
     * Contar valores de un campo personalizado
     */
    public function countByField(UuidInterface $customFieldId): int;
}
