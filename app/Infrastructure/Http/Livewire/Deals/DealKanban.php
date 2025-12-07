<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Deals;

use App\Application\Deal\Services\DealService;
use App\Application\SalePhase\Services\SalePhaseService;
use App\Domain\Deal\Services\DealPhaseService;
use Livewire\Attributes\On;
use Livewire\Component;

class DealKanban extends Component
{
    public string $search = '';

    // Value modal for closing as won
    public bool $showValueModal = false;

    public ?string $pendingWonDealId = null;

    public ?string $pendingWonPhaseId = null;

    public ?string $dealValue = null;

    #[On('dealSaved')]
    public function refreshBoard(): void
    {
        // El board se refresca automáticamente
    }

    public function openCreateModal(?string $leadId = null): void
    {
        $this->dispatch('openDealModal', leadId: $leadId);
    }

    public function openEditModal(string $dealId): void
    {
        $this->dispatch('openDealModal', dealId: $dealId);
    }

    public function moveToPhase(string $dealId, string $phaseId): void
    {
        $dealService = app(DealService::class);
        $phaseService = app(SalePhaseService::class);

        $deal = $dealService->findWithRelations($dealId);
        $newPhase = $phaseService->find($phaseId);

        if (! $deal || ! $newPhase) {
            return;
        }

        $service = new DealPhaseService();
        $validation = $service->canChangePhase($deal, $newPhase);

        if (! $validation['can_change']) {
            if ($validation['reason'] === DealPhaseService::RESULT_NO_CHANGE) {
                return;
            }

            if ($validation['reason'] === DealPhaseService::RESULT_REQUIRES_VALUE) {
                $this->pendingWonDealId = $dealId;
                $this->pendingWonPhaseId = $phaseId;
                $this->dealValue = $deal->value ? (string) $deal->value : null;
                $this->showValueModal = true;

                return;
            }

            $this->dispatch('notify', type: 'error', message: $service->getErrorMessage($validation['reason']));

            return;
        }

        $result = $service->applyPhaseChange($deal, $newPhase);
        $this->dispatch('notify', type: 'success', message: $result['message']);
    }

    public function confirmWonWithValue(): void
    {
        $validationRules = DealPhaseService::getWonValueValidationRules();
        $this->validate($validationRules['rules'], $validationRules['messages']);

        $dealService = app(DealService::class);
        $phaseService = app(SalePhaseService::class);

        $deal = $dealService->find($this->pendingWonDealId);
        $newPhase = $phaseService->find($this->pendingWonPhaseId);

        if (! $deal || ! $newPhase) {
            $this->cancelWonPhase();

            return;
        }

        $service = new DealPhaseService();
        $result = $service->changePhaseWithValue($deal, $newPhase, (float) $this->dealValue);

        $this->dispatch('notify', type: $result['success'] ? 'success' : 'error', message: $result['message']);

        // Cerrar modal y limpiar
        $this->showValueModal = false;
        $this->pendingWonDealId = null;
        $this->pendingWonPhaseId = null;
        $this->dealValue = null;
    }

    public function cancelWonPhase(): void
    {
        $this->showValueModal = false;
        $this->pendingWonDealId = null;
        $this->pendingWonPhaseId = null;
        $this->dealValue = null;
    }

    public function render()
    {
        $dealService = app(DealService::class);
        $phaseService = app(SalePhaseService::class);

        // Obtener fases usando el servicio
        $openPhases = $phaseService->getActivePhases();
        $closedPhases = $phaseService->getClosedPhases();

        // Obtener IDs de fases abiertas
        $openPhaseIds = $openPhases->pluck('id')->toArray();

        // Obtener todos los deals de fases abiertas usando el servicio
        $searchTerm = $this->search ?: null;
        $allDeals = $dealService->getByPhaseIds($openPhaseIds, $searchTerm);

        // Agrupar en memoria por fase
        $dealsByPhase = $allDeals->groupBy('sale_phase_id');

        // Asegurar que todas las fases tengan una colección (aunque esté vacía)
        foreach ($openPhaseIds as $phaseId) {
            if (! isset($dealsByPhase[$phaseId])) {
                $dealsByPhase[$phaseId] = collect();
            }
        }

        // Stats calculados desde la colección ya cargada (sin queries adicionales)
        $totalDeals = $allDeals->count();
        $totalValue = $allDeals->sum('value') ?? 0;

        return view('livewire.deals.kanban', [
            'openPhases' => $openPhases,
            'closedPhases' => $closedPhases,
            'dealsByPhase' => $dealsByPhase,
            'totalDeals' => $totalDeals,
            'totalValue' => $totalValue,
        ])->layout('components.layouts.app', ['title' => 'Pipeline']);
    }
}
