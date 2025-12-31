<?php

declare(strict_types=1);

namespace App\Domain\CustomField\Repositories;

use App\Domain\CustomField\Entities\CustomField;
use App\Domain\CustomField\ValueObjects\EntityType;
use App\Domain\CustomField\ValueObjects\FieldName;
use Ramsey\Uuid\UuidInterface;

interface CustomFieldRepositoryInterface
{
    public function save(CustomField $field): void;

    public function findById(UuidInterface $id): ?CustomField;

    public function findByName(FieldName $name): ?CustomField;

    public function findByEntityType(EntityType $entityType, bool $activeOnly = true): array;

    public function findByGroup(UuidInterface $groupId): array;

    public function delete(UuidInterface $id): void;

    public function getNextOrder(EntityType $entityType, ?UuidInterface $groupId = null): int;
}
