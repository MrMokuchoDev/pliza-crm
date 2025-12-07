<?php

declare(strict_types=1);

namespace App\Application\Lead\Commands;

/**
 * Comando para eliminar un Lead y sus relaciones en cascada.
 */
readonly class DeleteLeadCommand
{
    public function __construct(
        public string $leadId,
    ) {}
}
