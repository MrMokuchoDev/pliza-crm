<?php

declare(strict_types=1);

namespace App\Application\Note\Handlers;

use App\Application\Note\Commands\DeleteNotesByLeadCommand;
use App\Infrastructure\Persistence\Eloquent\NoteModel;

/**
 * Handler para eliminar todas las notas de un lead.
 */
class DeleteNotesByLeadHandler
{
    /**
     * @return int Número de notas eliminadas
     */
    public function handle(DeleteNotesByLeadCommand $command): int
    {
        return NoteModel::where('lead_id', $command->leadId)->delete();
    }
}
