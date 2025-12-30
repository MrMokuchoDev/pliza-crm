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
use App\Domain\User\ValueObjects\Permission;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class DealFormModal extends Component
{
    public bool $show = false;

    public ?string $dealId = null;

    public ?string $leadId = null;

    public string $salePhaseId = '';

    public ?string $closeDate = null;

    // Array dinámico para custom field values del Deal
    public array $customFieldValues = [];

    // Array dinámico para custom field values del Lead asociado
    public array $leadCustomFieldValues = [];

    // Lead search
    public string $leadSearch = '';

    public bool $showLeadSearch = false;

    public bool $createNewLead = false;

    public ?string $leadHasOpenDealError = null;

    public ?string $assigned_to = null;

    public bool $canAssign = false;

    public bool $canEditLead = true;

    public bool $canEditDeal = true;

    public Collection $assignableUsers;

    public function mount(): void
    {
        $this->assignableUsers = collect();
        $this->canAssign = Auth::user()?->canAssignDeals() ?? false;

        if ($this->canAssign) {
            $this->loadAssignableUsers();
        }

        $this->setDefaultPhase();
    }

    protected function loadAssignableUsers(): void
    {
        $this->assignableUsers = User::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Determina si el usuario actual puede editar el lead.
     * Puede editar si: tiene permiso leads.update_all O el lead está asignado a él.
     */
    protected function determineCanEditLead(?string $leadAssignedTo): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Si tiene permiso de actualizar todos los leads, puede editar
        if ($user->hasPermission(Permission::LEADS_UPDATE_ALL)) {
            return true;
        }

        // Si el lead está asignado al usuario actual, puede editar
        if ($leadAssignedTo && $leadAssignedTo === $user->uuid) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario actual puede editar el deal.
     * Puede editar si: tiene permiso deals.update_all O el deal está asignado a él.
     */
    protected function determineCanEditDeal(?string $dealAssignedTo): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Si tiene permiso de actualizar todos los deals, puede editar
        if ($user->hasPermission(Permission::DEALS_UPDATE_ALL)) {
            return true;
        }

        // Si el deal está asignado al usuario actual, puede editar
        if ($dealAssignedTo && $dealAssignedTo === $user->uuid) {
            return true;
        }

        return false;
    }

    protected function rules(): array
    {
        return [
            'customFieldValues.*' => 'nullable',
            'leadCustomFieldValues.*' => 'nullable',
            'salePhaseId' => 'required|exists:sale_phases,id',
            'closeDate' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,uuid',
        ];
    }

    protected function messages(): array
    {
        return [
            'salePhaseId.required' => 'La fase de venta es requerida.',
        ];
    }

    #[On('openDealModal')]
    public function open(?string $dealId = null, ?string $leadId = null): void
    {
        $this->resetForm();

        // Recargar permisos y usuarios en cada apertura
        $this->canAssign = Auth::user()?->canAssignDeals() ?? false;
        if ($this->canAssign) {
            $this->loadAssignableUsers();
        }

        if ($dealId) {
            $dealService = app(DealService::class);
            $deal = $dealService->findWithLead($dealId);
            if ($deal) {
                // Verificar si puede editar este deal
                $this->canEditDeal = $this->determineCanEditDeal($deal->assigned_to);

                if (!$this->canEditDeal) {
                    $this->dispatch('notify', type: 'error', message: 'No tienes permiso para editar este negocio.');
                    return;
                }

                $this->dealId = $dealId;
                $this->leadId = $deal->lead_id;
                $this->salePhaseId = $deal->sale_phase_id;
                $this->closeDate = $deal->close_date?->format('Y-m-d');

                // Cargar custom field values del Deal dinámicamente desde la relación
                foreach ($deal->customFieldValues as $cfValue) {
                    $fieldName = $cfValue->customField->name ?? null;
                    if ($fieldName) {
                        $this->customFieldValues[$fieldName] = $cfValue->value ?? '';
                    }
                }

                // Load lead data
                if ($deal->lead) {
                    // Cargar custom field values del Lead dinámicamente desde la relación
                    foreach ($deal->lead->customFieldValues as $cfValue) {
                        $fieldName = $cfValue->customField->name ?? null;
                        if ($fieldName) {
                            $this->leadCustomFieldValues[$fieldName] = $cfValue->value ?? '';
                        }
                    }

                    // Determinar si el usuario puede editar el lead
                    $this->canEditLead = $this->determineCanEditLead($deal->lead->assigned_to);
                }
                $this->assigned_to = $deal->assigned_to;
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

                // Cargar custom field values del Lead dinámicamente desde la relación
                foreach ($lead->customFieldValues as $cfValue) {
                    $fieldName = $cfValue->customField->name ?? null;
                    if ($fieldName) {
                        $this->leadCustomFieldValues[$fieldName] = $cfValue->value ?? '';
                    }
                }

                $this->showLeadSearch = false;
                // Determinar si el usuario puede editar el lead
                $this->canEditLead = $this->determineCanEditLead($lead->assigned_to);
                // Asignar por defecto al usuario actual al crear nuevo negocio
                $this->assigned_to = Auth::user()?->uuid;
            }
        } else {
            // Opening modal without lead - show search
            $this->showLeadSearch = true;
            $this->createNewLead = false;
            // Asignar por defecto al usuario actual al crear nuevo negocio
            $this->assigned_to = Auth::user()?->uuid;
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

        // Cargar custom field values del Lead dinámicamente desde la relación
        foreach ($lead->customFieldValues as $cfValue) {
            $fieldName = $cfValue->customField->name ?? null;
            if ($fieldName) {
                $this->leadCustomFieldValues[$fieldName] = $cfValue->value ?? '';
            }
        }

        $this->showLeadSearch = false;
        $this->leadSearch = '';
        $this->leadHasOpenDealError = null;
        // Determinar si el usuario puede editar el lead seleccionado
        $this->canEditLead = $this->determineCanEditLead($lead->assigned_to);
    }

    public function startNewLead(): void
    {
        $this->createNewLead = true;
        $this->showLeadSearch = false;
        $this->leadId = null;
        $this->leadSearch = '';
        $this->leadHasOpenDealError = null;
        // Al crear un nuevo lead, el usuario puede editarlo
        $this->canEditLead = true;
    }

    public function backToSearch(): void
    {
        $this->createNewLead = false;
        $this->showLeadSearch = true;
        $this->leadId = null;
        $this->leadCustomFieldValues = [];
        $this->customFieldValues = [];
    }

    public function clearSelectedLead(): void
    {
        $this->leadId = null;
        $this->leadCustomFieldValues = [];
        $this->customFieldValues = [];
        $this->showLeadSearch = true;
        $this->createNewLead = false;
    }

    public function save(): void
    {
        // Validación de seguridad: verificar permisos antes de guardar (edición)
        if ($this->dealId && !$this->canEditDeal) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para editar este negocio.');
            $this->close();
            return;
        }

        $this->validate();

        $phaseService = app(SalePhaseService::class);
        $phase = $phaseService->find($this->salePhaseId);

        // Validar si la fase seleccionada es ganada y requiere valor (tanto para crear como para editar)
        if ($phase && $phase->is_closed && $phase->is_won) {
            $value = $this->customFieldValues['cf_deal_2'] ?? null;
            if (empty($value) || !is_numeric($value) || (float)$value <= 0) {
                $this->dispatch('notify', type: 'error', message: 'El valor del negocio es obligatorio para cerrarlo como ganado.');
                return;
            }
        }

        // Si estamos editando un negocio, validar cambio de fase ANTES de la transacción
        $dealService = app(DealService::class);
        if ($this->dealId && $phase) {
            $deal = $dealService->findWithRelations($this->dealId);
            if ($deal && $deal->sale_phase_id !== $phase->id) {
                $service = new DealPhaseService();
                $validation = $service->canChangePhase($deal, $phase);
                if (! $validation['can_change']) {
                    // Validar solo si el lead tiene negocio abierto (el valor ya se validó arriba)
                    if ($validation['reason'] === DealPhaseService::RESULT_LEAD_HAS_OPEN_DEAL) {
                        $this->dispatch('notify', type: 'error', message: $service->getErrorMessage($validation['reason']));

                        return;
                    }
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
                $leadData = LeadData::fromArray(array_merge(
                    $this->leadCustomFieldValues,
                    ['source_type' => SourceType::MANUAL->value]
                ));
                $newLead = $leadService->create($leadData);
                $this->leadId = $newLead->id;
            }

            // Update lead data if we have a lead AND user can edit it
            if ($this->leadId && ! $this->createNewLead && $this->canEditLead) {
                $leadData = LeadData::fromArray($this->leadCustomFieldValues);
                $leadService->update($this->leadId, $leadData);
            }

            // Si se mueve a fase abierta, limpiar fecha de cierre
            $closeDate = $phase?->is_closed
                ? ($this->closeDate ?: now()->format('Y-m-d'))
                : null;

            // Si no puede asignar, usar el usuario actual para nuevos deals
            $assignedTo = $this->canAssign ? $this->assigned_to : ($this->dealId ? null : Auth::user()?->uuid);

            $dealData = DealData::fromArray(array_merge(
                $this->customFieldValues,
                [
                    'lead_id' => $this->leadId,
                    'sale_phase_id' => $this->salePhaseId,
                    'close_date' => $closeDate,
                    'assigned_to' => $assignedTo,
                ]
            ));

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
        $this->customFieldValues = [];
        $this->leadCustomFieldValues = [];
        $this->closeDate = null;
        $this->leadSearch = '';
        $this->showLeadSearch = false;
        $this->createNewLead = false;
        $this->leadHasOpenDealError = null;
        $this->assigned_to = null;
        $this->canEditDeal = true;
        $this->canEditLead = true;
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

            /** @var User|null $user */
            $user = Auth::user();
            // Solo filtrar por asignación si el usuario no puede ver todos los leads
            $onlyOwn = $user && !$user->hasPermission(Permission::LEADS_VIEW_ALL);
            $userUuid = $user?->uuid;

            $searchResults = $leadService->search(
                $this->leadSearch,
                10,
                $userUuid,
                $onlyOwn
            );
        }

        return view('livewire.deals.form-modal', [
            'phases' => $phases,
            'searchResults' => $searchResults,
        ]);
    }
}
