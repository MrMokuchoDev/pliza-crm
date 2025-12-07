<?php

declare(strict_types=1);

namespace App\Application\Deal\Handlers;

use App\Application\Deal\Commands\DeleteDealCommand;
use App\Infrastructure\Persistence\Eloquent\DealCommentModel;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use Illuminate\Support\Facades\DB;

/**
 * Handler para eliminar un Deal y sus comentarios.
 */
class DeleteDealHandler
{
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
            // Eliminar comentarios del deal
            $deletedComments = DealCommentModel::where('deal_id', $command->dealId)->count();
            DealCommentModel::where('deal_id', $command->dealId)->delete();

            // Eliminar el deal
            DealModel::destroy($command->dealId);
        });

        return [
            'success' => true,
            'deleted_comments' => $deletedComments,
        ];
    }
}
