<?php

declare(strict_types=1);

namespace App\Application\CustomField\Queries;

final class GetCustomFieldsByEntityQuery
{
    public function __construct(
        public readonly string $entityType,
        public readonly bool $activeOnly = true,
        public readonly ?string $groupId = null,
    ) {}
}
