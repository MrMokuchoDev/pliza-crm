<?php

declare(strict_types=1);

namespace App\Application\CustomField\Commands;

final class ReorderCustomFieldsCommand
{
    /**
     * @param array<string, int> $order Array de [field_id => order_position]
     */
    public function __construct(
        public readonly array $order,
    ) {}
}
