<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Leads;

use App\Domain\Lead\ValueObjects\SourceType;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
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

    public string $filterSource = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterSource' => ['except' => ''],
    ];

    public function updatingSearch(): void
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

    public function openCreateDealModal(string $leadId): void
    {
        $this->dispatch('openDealModal', leadId: $leadId);
    }

    #[On('leadSaved')]
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

        LeadModel::destroy($this->deletingId);
        $this->dispatch('notify', type: 'success', message: 'Contacto eliminado correctamente');
        $this->closeDeleteModal();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->filterSource = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = LeadModel::withCount(['deals', 'activeDeals'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterSource, fn ($q) => $q->where('source_type', $this->filterSource))
            ->orderByDesc('created_at');

        $leads = $query->paginate(10);

        $sourceTypes = SourceType::cases();

        // Stats
        $totalLeads = LeadModel::count();
        $leadsWithDeals = LeadModel::has('deals')->count();
        $leadsWithoutDeals = $totalLeads - $leadsWithDeals;

        return view('livewire.leads.index', [
            'leads' => $leads,
            'sourceTypes' => $sourceTypes,
            'totalLeads' => $totalLeads,
            'leadsWithDeals' => $leadsWithDeals,
            'leadsWithoutDeals' => $leadsWithoutDeals,
        ])->layout('components.layouts.app', ['title' => 'Contactos']);
    }
}
