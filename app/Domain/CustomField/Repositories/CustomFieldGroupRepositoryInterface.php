<?php

declare(strict_types=1);

namespace App\Domain\CustomField\Repositories;

use App\Domain\CustomField\Entities\CustomFieldGroup;
use App\Domain\CustomField\ValueObjects\EntityType;
use Ramsey\Uuid\UuidInterface;

interface CustomFieldGroupRepositoryInterface
{
    public function save(CustomFieldGroup $group): void;

    public function findById(UuidInterface $id): ?CustomFieldGroup;

    public function findByEntityType(EntityType $entityType): array;

    public function delete(UuidInterface $id): void;

    public function getNextOrder(EntityType $entityType): int;
}
