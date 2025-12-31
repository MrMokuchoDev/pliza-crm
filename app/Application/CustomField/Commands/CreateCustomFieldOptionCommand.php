<?php

declare(strict_types=1);

namespace App\Application\CustomField\Commands;

final class CreateCustomFieldOptionCommand
{
    public function __construct(
        public readonly string $customFieldId,
        public readonly string $label,
        public readonly string $value,
        public readonly ?int $order = null,
    ) {}
}
