<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Leads;

use App\Application\Lead\Services\LeadService;
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

    public string $deletingName = '';

    public int $deletingDealsCount = 0;

    public int $deletingNotesCount = 0;

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
        $leadService = app(LeadService::class);

        $this->deletingId = $id;
        $this->deletingName = $leadService->getDisplayName($id);

        $counts = $leadService->getRelatedCounts($id);
        $this->deletingDealsCount = $counts['deals'];
        $this->deletingNotesCount = $counts['notes'];

        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        $leadService = app(LeadService::class);
        $result = $leadService->delete($this->deletingId);

        if ($result['success']) {
            $this->dispatch('notify', type: 'success', message: 'Contacto y sus negocios eliminados correctamente');
        } else {
            $this->dispatch('notify', type: 'error', message: 'Error al eliminar el contacto');
        }

        $this->closeDeleteModal();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->deletingName = '';
        $this->deletingDealsCount = 0;
        $this->deletingNotesCount = 0;
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
            ->with(['activeDeals' => fn ($q) => $q->with('salePhase')->limit(1)])
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
