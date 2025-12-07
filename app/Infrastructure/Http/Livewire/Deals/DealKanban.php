<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Deals;

use App\Application\Deal\Services\DealService;
use App\Application\SalePhase\Services\SalePhaseService;
use App\Infrastructure\Http\Livewire\Traits\HasWonPhaseValue;
use Livewire\Attributes\On;
use Livewire\Component;

class DealKanban extends Component
{
    use HasWonPhaseValue;

    public string $search = '';

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
        $result = $this->handlePhaseChange($dealId, $phaseId);

        if ($result === null) {
            // Se abrió el modal de valor o no hubo cambio
            return;
        }

        $this->dispatch('notify', type: $result['success'] ? 'success' : 'error', message: $result['message']);
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
