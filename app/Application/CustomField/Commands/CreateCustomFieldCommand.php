<?php

declare(strict_types=1);

namespace App\Application\CustomField\Commands;

final class CreateCustomFieldCommand
{
    public function __construct(
        public readonly string $entityType,
        public readonly string $label,
        public readonly string $type,
        public readonly ?string $groupId = null,
        public readonly ?string $defaultValue = null,
        public readonly bool $isRequired = false,
        public readonly ?array $validationRules = null,
        public readonly ?int $order = null,
        public readonly array $options = [],
    ) {}
}
