<?php

declare(strict_types=1);

namespace App\Application\CustomField\Commands;

final class UpdateCustomFieldGroupCommand
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?int $order = null,
    ) {}
}
