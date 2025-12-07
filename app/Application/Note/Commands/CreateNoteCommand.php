<?php

declare(strict_types=1);

namespace App\Application\Note\Commands;

use App\Application\Note\DTOs\NoteData;

/**
 * Comando para crear una nueva nota.
 */
readonly class CreateNoteCommand
{
    public function __construct(
        public NoteData $data,
    ) {}
}
