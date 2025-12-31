<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\CustomField\Entities\CustomFieldValue;
use App\Domain\CustomField\Repositories\CustomFieldValueRepositoryInterface;
use App\Domain\CustomField\ValueObjects\EntityType;
use App\Infrastructure\Persistence\Eloquent\CustomFieldValueModel;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class EloquentCustomFieldValueRepository implements CustomFieldValueRepositoryInterface
{
    public function save(CustomFieldValue $value): void
    {
        CustomFieldValueModel::updateOrCreate(
            [
                'custom_field_id' => $value->customFieldId()->toString(),
                'entity_type' => $value->entityType()->value,
                'entity_id' => $value->entityId()->toString(),
            ],
            [
                'id' => $value->id()->toString(),
                'value' => $value->value(),
            ]
        );
    }

    public function findById(UuidInterface $id): ?CustomFieldValue
    {
        $model = CustomFieldValueModel::find($id->toString());

        if (!$model) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findByEntity(EntityType $entityType, UuidInterface $entityId): array
    {
        $models = CustomFieldValueModel::where('entity_type', $entityType->value)
            ->where('entity_id', $entityId->toString())
            ->get();

        return $models->map(fn($model) => $this->toDomain($model))->all();
    }

    public function findByFieldAndEntity(UuidInterface $customFieldId, EntityType $entityType, UuidInterface $entityId): ?CustomFieldValue
    {
        $model = CustomFieldValueModel::where('custom_field_id', $customFieldId->toString())
            ->where('entity_type', $entityType->value)
            ->where('entity_id', $entityId->toString())
            ->first();

        if (!$model) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function delete(UuidInterface $id): void
    {
        CustomFieldValueModel::where('id', $id->toString())->delete();
    }

    public function deleteByEntity(EntityType $entityType, UuidInterface $entityId): void
    {
        CustomFieldValueModel::where('entity_type', $entityType->value)
            ->where('entity_id', $entityId->toString())
            ->delete();
    }

    public function deleteByField(UuidInterface $customFieldId): void
    {
        CustomFieldValueModel::where('custom_field_id', $customFieldId->toString())
            ->delete();
    }

    public function saveBulk(EntityType $entityType, UuidInterface $entityId, array $values): void
    {
        foreach ($values as $customFieldId => $value) {
            $fieldId = is_string($customFieldId) ? Uuid::fromString($customFieldId) : $customFieldId;

            $existingValue = $this->findByFieldAndEntity($fieldId, $entityType, $entityId);

            if ($existingValue) {
                $existingValue->changeValue($value);
                $this->save($existingValue);
            } else {
                $newValue = CustomFieldValue::create(
                    id: Uuid::uuid4(),
                    customFieldId: $fieldId,
                    entityType: $entityType,
                    entityId: $entityId,
                    value: $value,
                );
                $this->save($newValue);
            }
        }
    }

    private function toDomain(CustomFieldValueModel $model): CustomFieldValue
    {
        return CustomFieldValue::reconstruct(
            id: Uuid::fromString($model->id),
            customFieldId: Uuid::fromString($model->custom_field_id),
            entityType: EntityType::from($model->entity_type),
            entityId: Uuid::fromString($model->entity_id),
            value: $model->value,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at),
        );
    }
}
