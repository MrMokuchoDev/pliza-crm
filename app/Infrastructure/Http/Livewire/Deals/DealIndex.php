<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Deals;

use App\Application\Deal\Services\DealService;
use App\Application\SalePhase\Services\SalePhaseService;
use App\Domain\Deal\Services\DealPhaseService;
use App\Domain\Lead\ValueObjects\SourceType;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class DealIndex extends Component
{
    use WithPagination;

    public bool $showDeleteModal = false;

    public ?string $deletingId = null;

    public int $refreshKey = 0;

    // Value modal for closing as won
    public bool $showValueModal = false;

    public ?string $pendingWonDealId = null;

    public ?string $pendingWonPhaseId = null;

    public ?string $dealValue = null;

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

    public function openDeleteModal(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        $dealService = app(DealService::class);
        $result = $dealService->delete($this->deletingId);

        if ($result['success']) {
            $message = 'Negocio eliminado correctamente';
            if ($result['deleted_comments'] > 0) {
                $message .= " ({$result['deleted_comments']} comentarios eliminados)";
            }
            $this->dispatch('notify', type: 'success', message: $message);
        } else {
            $this->dispatch('notify', type: 'error', message: 'Error al eliminar el negocio');
        }

        $this->closeDeleteModal();
    }

    public function updatePhase(string $dealId, string $phaseId): void
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
                $this->refreshKey++;

                return;
            }

            $this->refreshKey++;
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
        $this->refreshKey++;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
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

        // Obtener deals paginados usando el servicio
        $filters = array_filter([
            'search' => $this->search,
            'phase_id' => $this->filterPhase,
            'source_type' => $this->filterSource,
        ]);
        $deals = $dealService->getPaginated($filters);

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
        ])->layout('components.layouts.app', ['title' => 'Negocios']);
    }
}
