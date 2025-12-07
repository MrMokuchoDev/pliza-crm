<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Leads;

use App\Application\Lead\Services\LeadService;
use App\Domain\Lead\ValueObjects\SourceType;
use App\Infrastructure\Http\Livewire\Traits\HasNotifications;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class LeadIndex extends Component
{
    use HasNotifications;
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
            $this->notifySuccess('Contacto y sus negocios eliminados correctamente');
        } else {
            $this->notifyError('Error al eliminar el contacto');
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
        $leadService = app(LeadService::class);

        $filters = array_filter([
            'search' => $this->search,
            'source' => $this->filterSource,
        ]);
        $leads = $leadService->getPaginated($filters);

        $sourceTypes = SourceType::cases();
        $stats = $leadService->getStats();

        return view('livewire.leads.index', [
            'leads' => $leads,
            'sourceTypes' => $sourceTypes,
            'totalLeads' => $stats['total'],
            'leadsWithDeals' => $stats['with_deals'],
            'leadsWithoutDeals' => $stats['without_deals'],
        ])->layout('components.layouts.app', ['title' => 'Contactos']);
    }
}
