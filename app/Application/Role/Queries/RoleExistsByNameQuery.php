<?php

declare(strict_types=1);

namespace App\Application\Role\Queries;

final readonly class RoleExistsByNameQuery
{
    public function __construct(
        public string $name,
        public ?string $excludeId = null,
    ) {}
}
