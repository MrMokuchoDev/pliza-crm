<?php

declare(strict_types=1);

namespace App\Application\Note\Handlers;

use App\Application\Note\Commands\UpdateNoteCommand;
use App\Infrastructure\Persistence\Eloquent\NoteModel;

/**
 * Handler para actualizar una nota existente.
 */
class UpdateNoteHandler
{
    public function handle(UpdateNoteCommand $command): ?NoteModel
    {
        $note = NoteModel::find($command->noteId);

        if (! $note) {
            return null;
        }

        $note->update($command->data->toArray());

        return $note->fresh();
    }
}
