<?php

declare(strict_types=1);

namespace App\Application\CustomField\Commands;

final class UpdateCustomFieldCommand
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $label = null,
        public readonly ?string $groupId = null,
        public readonly ?string $defaultValue = null,
        public readonly ?bool $isRequired = null,
        public readonly ?bool $isActive = null,
        public readonly ?array $validationRules = null,
        public readonly ?int $order = null,
        public readonly ?array $options = null,
    ) {}
}
