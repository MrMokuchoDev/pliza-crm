<?php

declare(strict_types=1);

namespace App\Application\Note\Commands;

use App\Application\Note\DTOs\NoteData;

/**
 * Comando para actualizar una nota existente.
 */
readonly class UpdateNoteCommand
{
    public function __construct(
        public string $noteId,
        public NoteData $data,
    ) {}
}
