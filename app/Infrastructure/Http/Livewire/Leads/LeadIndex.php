<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Leads;

use App\Domain\Lead\ValueObjects\SourceType;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class LeadIndex extends Component
{
    use WithPagination;

    public bool $showDeleteModal = false;

    public ?string $deletingId = null;

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
        $this->dispatch('openLeadModal');
    }

    public function openEditModal(string $id): void
    {
        $this->dispatch('openLeadModal', leadId: $id);
    }

    #[On('leadSaved')]
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

        LeadModel::destroy($this->deletingId);
        $this->dispatch('notify', type: 'success', message: 'Lead eliminado correctamente');
        $this->closeDeleteModal();
    }

    public function updatePhase(string $leadId, string $phaseId): void
    {
        LeadModel::where('id', $leadId)->update([
            'sale_phase_id' => $phaseId,
            'updated_at' => now(),
        ]);
        $this->dispatch('notify', type: 'success', message: 'Fase actualizada');
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
        $query = LeadModel::with('salePhase')
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterPhase, fn ($q) => $q->where('sale_phase_id', $this->filterPhase))
            ->when($this->filterSource, fn ($q) => $q->where('source_type', $this->filterSource))
            ->orderByDesc('created_at');

        $leads = $query->paginate(10);

        $phases = SalePhaseModel::orderBy('order')->get();
        $sourceTypes = SourceType::cases();

        // Stats
        $totalLeads = LeadModel::count();
        $leadsByPhase = LeadModel::selectRaw('sale_phase_id, count(*) as count')
            ->groupBy('sale_phase_id')
            ->pluck('count', 'sale_phase_id')
            ->toArray();

        return view('livewire.leads.index', [
            'leads' => $leads,
            'phases' => $phases,
            'sourceTypes' => $sourceTypes,
            'totalLeads' => $totalLeads,
            'leadsByPhase' => $leadsByPhase,
        ])->layout('components.layouts.app', ['title' => 'Leads']);
    }
}
