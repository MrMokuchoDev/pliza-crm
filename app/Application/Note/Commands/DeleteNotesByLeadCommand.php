<?php

declare(strict_types=1);

namespace App\Application\Note\Commands;

/**
 * Comando para eliminar todas las notas de un lead.
 */
readonly class DeleteNotesByLeadCommand
{
    public function __construct(
        public string $leadId,
    ) {}
}
