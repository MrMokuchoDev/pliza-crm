<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Leads;

use App\Application\Lead\Services\LeadService;
use App\Domain\Lead\ValueObjects\SourceType;
use App\Infrastructure\Http\Livewire\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class LeadIndex extends Component
{
    use HasNotifications;
    use WithPagination;

    public function mount(): void
    {
        // Verificar acceso al módulo
        if (! Auth::user()?->canAccessLeads()) {
            $this->redirect(route('dashboard'), navigate: true);
        }
    }

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
        // Verificar permiso antes de abrir el modal
        $leadService = app(LeadService::class);
        $lead = $leadService->find($id);

        if (! $lead) {
            $this->notifyError('Contacto no encontrado');

            return;
        }

        if (! Auth::user()?->canEditLead($lead->assigned_to)) {
            $this->notifyError('No tienes permiso para editar este contacto');

            return;
        }

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
        $lead = $leadService->find($id);

        if (! $lead) {
            $this->notifyError('Contacto no encontrado');

            return;
        }

        if (! Auth::user()?->canDeleteLead($lead->assigned_to)) {
            $this->notifyError('No tienes permiso para eliminar este contacto');

            return;
        }

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
        $lead = $leadService->find($this->deletingId);

        // Verificación adicional de seguridad
        if (! $lead || ! Auth::user()?->canDeleteLead($lead->assigned_to)) {
            $this->notifyError('No tienes permiso para eliminar este contacto');
            $this->closeDeleteModal();

            return;
        }

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
        $user = Auth::user();

        $filters = array_filter([
            'search' => $this->search,
            'source' => $this->filterSource,
        ]);

        // Determinar si el usuario solo puede ver sus propios leads
        $userUuid = $user?->uuid;
        $onlyOwn = $user && ! $user->canViewAllLeads();

        $leads = $leadService->getPaginated($filters, 10, $userUuid, $onlyOwn);

        $sourceTypes = SourceType::cases();
        $stats = $leadService->getStats();

        return view('livewire.leads.index', [
            'leads' => $leads,
            'sourceTypes' => $sourceTypes,
            'totalLeads' => $stats['total'],
            'leadsWithDeals' => $stats['with_deals'],
            'leadsWithoutDeals' => $stats['without_deals'],
            'canCreate' => $user?->canCreateLeads() ?? false,
            'canEdit' => $user?->canEditLeads() ?? false,
            'canDelete' => $user?->canDeleteLeads() ?? false,
            'canCreateDeals' => $user?->canCreateDeals() ?? false,
        ])->layout('components.layouts.app', ['title' => 'Contactos']);
    }
}
