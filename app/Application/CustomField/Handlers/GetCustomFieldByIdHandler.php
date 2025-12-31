<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\DTOs\CustomFieldData;
use App\Application\CustomField\Queries\GetCustomFieldByIdQuery;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use Ramsey\Uuid\Uuid;

final class GetCustomFieldByIdHandler
{
    public function __construct(
        private readonly CustomFieldRepositoryInterface $fieldRepository,
    ) {}

    public function handle(GetCustomFieldByIdQuery $query): ?CustomFieldData
    {
        $field = $this->fieldRepository->findById(Uuid::fromString($query->id));

        if (!$field) {
            return null;
        }

        return CustomFieldData::fromEntity($field);
    }
}
