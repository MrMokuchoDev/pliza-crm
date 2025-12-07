<?php

declare(strict_types=1);

namespace App\Application\Note\Handlers;

use App\Application\Note\Commands\DeleteNoteCommand;
use App\Infrastructure\Persistence\Eloquent\NoteModel;

/**
 * Handler para eliminar una nota.
 */
class DeleteNoteHandler
{
    public function handle(DeleteNoteCommand $command): bool
    {
        $note = NoteModel::find($command->noteId);

        if (! $note) {
            return false;
        }

        return (bool) $note->delete();
    }
}
