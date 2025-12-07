<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Deals;

use App\Application\Deal\DTOs\DealData;
use App\Application\Deal\Services\DealService;
use App\Application\Lead\DTOs\LeadData;
use App\Application\Lead\Services\LeadService;
use App\Application\SalePhase\Services\SalePhaseService;
use App\Domain\Deal\Services\DealPhaseService;
use App\Domain\Lead\ValueObjects\SourceType;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class DealFormModal extends Component
{
    public bool $show = false;

    public ?string $dealId = null;

    public ?string $leadId = null;

    public string $name = '';

    public ?string $value = '';

    public string $description = '';

    public string $salePhaseId = '';

    public ?string $estimatedCloseDate = null;

    public ?string $closeDate = null;

    // Lead data for display/edit
    public string $leadName = '';

    public string $leadEmail = '';

    public string $leadPhone = '';

    // Lead search
    public string $leadSearch = '';

    public bool $showLeadSearch = false;

    public bool $createNewLead = false;

    public ?string $leadHasOpenDealError = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'value' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:5000',
            'salePhaseId' => 'required|exists:sale_phases,id',
            'estimatedCloseDate' => 'nullable|date',
            'closeDate' => 'nullable|date',
            'leadName' => 'nullable|string|max:255',
            'leadEmail' => 'nullable|email|max:255',
            'leadPhone' => 'nullable|string|max:50',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre del negocio es requerido.',
            'salePhaseId.required' => 'La fase de venta es requerida.',
            'value.numeric' => 'El valor debe ser un número.',
            'leadEmail.email' => 'El email debe ser válido.',
        ];
    }

    #[On('openDealModal')]
    public function open(?string $dealId = null, ?string $leadId = null): void
    {
        $this->resetForm();

        if ($dealId) {
            $dealService = app(DealService::class);
            $deal = $dealService->findWithLead($dealId);
            if ($deal) {
                $this->dealId = $dealId;
                $this->leadId = $deal->lead_id;
                $this->name = $deal->name;
                $this->value = $deal->value ? (string) $deal->value : '';
                $this->description = $deal->description ?? '';
                $this->salePhaseId = $deal->sale_phase_id;
                $this->estimatedCloseDate = $deal->estimated_close_date?->format('Y-m-d');
                $this->closeDate = $deal->close_date?->format('Y-m-d');

                // Load lead data
                if ($deal->lead) {
                    $this->leadName = $deal->lead->name ?? '';
                    $this->leadEmail = $deal->lead->email ?? '';
                    $this->leadPhone = $deal->lead->phone ?? '';
                }
                $this->showLeadSearch = false;
            }
        } elseif ($leadId) {
            // Creating new deal for existing lead
            $leadService = app(LeadService::class);
            $leadData = $leadService->findWithOpenDealCheck($leadId);
            $lead = $leadData['lead'];
            if ($lead) {
                // Check if lead already has an open deal
                if ($leadData['has_open_deal']) {
                    $this->dispatch('notify', type: 'error', message: 'Este contacto ya tiene un negocio abierto.');

                    return;
                }

                $this->leadId = $leadId;
                $this->leadName = $lead->name ?? '';
                $this->leadEmail = $lead->email ?? '';
                $this->leadPhone = $lead->phone ?? '';
                $this->showLeadSearch = false;
            }
        } else {
            // Opening modal without lead - show search
            $this->showLeadSearch = true;
            $this->createNewLead = false;
        }

        $this->show = true;
    }

    public function updatedLeadSearch(): void
    {
        $this->leadHasOpenDealError = null;
    }

    public function selectLead(string $id): void
    {
        $leadService = app(LeadService::class);
        $leadData = $leadService->findWithOpenDealCheck($id);
        $lead = $leadData['lead'];
        if (! $lead) {
            return;
        }

        // Check if lead has open deal
        if ($leadData['has_open_deal']) {
            $this->leadHasOpenDealError = 'Este contacto ya tiene un negocio abierto.';

            return;
        }

        $this->leadId = $id;
        $this->leadName = $lead->name ?? '';
        $this->leadEmail = $lead->email ?? '';
        $this->leadPhone = $lead->phone ?? '';
        $this->showLeadSearch = false;
        $this->leadSearch = '';
        $this->leadHasOpenDealError = null;
    }

    public function startNewLead(): void
    {
        $this->createNewLead = true;
        $this->showLeadSearch = false;
        $this->leadId = null;
        $this->leadSearch = '';
        $this->leadHasOpenDealError = null;
    }

    public function backToSearch(): void
    {
        $this->createNewLead = false;
        $this->showLeadSearch = true;
        $this->leadId = null;
        $this->leadName = '';
        $this->leadEmail = '';
        $this->leadPhone = '';
        $this->name = '';
    }

    public function clearSelectedLead(): void
    {
        $this->leadId = null;
        $this->leadName = '';
        $this->leadEmail = '';
        $this->leadPhone = '';
        $this->name = '';
        $this->showLeadSearch = true;
        $this->createNewLead = false;
    }

    public function save(): void
    {
        // Verificar PRIMERO si se quiere cerrar como GANADO sin valor
        $phaseService = app(SalePhaseService::class);
        $phase = $phaseService->find($this->salePhaseId);
        if ($phase) {
            $valueValidation = DealPhaseService::validateValueForWonPhase($phase, $this->value);
            if (! $valueValidation['valid']) {
                $this->addError('value', $valueValidation['error']);
                $this->dispatch('notify', type: 'error', message: $valueValidation['error']);

                return;
            }
        }

        $this->validate();

        // Si estamos editando un negocio, validar cambio de fase ANTES de la transacción
        $dealService = app(DealService::class);
        if ($this->dealId && $phase) {
            $deal = $dealService->findWithRelations($this->dealId);
            if ($deal && $deal->sale_phase_id !== $phase->id) {
                $service = new DealPhaseService();
                $validation = $service->canChangePhase($deal, $phase);
                if (! $validation['can_change'] && $validation['reason'] === DealPhaseService::RESULT_LEAD_HAS_OPEN_DEAL) {
                    $this->dispatch('notify', type: 'error', message: $service->getErrorMessage($validation['reason']));

                    return;
                }
            }
        }

        // Envolver operaciones de BD en transacción para garantizar consistencia
        $isUpdate = (bool) $this->dealId;
        $leadService = app(LeadService::class);
        $dealService = app(DealService::class);

        DB::transaction(function () use ($phase, $leadService, $dealService) {
            // If creating new lead
            if ($this->createNewLead && ! $this->leadId) {
                $leadData = new LeadData(
                    name: $this->leadName ?: null,
                    email: $this->leadEmail ?: null,
                    phone: $this->leadPhone ?: null,
                    sourceType: SourceType::MANUAL,
                );
                $newLead = $leadService->create($leadData);
                $this->leadId = $newLead->id;
            }

            // Update lead data if we have a lead
            if ($this->leadId && ! $this->createNewLead) {
                $leadData = new LeadData(
                    name: $this->leadName ?: null,
                    email: $this->leadEmail ?: null,
                    phone: $this->leadPhone ?: null,
                );
                $leadService->update($this->leadId, $leadData);
            }

            // Si se mueve a fase abierta, limpiar fecha de cierre
            $closeDate = $phase?->is_closed
                ? ($this->closeDate ?: now()->format('Y-m-d'))
                : null;

            $dealData = DealData::fromArray([
                'lead_id' => $this->leadId,
                'name' => $this->name,
                'value' => $this->value ?: null,
                'description' => $this->description ?: null,
                'sale_phase_id' => $this->salePhaseId,
                'estimated_close_date' => $this->estimatedCloseDate ?: null,
                'close_date' => $closeDate,
            ]);

            if ($this->dealId) {
                $dealService->update($this->dealId, $dealData);
            } else {
                $dealService->create($dealData);
            }
        });

        $this->dispatch('notify', type: 'success', message: $isUpdate ? 'Negocio actualizado' : 'Negocio creado');
        $this->close();
        $this->dispatch('dealSaved');
    }

    public function close(): void
    {
        $this->show = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->dealId = null;
        $this->leadId = null;
        $this->name = '';
        $this->value = '';
        $this->description = '';
        $this->estimatedCloseDate = null;
        $this->closeDate = null;
        $this->leadName = '';
        $this->leadEmail = '';
        $this->leadPhone = '';
        $this->leadSearch = '';
        $this->showLeadSearch = false;
        $this->createNewLead = false;
        $this->leadHasOpenDealError = null;
        $this->setDefaultPhase();
        $this->resetValidation();
    }

    private function setDefaultPhase(): void
    {
        $phaseService = app(SalePhaseService::class);
        $defaultPhase = $phaseService->getDefaultOrFirstOpen();
        $this->salePhaseId = $defaultPhase?->id ?? '';
    }

    public function render()
    {
        $phaseService = app(SalePhaseService::class);
        $phases = $phaseService->getAllOrdered();

        // Search leads if searching
        $searchResults = collect();
        if ($this->showLeadSearch && strlen($this->leadSearch) >= 2) {
            $leadService = app(LeadService::class);
            $searchResults = $leadService->search($this->leadSearch);
        }

        return view('livewire.deals.form-modal', [
            'phases' => $phases,
            'searchResults' => $searchResults,
        ]);
    }
}
