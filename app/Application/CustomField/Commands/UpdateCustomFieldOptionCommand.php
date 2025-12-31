<?php

declare(strict_types=1);

namespace App\Application\CustomField\Commands;

final class UpdateCustomFieldOptionCommand
{
    public function __construct(
        public readonly string $id,
        public readonly string $customFieldId,
        public readonly ?string $label = null,
        public readonly ?string $value = null,
        public readonly ?int $order = null,
    ) {}
}
