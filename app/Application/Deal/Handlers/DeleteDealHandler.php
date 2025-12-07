<?php

declare(strict_types=1);

namespace App\Application\Deal\Handlers;

use App\Application\Deal\Commands\DeleteDealCommand;
use App\Application\DealComment\Services\DealCommentService;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use Illuminate\Support\Facades\DB;

/**
 * Handler para eliminar un Deal y sus comentarios.
 */
class DeleteDealHandler
{
    public function __construct(
        private readonly DealCommentService $dealCommentService,
    ) {}

    /**
     * @return array{success: bool, deleted_comments: int}
     */
    public function handle(DeleteDealCommand $command): array
    {
        $deal = DealModel::find($command->dealId);

        if (! $deal) {
            return [
                'success' => false,
                'deleted_comments' => 0,
            ];
        }

        $deletedComments = 0;

        DB::transaction(function () use ($command, &$deletedComments) {
            // Eliminar comentarios del deal usando el servicio
            $deletedComments = $this->dealCommentService->deleteByDealId($command->dealId);

            // Eliminar el deal
            DealModel::destroy($command->dealId);
        });

        return [
            'success' => true,
            'deleted_comments' => $deletedComments,
        ];
    }
}
