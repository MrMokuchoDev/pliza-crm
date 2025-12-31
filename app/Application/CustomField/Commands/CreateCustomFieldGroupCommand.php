<?php

declare(strict_types=1);

namespace App\Application\CustomField\Commands;

final class CreateCustomFieldGroupCommand
{
    public function __construct(
        public readonly string $entityType,
        public readonly string $name,
        public readonly ?int $order = null,
    ) {}
}
