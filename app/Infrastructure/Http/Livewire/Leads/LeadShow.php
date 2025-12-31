<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Leads;

use App\Application\CustomField\Services\CustomFieldService;
use App\Application\Lead\Services\LeadService;
use App\Application\Note\DTOs\NoteData;
use App\Application\Note\Services\NoteService;
use App\Domain\User\ValueObjects\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class LeadShow extends Component
{
    public $lead = null;

    public string $leadId;

    public array $customFieldGroups = [];

    public bool $canEditLead = false;

    public bool $canDeleteLead = false;

    // Note form
    public string $noteContent = '';

    public ?string $editingNoteId = null;

    public bool $showDeleteModal = false;

    public bool $showDeleteNoteModal = false;

    public ?string $deletingNoteId = null;

    protected function rules(): array
    {
        return [
            'noteContent' => 'required|string|min:1|max:5000',
        ];
    }

    public function mount(string $id): void
    {
        $this->leadId = $id;
        $this->loadLead();
    }

    public function loadLead(): void
    {
        $leadService = app(LeadService::class);
        $this->lead = $leadService->findWithRelations($this->leadId);

        // Calcular permisos de edición/eliminación del lead
        $this->calculateLeadPermissions();

        // Cargar custom fields agrupados
        $this->loadCustomFields();
    }

    /**
     * Carga los custom fields agrupados por bloques con sus valores.
     */
    protected function loadCustomFields(): void
    {
        if (!$this->lead) {
            return;
        }

        $customFieldService = app(CustomFieldService::class);

        // Obtener grupos de custom fields para leads
        $groups = $customFieldService->getGroupsByEntity('lead');

        // Cargar valores actuales del lead
        $customFieldValues = $this->lead->customFieldValues ?? collect();

        $this->customFieldGroups = [];

        foreach ($groups as $group) {
            // Obtener campos del grupo
            $fields = $customFieldService->getFieldsByEntity('lead', true, $group->id);

            // Agregar valores a los campos
            $fieldsWithValues = [];
            foreach ($fields as $field) {
                // Excluir campos que ya se muestran en el card de contacto superior
                // cf_lead_1 = Nombre, cf_lead_2 = Email, cf_lead_3 = Teléfono
                if (in_array($field->name, ['cf_lead_1', 'cf_lead_2', 'cf_lead_3'])) {
                    continue;
                }

                $value = $customFieldValues->firstWhere('custom_field_id', $field->id);

                // Determinar si el campo necesita opciones
                $needsOptions = in_array($field->type, ['select', 'multiselect', 'radio', 'checkbox']);

                // Obtener opciones y convertirlas a arrays simples
                $options = [];
                if ($needsOptions) {
                    $optionDtos = $customFieldService->getOptions($field->id);
                    foreach ($optionDtos as $optionDto) {
                        $options[] = [
                            'value' => $optionDto->value,
                            'label' => $optionDto->label,
                        ];
                    }
                }

                $fieldsWithValues[] = [
                    'field' => $field->toArray(), // Convertir DTO a array
                    'value' => $value?->value,
                    'options' => $options,
                ];
            }

            // Solo agregar grupos que tengan campos
            if (count($fieldsWithValues) > 0) {
                $this->customFieldGroups[] = [
                    'group' => $group->toArray(), // Convertir DTO a array
                    'fields' => $fieldsWithValues,
                ];
            }
        }
    }

    /**
     * Calcula si el usuario puede editar/eliminar el lead actual.
     */
    protected function calculateLeadPermissions(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user || !$this->lead) {
            $this->canEditLead = false;
            $this->canDeleteLead = false;
            return;
        }

        // Puede editar si tiene permiso update_all O el lead está asignado a él
        $this->canEditLead = $user->hasPermission(Permission::LEADS_UPDATE_ALL)
            || ($this->lead->assigned_to && $this->lead->assigned_to === $user->uuid);

        // Puede eliminar si tiene permiso delete_all O el lead está asignado a él
        $this->canDeleteLead = $user->hasPermission(Permission::LEADS_DELETE_ALL)
            || ($this->lead->assigned_to && $this->lead->assigned_to === $user->uuid);
    }

    /**
     * Verifica si el usuario puede editar un deal específico.
     */
    public function canEditDeal(string $dealAssignedTo = null): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Primero verificar que tenga acceso al módulo de deals
        if (!$user->canAccessDeals()) {
            return false;
        }

        // Puede editar si tiene permiso update_all O (update_own Y el deal está asignado a él)
        return $user->hasPermission(Permission::DEALS_UPDATE_ALL)
            || ($user->hasPermission(Permission::DEALS_UPDATE_OWN) && $dealAssignedTo && $dealAssignedTo === $user->uuid);
    }

    public function openCreateDealModal(): void
    {
        $this->dispatch('openDealModal', leadId: $this->leadId);
    }

    public function openEditDealModal(string $dealId): void
    {
        $this->dispatch('openDealModal', dealId: $dealId);
    }

    public function addNote(): void
    {
        $this->validate();

        $noteService = app(NoteService::class);
        $noteData = new NoteData(
            leadId: $this->leadId,
            content: $this->noteContent,
        );

        if ($this->editingNoteId) {
            $noteService->update($this->editingNoteId, $noteData);
            $this->dispatch('notify', type: 'success', message: 'Nota actualizada');
        } else {
            $noteService->create($noteData);
            $this->dispatch('notify', type: 'success', message: 'Nota agregada');
        }

        $this->resetNoteForm();
        $this->loadLead();
    }

    public function editNote(string $noteId): void
    {
        $noteService = app(NoteService::class);
        $note = $noteService->find($noteId);
        if ($note) {
            $this->editingNoteId = $noteId;
            $this->noteContent = $note->content;
        }
    }

    public function cancelEditNote(): void
    {
        $this->resetNoteForm();
    }

    public function confirmDeleteNote(string $noteId): void
    {
        $this->deletingNoteId = $noteId;
        $this->showDeleteNoteModal = true;
    }

    public function deleteNote(): void
    {
        if ($this->deletingNoteId) {
            $noteService = app(NoteService::class);
            $noteService->delete($this->deletingNoteId);
            $this->dispatch('notify', type: 'success', message: 'Nota eliminada');
            $this->showDeleteNoteModal = false;
            $this->deletingNoteId = null;
            $this->loadLead();
        }
    }

    public function openEditModal(): void
    {
        $this->dispatch('openLeadModal', leadId: $this->leadId);
    }

    #[On('leadSaved')]
    #[On('dealSaved')]
    public function onLeadSaved(): void
    {
        $this->loadLead();
    }

    public function openDeleteModal(): void
    {
        $this->showDeleteModal = true;
    }

    public function deleteLead(): void
    {
        if ($this->lead) {
            $leadService = app(LeadService::class);
            $result = $leadService->delete($this->lead->id);

            if ($result['success']) {
                $this->dispatch('notify', type: 'success', message: 'Contacto y sus negocios eliminados');
                $this->redirect(route('leads.index'), navigate: true);
            } else {
                $this->dispatch('notify', type: 'error', message: 'Error al eliminar el contacto');
            }
        }
    }

    private function resetNoteForm(): void
    {
        $this->noteContent = '';
        $this->editingNoteId = null;
    }

    public function render()
    {
        if (! $this->lead) {
            return view('livewire.leads.not-found')
                ->layout('components.layouts.app', ['title' => 'Contacto no encontrado']);
        }

        $user = Auth::user();

        return view('livewire.leads.show', [
            'canAccessDeals' => $user?->canAccessDeals() ?? false,
            'canCreateDeals' => $user?->canCreateDeals() ?? false,
        ])->layout('components.layouts.app', ['title' => 'Detalle del Contacto']);
    }
}
