<?php

declare(strict_types=1);

namespace App\Application\CustomField\Commands;

final class DeleteCustomFieldOptionCommand
{
    public function __construct(
        public readonly string $id,
        public readonly string $customFieldId,
    ) {}
}
