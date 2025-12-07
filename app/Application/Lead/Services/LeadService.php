<?php

declare(strict_types=1);

namespace App\Application\Lead\Services;

use App\Application\Lead\Commands\CreateLeadCommand;
use App\Application\Lead\Commands\DeleteLeadCommand;
use App\Application\Lead\Commands\UpdateLeadCommand;
use App\Application\Lead\DTOs\LeadData;
use App\Application\Lead\Handlers\CreateLeadHandler;
use App\Application\Lead\Handlers\DeleteLeadHandler;
use App\Application\Lead\Handlers\UpdateLeadHandler;
use App\Infrastructure\Persistence\Eloquent\LeadModel;

/**
 * Servicio de aplicación para gestionar Leads.
 * Orquesta los comandos y handlers.
 */
class LeadService
{
    public function __construct(
        private readonly CreateLeadHandler $createHandler,
        private readonly UpdateLeadHandler $updateHandler,
        private readonly DeleteLeadHandler $deleteHandler,
    ) {}

    /**
     * Crear un nuevo Lead.
     */
    public function create(LeadData $data): LeadModel
    {
        $command = new CreateLeadCommand($data);

        return $this->createHandler->handle($command);
    }

    /**
     * Actualizar un Lead existente.
     */
    public function update(string $leadId, LeadData $data): ?LeadModel
    {
        $command = new UpdateLeadCommand($leadId, $data);

        return $this->updateHandler->handle($command);
    }

    /**
     * Eliminar un Lead y sus relaciones en cascada.
     *
     * @return array{success: bool, deleted: array{comments: int, deals: int, notes: int}}
     */
    public function delete(string $leadId): array
    {
        $command = new DeleteLeadCommand($leadId);

        return $this->deleteHandler->handle($command);
    }

    /**
     * Obtener conteos de relaciones para mostrar en confirmación de eliminación.
     *
     * @return array{deals: int, notes: int}
     */
    public function getRelatedCounts(string $leadId): array
    {
        $lead = LeadModel::withCount(['deals', 'notes'])->find($leadId);

        if (! $lead) {
            return ['deals' => 0, 'notes' => 0];
        }

        return [
            'deals' => $lead->deals_count,
            'notes' => $lead->notes_count,
        ];
    }

    /**
     * Obtener nombre para mostrar del Lead.
     */
    public function getDisplayName(string $leadId): string
    {
        $lead = LeadModel::find($leadId);

        if (! $lead) {
            return 'Sin nombre';
        }

        return $lead->name ?? $lead->email ?? $lead->phone ?? 'Sin nombre';
    }

    /**
     * Buscar un Lead por ID.
     */
    public function find(string $leadId): ?LeadModel
    {
        return LeadModel::find($leadId);
    }

    /**
     * Verificar si un Lead existe.
     */
    public function exists(string $leadId): bool
    {
        return LeadModel::where('id', $leadId)->exists();
    }
}
