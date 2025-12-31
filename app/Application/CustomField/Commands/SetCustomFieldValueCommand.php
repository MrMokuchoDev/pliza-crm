<?php

declare(strict_types=1);

namespace App\Application\CustomField\Commands;

final class SetCustomFieldValueCommand
{
    public function __construct(
        public readonly string $customFieldId,
        public readonly string $entityType,
        public readonly string $entityId,
        public readonly mixed $value,
    ) {}
}
