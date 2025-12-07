<?php

declare(strict_types=1);

namespace App\Application\Note\Commands;

/**
 * Comando para eliminar una nota.
 */
readonly class DeleteNoteCommand
{
    public function __construct(
        public string $noteId,
    ) {}
}
