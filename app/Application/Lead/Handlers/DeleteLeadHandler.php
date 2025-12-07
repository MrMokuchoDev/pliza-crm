<?php

declare(strict_types=1);

namespace App\Application\Lead\Handlers;

use App\Application\Deal\Services\DealService;
use App\Application\DealComment\Services\DealCommentService;
use App\Application\Lead\Commands\DeleteLeadCommand;
use App\Application\Note\Services\NoteService;
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
        private readonly DealCommentService $dealCommentService,
        private readonly DealService $dealService,
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
            $dealIds = $this->dealService->getDealIdsByLeadId($command->leadId);

            // Eliminar comentarios de negocios usando el servicio
            if ($dealIds->isNotEmpty()) {
                $deletedCounts['comments'] = $this->dealCommentService->deleteByDealIds($dealIds);
            }

            // Eliminar negocios asociados usando DealService
            $deletedCounts['deals'] = $this->dealService->deleteByLeadId($command->leadId);

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
