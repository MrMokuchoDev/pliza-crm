<?php

declare(strict_types=1);

namespace App\Application\Lead\Commands;

use App\Application\Lead\DTOs\LeadData;

/**
 * Comando para actualizar un Lead existente.
 */
readonly class UpdateLeadCommand
{
    public function __construct(
        public string $leadId,
        public LeadData $data,
    ) {}
}
