<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Leads;

use App\Application\Lead\DTOs\LeadData;
use App\Application\Lead\Services\LeadService;
use App\Domain\Lead\ValueObjects\SourceType;
use App\Domain\User\ValueObjects\Permission;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class LeadFormModal extends Component
{
    public bool $show = false;

    public ?string $leadId = null;

    // Array dinámico para custom field values (cf_lead_1 => 'valor', cf_lead_2 => 'valor', etc.)
    public array $customFieldValues = [];

    public ?string $assigned_to = null;

    public bool $canAssign = false;

    public bool $canEdit = true;

    public Collection $assignableUsers;

    public function mount(): void
    {
        $this->assignableUsers = collect();
        $this->canAssign = Auth::user()?->canAssignLeads() ?? false;

        if ($this->canAssign) {
            $this->loadAssignableUsers();
        }
    }

    protected function loadAssignableUsers(): void
    {
        $this->assignableUsers = User::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Determina si el usuario actual puede editar el lead.
     */
    protected function determineCanEdit(?string $leadAssignedTo): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Si tiene permiso de actualizar todos los leads, puede editar
        if ($user->hasPermission(Permission::LEADS_UPDATE_ALL)) {
            return true;
        }

        // Si el lead está asignado al usuario actual, puede editar
        if ($leadAssignedTo && $leadAssignedTo === $user->uuid) {
            return true;
        }

        return false;
    }

    protected function rules(): array
    {
        return [
            'customFieldValues.*' => 'nullable',
            'assigned_to' => 'nullable|exists:users,uuid',
        ];
    }

    #[On('openLeadModal')]
    public function open(?string $leadId = null): void
    {
        $this->resetForm();

        // Recargar permisos y usuarios en cada apertura
        $this->canAssign = Auth::user()?->canAssignLeads() ?? false;
        if ($this->canAssign) {
            $this->loadAssignableUsers();
        }

        if ($leadId) {
            $leadService = app(LeadService::class);
            $lead = $leadService->find($leadId);
            if ($lead) {
                // Verificar si puede editar este lead
                $this->canEdit = $this->determineCanEdit($lead->assigned_to);

                if (!$this->canEdit) {
                    $this->dispatch('notify', type: 'error', message: 'No tienes permiso para editar este contacto.');
                    return;
                }

                $this->leadId = $leadId;

                // Cargar custom field values dinámicamente desde la relación
                foreach ($lead->customFieldValues as $cfValue) {
                    // Obtener el nombre del custom field
                    $fieldName = $cfValue->customField->name ?? null;
                    if ($fieldName) {
                        $this->customFieldValues[$fieldName] = $cfValue->value ?? '';
                    }
                }

                $this->assigned_to = $lead->assigned_to;
            }
        } else {
            // Para nuevos leads siempre puede editar
            $this->canEdit = true;
            // Asignar por defecto al usuario actual al crear nuevo contacto
            $this->assigned_to = Auth::user()?->uuid;
        }

        $this->show = true;
    }

    public function save(): void
    {
        // Validación de seguridad: verificar permisos antes de guardar
        if ($this->leadId && !$this->canEdit) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para editar este contacto.');
            $this->close();
            return;
        }

        $this->validate();

        $leadService = app(LeadService::class);

        // Si no puede asignar, usar el usuario actual para nuevos leads
        $assignedTo = $this->canAssign ? $this->assigned_to : ($this->leadId ? null : Auth::user()?->uuid);

        // Preparar datos: custom field values + campos del sistema
        $data = array_merge($this->customFieldValues, [
            'source_type' => $this->leadId ? null : SourceType::MANUAL->value,
            'assigned_to' => $assignedTo,
        ]);

        // Crear DTO desde array (el DTO ahora separa automáticamente custom fields de campos del sistema)
        $leadData = LeadData::fromArray($data);

        if ($this->leadId) {
            $leadService->update($this->leadId, $leadData);
            $this->dispatch('notify', type: 'success', message: 'Contacto actualizado');
        } else {
            $leadService->create($leadData);
            $this->dispatch('notify', type: 'success', message: 'Contacto creado');
        }

        $this->close();
        $this->dispatch('leadSaved');
    }

    public function close(): void
    {
        $this->show = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->leadId = null;
        $this->customFieldValues = [];
        $this->assigned_to = null;
        $this->canEdit = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.leads.form-modal');
    }
}
