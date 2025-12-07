<?php

declare(strict_types=1);

namespace App\Application\Lead\Handlers;

use App\Application\Lead\Commands\DeleteLeadCommand;
use App\Application\Note\Services\NoteService;
use App\Infrastructure\Persistence\Eloquent\DealCommentModel;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use Illuminate\Support\Facades\DB;

/**
 * Handler para eliminar un Lead y sus relaciones en cascada.
 *
 * Elimina en orden:
 * 1. Comentarios de negocios
 * 2. Negocios
 * 3. Notas
 * 4. Lead
 */
class DeleteLeadHandler
{
    public function __construct(
        private readonly NoteService $noteService,
    ) {}

    /**
     * @return array{success: bool, deleted: array{comments: int, deals: int, notes: int}}
     */
    public function handle(DeleteLeadCommand $command): array
    {
        $lead = LeadModel::find($command->leadId);

        if (! $lead) {
            return [
                'success' => false,
                'deleted' => ['comments' => 0, 'deals' => 0, 'notes' => 0],
            ];
        }

        $deletedCounts = [
            'comments' => 0,
            'deals' => 0,
            'notes' => 0,
        ];

        DB::transaction(function () use ($command, &$deletedCounts) {
            // Obtener IDs de negocios para eliminar sus comentarios
            $dealIds = DealModel::where('lead_id', $command->leadId)->pluck('id');

            // Eliminar comentarios de negocios
            if ($dealIds->isNotEmpty()) {
                $deletedCounts['comments'] = DealCommentModel::whereIn('deal_id', $dealIds)->count();
                DealCommentModel::whereIn('deal_id', $dealIds)->delete();
            }

            // Eliminar negocios asociados
            $deletedCounts['deals'] = DealModel::where('lead_id', $command->leadId)->count();
            DealModel::where('lead_id', $command->leadId)->delete();

            // Eliminar notas asociadas usando NoteService
            $deletedCounts['notes'] = $this->noteService->deleteByLeadId($command->leadId);

            // Eliminar el contacto
            LeadModel::destroy($command->leadId);
        });

        return [
            'success' => true,
            'deleted' => $deletedCounts,
        ];
    }
}
