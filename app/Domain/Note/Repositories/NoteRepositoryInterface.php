<?php

declare(strict_types=1);

namespace App\Domain\Note\Repositories;

use App\Domain\Note\Entities\Note;

interface NoteRepositoryInterface
{
    public function findById(string $id): ?Note;

    /** @return Note[] */
    public function findByLead(string $leadId): array;

    public function save(Note $note): void;

    public function delete(string $id): void;
}
