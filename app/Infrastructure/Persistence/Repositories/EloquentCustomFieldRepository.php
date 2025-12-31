<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\CustomField\Entities\CustomField;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use App\Domain\CustomField\ValueObjects\EntityType;
use App\Domain\CustomField\ValueObjects\FieldName;
use App\Domain\CustomField\ValueObjects\FieldType;
use App\Domain\CustomField\ValueObjects\ValidationRules;
use App\Infrastructure\Persistence\Eloquent\CustomFieldModel;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class EloquentCustomFieldRepository implements CustomFieldRepositoryInterface
{
    public function save(CustomField $field): void
    {
        CustomFieldModel::updateOrCreate(
            ['id' => $field->id()->toString()],
            [
                'entity_type' => $field->entityType()->value,
                'group_id' => $field->groupId()?->toString(),
                'name' => $field->name()->value(),
                'label' => $field->label(),
                'type' => $field->type()->value,
                'default_value' => $field->defaultValue(),
                'is_required' => $field->isRequired(),
                'validation_rules' => $field->validationRules()->toArray(),
                'order' => $field->order(),
                'is_active' => $field->isActive(),
            ]
        );
    }

    public function findById(UuidInterface $id): ?CustomField
    {
        $model = CustomFieldModel::find($id->toString());

        if (!$model) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findByName(FieldName $name): ?CustomField
    {
        $model = CustomFieldModel::where('name', $name->value())->first();

        if (!$model) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findByEntityType(EntityType $entityType, bool $activeOnly = true): array
    {
        $query = CustomFieldModel::where('entity_type', $entityType->value);

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        $models = $query->orderBy('order')->get();

        return $models->map(fn($model) => $this->toDomain($model))->all();
    }

    public function findByGroup(UuidInterface $groupId): array
    {
        $models = CustomFieldModel::where('group_id', $groupId->toString())
            ->orderBy('order')
            ->get();

        return $models->map(fn($model) => $this->toDomain($model))->all();
    }

    public function delete(UuidInterface $id): void
    {
        CustomFieldModel::where('id', $id->toString())->delete();
    }

    public function getNextOrder(EntityType $entityType, ?UuidInterface $groupId = null): int
    {
        $query = CustomFieldModel::where('entity_type', $entityType->value);

        if ($groupId) {
            $query->where('group_id', $groupId->toString());
        }

        $maxOrder = $query->max('order');

        return ($maxOrder ?? 0) + 1;
    }

    private function toDomain(CustomFieldModel $model): CustomField
    {
        return CustomField::reconstruct(
            id: Uuid::fromString($model->id),
            entityType: EntityType::from($model->entity_type),
            groupId: $model->group_id ? Uuid::fromString($model->group_id) : null,
            name: FieldName::fromString($model->name),
            label: $model->label,
            type: FieldType::from($model->type),
            defaultValue: $model->default_value,
            isRequired: $model->is_required,
            validationRules: ValidationRules::fromArray($model->validation_rules ?? []),
            order: $model->order,
            isActive: $model->is_active,
            isSystem: $model->is_system ?? false,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at),
        );
    }
}
