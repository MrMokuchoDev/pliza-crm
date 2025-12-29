<?php

declare(strict_types=1);

namespace App\Application\CustomField\Queries;

final class GetCustomFieldGroupByIdQuery
{
    public function __construct(
        public readonly string $id,
    ) {}
}
