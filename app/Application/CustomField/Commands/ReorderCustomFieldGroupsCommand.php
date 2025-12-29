<?php

declare(strict_types=1);

namespace App\Application\CustomField\Commands;

final class ReorderCustomFieldGroupsCommand
{
    /**
     * @param array<string> $groupIds Array de IDs en el nuevo orden
     */
    public function __construct(
        public readonly array $groupIds,
    ) {}
}
