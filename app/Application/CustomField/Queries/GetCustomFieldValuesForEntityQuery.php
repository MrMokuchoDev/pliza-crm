<?php

declare(strict_types=1);

namespace App\Application\CustomField\Queries;

final class GetCustomFieldValuesForEntityQuery
{
    public function __construct(
        public readonly string $entityType,
        public readonly string $entityId,
    ) {}
}
