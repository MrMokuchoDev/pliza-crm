<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\Note\Commands\CreateNoteCommand;
use App\Application\Note\Commands\DeleteNoteCommand;
use App\Application\Note\Commands\DeleteNotesByLeadCommand;
use App\Application\Note\Commands\UpdateNoteCommand;
use App\Application\Note\DTOs\NoteData;
use App\Application\Note\Handlers\CreateNoteHandler;
use App\Application\Note\Handlers\DeleteNoteHandler;
use App\Application\Note\Handlers\DeleteNotesByLeadHandler;
use App\Application\Note\Handlers\UpdateNoteHandler;
use App\Infrastructure\Persistence\Eloquent\NoteModel;

/**
 * Servicio de aplicación para gestionar Notas.
 * Orquesta los comandos y handlers.
 */
class NoteService
{
    public function __construct(
        private readonly CreateNoteHandler $createHandler,
        private readonly UpdateNoteHandler $updateHandler,
        private readonly DeleteNoteHandler $deleteHandler,
        private readonly DeleteNotesByLeadHandler $deleteByLeadHandler,
    ) {}

    /**
     * Crear una nueva nota.
     */
    public function create(NoteData $data): NoteModel
    {
        $command = new CreateNoteCommand($data);

        return $this->createHandler->handle($command);
    }

    /**
     * Actualizar una nota existente.
     */
    public function update(string $noteId, NoteData $data): ?NoteModel
    {
        $command = new UpdateNoteCommand($noteId, $data);

        return $this->updateHandler->handle($command);
    }

    /**
     * Eliminar una nota.
     */
    public function delete(string $noteId): bool
    {
        $command = new DeleteNoteCommand($noteId);

        return $this->deleteHandler->handle($command);
    }

    /**
     * Eliminar todas las notas de un lead.
     */
    public function deleteByLeadId(string $leadId): int
    {
        $command = new DeleteNotesByLeadCommand($leadId);

        return $this->deleteByLeadHandler->handle($command);
    }

    /**
     * Buscar una nota por ID.
     */
    public function find(string $noteId): ?NoteModel
    {
        return NoteModel::find($noteId);
    }
}
