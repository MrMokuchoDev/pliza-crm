<?php

declare(strict_types=1);

namespace App\Application\Lead\Handlers;

use App\Application\Deal\DTOs\DealData;
use App\Application\Deal\Services\DealService;
use App\Application\Lead\Commands\CaptureLeadCommand;
use App\Application\Lead\DTOs\LeadData;
use App\Application\Lead\Queries\FindLeadByContactQuery;
use App\Application\SalePhase\Services\SalePhaseService;
use App\Domain\Lead\Services\LeadAssignmentService;
use App\Domain\Lead\ValueObjects\SourceType;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use Illuminate\Support\Facades\DB;

/**
 * Handler para capturar un Lead desde un widget externo.
 */
class CaptureLeadHandler
{
    public function __construct(
        private readonly FindLeadByContactHandler $findByContactHandler,
        private readonly CreateLeadHandler $createLeadHandler,
        private readonly SalePhaseService $salePhaseService,
        private readonly DealService $dealService,
        private readonly LeadAssignmentService $leadAssignmentService,
    ) {}

    /**
     * @return array{success: bool, message: string, data: array}
     */
    public function handle(CaptureLeadCommand $command): array
    {
        // Obtener fase por defecto
        $defaultPhase = $this->salePhaseService->getDefaultOrFirstOpen();

        if (! $defaultPhase) {
            return [
                'success' => false,
                'message' => 'No hay fases de venta configuradas',
                'data' => [],
                'status_code' => 500,
            ];
        }

        // Toda la lógica dentro de transacción para evitar race conditions
        return DB::transaction(function () use ($command, $defaultPhase) {
            // Buscar contacto existente por email o teléfono (con lock)
            $findQuery = new FindLeadByContactQuery(
                email: $command->email,
                phone: $command->phone,
                lockForUpdate: true
            );
            $existingLead = $this->findByContactHandler->handle($findQuery);

            // Si el contacto existe
            if ($existingLead) {
                return $this->handleExistingLead($existingLead, $command, $defaultPhase);
            }

            // Contacto no existe, crear nuevo
            return $this->handleNewLead($command, $defaultPhase);
        });
    }

    /**
     * Manejar lead existente.
     */
    private function handleExistingLead(LeadModel $lead, CaptureLeadCommand $command, $defaultPhase): array
    {
        // Verificar si tiene negocio abierto
        if ($lead->hasOpenDeal()) {
            return [
                'success' => true,
                'message' => 'Contacto ya registrado con negocio activo',
                'data' => [
                    'id' => $lead->id,
                    'existing' => true,
                    'has_open_deal' => true,
                ],
                'status_code' => 200,
            ];
        }

        // Obtener usuario asignado según la configuración del sitio
        $assignedUserId = $command->siteId
            ? $this->leadAssignmentService->getAssignedUserForSite($command->siteId)
            : null;

        // No tiene negocio abierto, crear uno nuevo
        $deal = $this->createDealForLead($lead->id, $command, $defaultPhase, $assignedUserId);

        return [
            'success' => true,
            'message' => 'Nuevo negocio creado para contacto existente',
            'data' => [
                'id' => $lead->id,
                'deal_id' => $deal->id,
                'existing' => true,
                'has_open_deal' => false,
                'assigned_to' => $assignedUserId,
            ],
            'status_code' => 201,
        ];
    }

    /**
     * Manejar nuevo lead.
     */
    private function handleNewLead(CaptureLeadCommand $command, $defaultPhase): array
    {
        // Obtener usuario asignado según la configuración del sitio
        $assignedUserId = $command->siteId
            ? $this->leadAssignmentService->getAssignedUserForSite($command->siteId)
            : null;

        // Crear lead
        $leadData = new LeadData(
            name: $command->name,
            email: $command->email,
            phone: $command->phone,
            message: $command->message,
            sourceType: $command->sourceType,
            sourceSiteId: $command->siteId,
            sourceUrl: $command->sourceUrl ?? $command->pageUrl,
            metadata: [
                'user_agent' => $command->userAgent,
                'ip_address' => $command->ipAddress,
                'page_url' => $command->pageUrl,
                'captured_at' => now()->toIso8601String(),
            ],
            assignedTo: $assignedUserId,
        );

        $createCommand = new \App\Application\Lead\Commands\CreateLeadCommand($leadData);
        $lead = $this->createLeadHandler->handle($createCommand);

        // Crear negocio asociado (con el mismo usuario asignado)
        $deal = $this->createDealForLead($lead->id, $command, $defaultPhase, $assignedUserId);

        return [
            'success' => true,
            'message' => 'Contacto y negocio creados exitosamente',
            'data' => [
                'id' => $lead->id,
                'deal_id' => $deal->id,
                'existing' => false,
                'assigned_to' => $assignedUserId,
            ],
            'status_code' => 201,
        ];
    }

    /**
     * Crear un negocio para un lead.
     */
    private function createDealForLead(string $leadId, CaptureLeadCommand $command, $defaultPhase, ?string $assignedUserId = null): \App\Infrastructure\Persistence\Eloquent\DealModel
    {
        $dealName = $this->generateDealName($command->sourceType, $command->name);
        $dealData = DealData::fromArray([
            'lead_id' => $leadId,
            'sale_phase_id' => $defaultPhase->id,
            'name' => $dealName,
            'description' => $command->message,
            'estimated_close_date' => now()->addMonth()->format('Y-m-d'),
            'assigned_to' => $assignedUserId,
        ]);

        $result = $this->dealService->create($dealData);

        return $result['deal'];
    }

    /**
     * Generar nombre del negocio basado en el tipo de origen.
     */
    private function generateDealName(SourceType $sourceType, ?string $name): string
    {
        $displayName = $name ?: 'Sin nombre';

        return match ($sourceType) {
            SourceType::WHATSAPP_BUTTON => 'WhatsApp - '.$displayName,
            SourceType::PHONE_BUTTON => 'Llamada - '.$displayName,
            SourceType::CONTACT_FORM => 'Formulario - '.$displayName,
            default => 'Nuevo negocio - '.$displayName,
        };
    }
}
