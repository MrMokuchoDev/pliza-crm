<?php

declare(strict_types=1);

namespace App\Application\Note\Handlers;

use App\Application\Note\Commands\CreateNoteCommand;
use App\Infrastructure\Persistence\Eloquent\NoteModel;

/**
 * Handler para crear una nueva nota.
 */
class CreateNoteHandler
{
    public function handle(CreateNoteCommand $command): NoteModel
    {
        return NoteModel::create($command->data->toArray());
    }
}
