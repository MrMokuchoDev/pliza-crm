<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Deals;

use App\Domain\Lead\ValueObjects\SourceType;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class DealIndex extends Component
{
    use WithPagination;

    public bool $showDeleteModal = false;

    public ?string $deletingId = null;

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

        DealModel::destroy($this->deletingId);
        $this->dispatch('notify', type: 'success', message: 'Negocio eliminado correctamente');
        $this->closeDeleteModal();
    }

    public function updatePhase(string $dealId, string $phaseId): void
    {
        $deal = DealModel::with(['salePhase', 'lead'])->find($dealId);
        $newPhase = SalePhaseModel::find($phaseId);

        if (! $deal || ! $newPhase) {
            return;
        }

        if ($deal->sale_phase_id === $phaseId) {
            return;
        }

        // Si el negocio está cerrado y se quiere mover a una fase abierta,
        // verificar que el contacto no tenga otro negocio abierto
        if ($deal->salePhase?->is_closed && ! $newPhase->is_closed) {
            if ($deal->lead && $deal->lead->hasOpenDeal($deal->id)) {
                $this->refreshKey++; // Forzar re-render del select
                $this->dispatch('notify', type: 'error', message: 'Este contacto ya tiene un negocio abierto. Cierra o elimina el otro negocio antes de reabrir este.');

                return;
            }
        }

        $updateData = [
            'sale_phase_id' => $phaseId,
            'updated_at' => now(),
        ];

        // Si se mueve a fase cerrada, establecer fecha de cierre
        if ($newPhase->is_closed && ! $deal->close_date) {
            $updateData['close_date'] = now();
        }

        // Si se mueve a fase abierta, limpiar fecha de cierre
        if (! $newPhase->is_closed) {
            $updateData['close_date'] = null;
        }

        DealModel::where('id', $dealId)->update($updateData);

        $message = $newPhase->is_closed
            ? ($newPhase->is_won ? 'Negocio marcado como ganado' : 'Negocio marcado como perdido')
            : 'Fase actualizada';

        $this->dispatch('notify', type: 'success', message: $message);
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
        $query = DealModel::with(['lead', 'salePhase'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('name', 'like', "%{$this->search}%")
                        ->orWhereHas('lead', function ($lq) {
                            $lq->where('name', 'like', "%{$this->search}%")
                                ->orWhere('email', 'like', "%{$this->search}%")
                                ->orWhere('phone', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->filterPhase, fn ($q) => $q->where('sale_phase_id', $this->filterPhase))
            ->when($this->filterSource, function ($q) {
                $q->whereHas('lead', fn ($lq) => $lq->where('source_type', $this->filterSource));
            })
            ->orderByDesc('created_at');

        $deals = $query->paginate(10);

        $phases = SalePhaseModel::orderBy('order')->get();
        $sourceTypes = SourceType::cases();

        // Stats
        $totalDeals = DealModel::count();
        $openDeals = DealModel::whereHas('salePhase', fn ($q) => $q->where('is_closed', false))->count();
        $totalValue = DealModel::whereHas('salePhase', fn ($q) => $q->where('is_closed', false))->sum('value');

        return view('livewire.deals.index', [
            'deals' => $deals,
            'phases' => $phases,
            'sourceTypes' => $sourceTypes,
            'totalDeals' => $totalDeals,
            'openDeals' => $openDeals,
            'totalValue' => $totalValue,
        ])->layout('components.layouts.app', ['title' => 'Negocios']);
    }
}
