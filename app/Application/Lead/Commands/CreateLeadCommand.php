<?php

declare(strict_types=1);

namespace App\Application\Lead\Commands;

use App\Application\Lead\DTOs\LeadData;

/**
 * Comando para crear un nuevo Lead.
 */
readonly class CreateLeadCommand
{
    public function __construct(
        public LeadData $data,
    ) {}
}
