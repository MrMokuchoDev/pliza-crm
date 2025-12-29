<?php

declare(strict_types=1);

namespace App\Application\CustomField\Queries;

final class GetCustomFieldGroupsQuery
{
    public function __construct(
        public readonly string $entityType,
    ) {}
}
