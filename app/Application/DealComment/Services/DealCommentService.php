<?php

declare(strict_types=1);

namespace App\Application\DealComment\Services;

use App\Application\DealComment\Commands\CreateDealCommentCommand;
use App\Application\DealComment\Commands\DeleteCommentsByDealCommand;
use App\Application\DealComment\Commands\DeleteCommentsByDealsCommand;
use App\Application\DealComment\Commands\DeleteDealCommentCommand;
use App\Application\DealComment\Commands\UpdateDealCommentCommand;
use App\Application\DealComment\DTOs\DealCommentData;
use App\Application\DealComment\Handlers\CreateDealCommentHandler;
use App\Application\DealComment\Handlers\DeleteCommentsByDealHandler;
use App\Application\DealComment\Handlers\DeleteCommentsByDealsHandler;
use App\Application\DealComment\Handlers\DeleteDealCommentHandler;
use App\Application\DealComment\Handlers\FindDealCommentHandler;
use App\Application\DealComment\Handlers\UpdateDealCommentHandler;
use App\Application\DealComment\Queries\FindDealCommentQuery;
use App\Infrastructure\Persistence\Eloquent\DealCommentModel;
use Illuminate\Support\Collection;

/**
 * Servicio de aplicación para gestionar comentarios de negocios.
 * Orquesta los comandos y handlers.
 */
class DealCommentService
{
    public function __construct(
        private readonly CreateDealCommentHandler $createHandler,
        private readonly UpdateDealCommentHandler $updateHandler,
        private readonly DeleteDealCommentHandler $deleteHandler,
        private readonly DeleteCommentsByDealHandler $deleteByDealHandler,
        private readonly DeleteCommentsByDealsHandler $deleteByDealsHandler,
        private readonly FindDealCommentHandler $findHandler,
    ) {}

    /**
     * Buscar un comentario por ID.
     */
    public function find(string $commentId): ?DealCommentModel
    {
        $query = new FindDealCommentQuery($commentId);

        return $this->findHandler->handle($query);
    }

    /**
     * Crear un nuevo comentario.
     */
    public function create(DealCommentData $data): DealCommentModel
    {
        $command = new CreateDealCommentCommand($data);

        return $this->createHandler->handle($command);
    }

    /**
     * Actualizar un comentario existente.
     */
    public function update(string $commentId, DealCommentData $data): ?DealCommentModel
    {
        $command = new UpdateDealCommentCommand($commentId, $data);

        return $this->updateHandler->handle($command);
    }

    /**
     * Eliminar un comentario.
     */
    public function delete(string $commentId): bool
    {
        $command = new DeleteDealCommentCommand($commentId);

        return $this->deleteHandler->handle($command);
    }

    /**
     * Eliminar todos los comentarios de un negocio.
     */
    public function deleteByDealId(string $dealId): int
    {
        $command = new DeleteCommentsByDealCommand($dealId);

        return $this->deleteByDealHandler->handle($command);
    }

    /**
     * Eliminar comentarios de múltiples negocios.
     * Usado en eliminación en cascada de leads.
     *
     * @param  Collection<int, string>|array<string>  $dealIds
     */
    public function deleteByDealIds(Collection|array $dealIds): int
    {
        $command = new DeleteCommentsByDealsCommand($dealIds);

        return $this->deleteByDealsHandler->handle($command);
    }
}
