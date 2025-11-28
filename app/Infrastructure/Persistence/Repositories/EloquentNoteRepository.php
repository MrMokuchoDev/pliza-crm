<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Note\Entities\Note;
use App\Domain\Note\Repositories\NoteRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\NoteModel;

class EloquentNoteRepository implements NoteRepositoryInterface
{
    public function findById(string $id): ?Note
    {
        $model = NoteModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByLead(string $leadId): array
    {
        return NoteModel::where('lead_id', $leadId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->toArray();
    }

    public function save(Note $note): void
    {
        NoteModel::updateOrCreate(
            ['id' => $note->id],
            [
                'lead_id' => $note->leadId,
                'content' => $note->content,
            ]
        );
    }

    public function delete(string $id): void
    {
        NoteModel::destroy($id);
    }

    private function toDomain(NoteModel $model): Note
    {
        return new Note(
            id: $model->id,
            leadId: $model->lead_id,
            content: $model->content,
            createdAt: $model->created_at ? \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()) : null,
            updatedAt: $model->updated_at ? \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()) : null,
        );
    }
}
