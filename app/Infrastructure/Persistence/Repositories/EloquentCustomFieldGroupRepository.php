<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\CustomField\Entities\CustomFieldGroup;
use App\Domain\CustomField\Repositories\CustomFieldGroupRepositoryInterface;
use App\Domain\CustomField\ValueObjects\EntityType;
use App\Infrastructure\Persistence\Eloquent\CustomFieldGroupModel;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class EloquentCustomFieldGroupRepository implements CustomFieldGroupRepositoryInterface
{
    public function save(CustomFieldGroup $group): void
    {
        CustomFieldGroupModel::updateOrCreate(
            ['id' => $group->id()->toString()],
            [
                'entity_type' => $group->entityType()->value,
                'name' => $group->name(),
                'order' => $group->order(),
                'updated_at' => $group->updatedAt(),
            ]
        );
    }

    public function findById(UuidInterface $id): ?CustomFieldGroup
    {
        $model = CustomFieldGroupModel::find($id->toString());

        if (!$model) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findByEntityType(EntityType $entityType): array
    {
        $models = CustomFieldGroupModel::where('entity_type', $entityType->value)
            ->orderBy('order')
            ->get();

        return $models->map(fn($model) => $this->toDomain($model))->all();
    }

    public function delete(UuidInterface $id): void
    {
        CustomFieldGroupModel::where('id', $id->toString())->delete();
    }

    public function getNextOrder(EntityType $entityType): int
    {
        return CustomFieldGroupModel::where('entity_type', $entityType->value)
            ->max('order') + 1 ?? 0;
    }

    private function toDomain(CustomFieldGroupModel $model): CustomFieldGroup
    {
        return CustomFieldGroup::reconstruct(
            id: Uuid::fromString($model->id),
            entityType: EntityType::from($model->entity_type),
            name: $model->name,
            order: $model->order,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at),
        );
    }
}
