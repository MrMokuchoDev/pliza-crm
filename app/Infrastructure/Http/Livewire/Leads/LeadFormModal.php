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

    public ?string $name = '';

    public ?string $email = '';

    public ?string $phone = '';

    public ?string $message = '';

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
        $emailUniqueRule = 'unique:leads,email';
        if ($this->leadId) {
            $emailUniqueRule .= ',' . $this->leadId;
        }

        return [
            'name' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', 'max:255', $emailUniqueRule],
            'phone' => ['nullable', 'string', 'min:7', 'max:20', 'regex:/^\+?[0-9\s\-\(\)]+$/'],
            'message' => 'nullable|string|max:5000',
            'assigned_to' => 'nullable|exists:users,uuid',
        ];
    }

    protected function messages(): array
    {
        return [
            'email.email' => 'El email debe ser válido.',
            'email.unique' => 'Este email ya está registrado en otro contacto.',
            'phone.min' => 'El teléfono debe tener al menos 7 dígitos.',
            'phone.regex' => 'El teléfono solo puede contener números, espacios, guiones y paréntesis.',
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
                $this->name = $lead->name ?? '';
                $this->email = $lead->email ?? '';
                $this->phone = $lead->phone ?? '';
                $this->message = $lead->message ?? '';
                $this->assigned_to = $lead->assigned_to;
            }
        } else {
            // Para nuevos leads siempre puede editar
            $this->canEdit = true;
            // Auto-asignar al usuario actual si no puede asignar a otros
            if (!$this->canAssign) {
                $this->assigned_to = Auth::user()?->uuid;
            }
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

        // Validar que al menos uno de los datos de contacto esté presente
        if (empty($this->email) && empty($this->phone)) {
            $this->addError('email', 'Debes proporcionar al menos un email o teléfono.');
            $this->addError('phone', 'Debes proporcionar al menos un email o teléfono.');

            return;
        }

        $this->validate();

        $leadService = app(LeadService::class);

        // Si no puede asignar, usar el usuario actual para nuevos leads
        $assignedTo = $this->canAssign ? $this->assigned_to : ($this->leadId ? null : Auth::user()?->uuid);

        $leadData = new LeadData(
            name: $this->name ?: null,
            email: $this->email ?: null,
            phone: $this->phone ?: null,
            message: $this->message ?: null,
            sourceType: $this->leadId ? null : SourceType::MANUAL,
            assignedTo: $assignedTo,
        );

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
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->message = '';
        $this->assigned_to = null;
        $this->canEdit = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.leads.form-modal');
    }
}
