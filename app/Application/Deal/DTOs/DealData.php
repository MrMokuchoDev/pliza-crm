<?php

declare(strict_types=1);

namespace App\Application\Deal\DTOs;

/**
 * DTO para transferir datos de Deal entre capas.
 */
readonly class DealData
{
    public function __construct(
        public ?string $id = null,
        public ?string $leadId = null,
        public ?string $salePhaseId = null,
        public ?string $closeDate = null,
        public ?string $assignedTo = null,
        public ?string $createdBy = null,
        public array $customFields = [],
    ) {}

    /**
     * Crear desde array (útil para formularios).
     */
    public static function fromArray(array $data): self
    {
        // Separar campos del sistema de custom fields
        $systemFields = ['id', 'lead_id', 'sale_phase_id', 'close_date', 'assigned_to', 'created_by'];
        $customFields = [];

        foreach ($data as $key => $value) {
            if (!in_array($key, $systemFields)) {
                $customFields[$key] = $value;
            }
        }

        return new self(
            id: $data['id'] ?? null,
            leadId: $data['lead_id'] ?? null,
            salePhaseId: $data['sale_phase_id'] ?? null,
            closeDate: $data['close_date'] ?? null,
            assignedTo: $data['assigned_to'] ?? null,
            createdBy: $data['created_by'] ?? null,
            customFields: $customFields,
        );
    }

    /**
     * Convertir a array para persistencia.
     */
    public function toArray(): array
    {
        $systemData = array_filter([
            'id' => $this->id,
            'lead_id' => $this->leadId,
            'sale_phase_id' => $this->salePhaseId,
            'close_date' => $this->closeDate,
            'assigned_to' => $this->assignedTo,
            'created_by' => $this->createdBy,
        ], fn ($value) => $value !== null);

        return array_merge($systemData, $this->customFields);
    }

    /**
     * Convertir a array para actualizaciones.
     * Incluye campos nullables que pueden ser limpiados por el usuario.
     */
    public function toArrayForUpdate(): array
    {
        $systemData = [];

        // Solo incluir campos de relación si tienen valor
        if ($this->leadId !== null) {
            $systemData['lead_id'] = $this->leadId;
        }
        if ($this->salePhaseId !== null) {
            $systemData['sale_phase_id'] = $this->salePhaseId;
        }
        if ($this->closeDate !== null) {
            $systemData['close_date'] = $this->closeDate;
        }
        if ($this->assignedTo !== null) {
            $systemData['assigned_to'] = $this->assignedTo;
        }

        return array_merge($systemData, $this->customFields);
    }
}
