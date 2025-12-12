<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Deals;

use App\Application\Deal\Services\DealService;
use App\Application\SalePhase\Services\SalePhaseService;
use App\Domain\Lead\ValueObjects\SourceType;
use App\Infrastructure\Http\Livewire\Traits\HasDeleteConfirmation;
use App\Infrastructure\Http\Livewire\Traits\HasWonPhaseValue;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class DealIndex extends Component
{
    use HasDeleteConfirmation;
    use HasWonPhaseValue;
    use WithPagination;

    public int $refreshKey = 0;

    // Filters
    public string $search = '';

    public string $filterPhase = '';

    public string $filterSource = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterPhase' => ['except' => ''],
        'filterSource' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPhase(): void
    {
        $this->resetPage();
    }

    public function updatingFilterSource(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->dispatch('openDealModal');
    }

    public function openEditModal(string $dealId): void
    {
        $this->dispatch('openDealModal', dealId: $dealId);
    }

    #[On('dealSaved')]
    public function refreshList(): void
    {
        // La lista se refresca automáticamente
    }

    /**
     * Implementación del método abstracto del trait HasDeleteConfirmation.
     */
    protected function performDelete(string $id): bool
    {
        $dealService = app(DealService::class);
        $result = $dealService->delete($id);

        return $result['success'];
    }

    protected function getDeleteSuccessMessage(): string
    {
        return 'Negocio eliminado correctamente';
    }

    protected function getDeleteErrorMessage(): string
    {
        return 'Error al eliminar el negocio';
    }

    public function updatePhase(string $dealId, string $phaseId): void
    {
        $result = $this->handlePhaseChange($dealId, $phaseId);

        if ($result === null) {
            // Se abrió el modal de valor o no hubo cambio
            $this->refreshKey++;

            return;
        }

        if (! $result['success']) {
            $this->refreshKey++;
        }

        $this->dispatch('notify', type: $result['success'] ? 'success' : 'error', message: $result['message']);
    }

    /**
     * Sobrescribir cancelWonPhase para incrementar refreshKey.
     */
    public function cancelWonPhase(): void
    {
        $this->showValueModal = false;
        $this->pendingWonDealId = null;
        $this->pendingWonPhaseId = null;
        $this->dealValue = null;
        $this->refreshKey++;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->filterPhase = '';
        $this->filterSource = '';
        $this->resetPage();
    }

    public function render()
    {
        $dealService = app(DealService::class);
        $phaseService = app(SalePhaseService::class);
        $user = Auth::user();

        // Obtener deals paginados usando el servicio
        $filters = array_filter([
            'search' => $this->search,
            'phase_id' => $this->filterPhase,
            'source_type' => $this->filterSource,
        ]);

        // Determinar si el usuario solo puede ver sus propios deals
        $userUuid = $user?->uuid;
        $onlyOwn = $user && ! $user->canViewAllDeals();

        $deals = $dealService->getPaginated($filters, 10, $userUuid, $onlyOwn);

        // Obtener fases y tipos de origen
        $phases = $phaseService->getAllOrdered();
        $sourceTypes = SourceType::cases();

        // Stats usando el servicio
        $openPhaseIds = $phases->where('is_closed', false)->pluck('id')->toArray();
        $stats = $dealService->getStats($openPhaseIds);

        return view('livewire.deals.index', [
            'deals' => $deals,
            'phases' => $phases,
            'sourceTypes' => $sourceTypes,
            'totalDeals' => $stats['total'],
            'openDeals' => $stats['open'],
            'totalValue' => $stats['total_value'],
            'canCreate' => $user?->canCreateDeals() ?? false,
            'canEdit' => $user?->canEditDeals() ?? false,
            'canDelete' => $user?->canDeleteDeals() ?? false,
        ])->layout('components.layouts.app', ['title' => 'Negocios']);
    }
}
