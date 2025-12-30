<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\CustomField;

use App\Application\CustomField\Services\CustomFieldService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

final class CustomFieldIndex extends Component
{
    public string $activeTab = 'groups';
    public string $selectedEntityType = 'lead';

    // Group management
    public bool $showGroupModal = false;
    public ?string $editingGroupId = null;
    public string $groupName = '';
    public int $groupOrder = 0;

    // Field management
    public bool $showFieldModal = false;
    public ?string $editingFieldId = null;
    public ?string $fieldGroupId = null;
    public string $fieldLabel = '';
    public string $fieldType = 'text';
    public bool $fieldRequired = false;
    public bool $fieldActive = true;
    public string $fieldDefaultValue = '';
    public array $openAccordions = [];

    public function mount(): void
    {
        // Verificar acceso al módulo
        if (! Auth::user()?->canAccessCustomFields()) {
            $this->redirect(route('dashboard'), navigate: true);
            return;
        }

        // Inicializar con el primer entity type disponible
        $service = app(CustomFieldService::class);
        $entityTypes = $service->getAvailableEntityTypes();

        if (!empty($entityTypes)) {
            $this->selectedEntityType = $entityTypes[0]['value'];
        }
    }

    public function changeTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function changeEntityType(string $entityType): void
    {
        $this->selectedEntityType = $entityType;
    }

    // ========== GROUP METHODS ==========

    public function openCreateGroupModal(): void
    {
        $this->reset(['editingGroupId', 'groupName', 'groupOrder']);
        $this->resetValidation();
        $this->showGroupModal = true;
    }

    public function closeGroupModal(): void
    {
        $this->showGroupModal = false;
        $this->reset(['editingGroupId', 'groupName', 'groupOrder']);
        $this->resetValidation();
    }

    public function openEditGroupModal(string $groupId): void
    {
        $service = app(CustomFieldService::class);
        $group = $service->getGroupById($groupId);

        if ($group) {
            $this->editingGroupId = $groupId;
            $this->groupName = $group->name;
            $this->groupOrder = $group->order;
            $this->showGroupModal = true;
        }
    }

    public function saveGroup(): void
    {
        // Verificar permisos
        if ($this->editingGroupId && ! Auth::user()?->canUpdateCustomFields()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para editar grupos');
            return;
        }

        if (! $this->editingGroupId && ! Auth::user()?->canCreateCustomFields()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para crear grupos');
            return;
        }

        $this->validate([
            'groupName' => 'required|string|min:1|max:255',
            'groupOrder' => 'required|integer|min:0',
        ]);

        $service = app(CustomFieldService::class);

        try {
            if ($this->editingGroupId) {
                $service->updateGroup($this->editingGroupId, $this->groupName, $this->groupOrder);
                $this->dispatch('notify', type: 'success', message: 'Grupo actualizado correctamente');
            } else {
                $service->createGroup($this->selectedEntityType, $this->groupName, $this->groupOrder);
                $this->dispatch('notify', type: 'success', message: 'Grupo creado correctamente');
            }

            $this->closeGroupModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function deleteGroup(string $groupId): void
    {
        if (! Auth::user()?->canDeleteCustomFields()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para eliminar grupos');
            return;
        }

        $service = app(CustomFieldService::class);

        try {
            $service->deleteGroup($groupId);
            $this->dispatch('notify', type: 'success', message: 'Grupo eliminado correctamente');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function updateGroupsOrder(array $groupIds): void
    {
        if (! Auth::user()?->canUpdateCustomFields()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para reordenar grupos');
            return;
        }

        $service = app(CustomFieldService::class);

        try {
            $service->reorderGroups($groupIds);
            $this->dispatch('notify', type: 'success', message: 'Orden actualizado correctamente');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    // ========== ACCORDION METHODS ==========

    public function toggleAccordion(string $groupId): void
    {
        if (in_array($groupId, $this->openAccordions)) {
            $this->openAccordions = array_filter($this->openAccordions, fn($id) => $id !== $groupId);
        } else {
            $this->openAccordions[] = $groupId;
        }
    }

    // ========== FIELD METHODS ==========

    public function openCreateFieldModal(string $groupId): void
    {
        $this->reset(['editingFieldId', 'fieldLabel', 'fieldType', 'fieldRequired', 'fieldActive', 'fieldDefaultValue']);
        $this->fieldGroupId = $groupId;
        $this->fieldActive = true; // Por defecto activo
        $this->resetValidation();
        $this->showFieldModal = true;
    }

    public function closeFieldModal(): void
    {
        $this->showFieldModal = false;
        $this->reset(['editingFieldId', 'fieldGroupId', 'fieldLabel', 'fieldType', 'fieldRequired', 'fieldActive', 'fieldDefaultValue']);
        $this->resetValidation();
    }

    public function openEditFieldModal(string $fieldId): void
    {
        $service = app(CustomFieldService::class);
        $field = $service->getFieldById($fieldId);

        if ($field) {
            $this->editingFieldId = $fieldId;
            $this->fieldGroupId = $field->groupId;
            $this->fieldLabel = $field->label;
            $this->fieldType = $field->type;
            $this->fieldRequired = $field->isRequired;
            $this->fieldActive = $field->isActive;
            $this->fieldDefaultValue = $field->defaultValue ?? '';
            $this->resetValidation();
            $this->showFieldModal = true;
        }
    }

    public function saveField(): void
    {
        // Verificar permisos
        if ($this->editingFieldId && ! Auth::user()?->canUpdateCustomFields()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para editar campos');
            return;
        }

        if (! $this->editingFieldId && ! Auth::user()?->canCreateCustomFields()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para crear campos');
            return;
        }

        $this->validate([
            'fieldLabel' => 'required|string|min:1|max:255',
            'fieldType' => 'required|string|in:text,textarea,email,tel,number,select,radio,multiselect,checkbox,date,url',
            'fieldRequired' => 'boolean',
            'fieldActive' => 'boolean',
            'fieldDefaultValue' => 'nullable|string',
        ]);

        $service = app(CustomFieldService::class);

        try {
            if ($this->editingFieldId) {
                $service->updateField(
                    id: $this->editingFieldId,
                    label: $this->fieldLabel,
                    groupId: $this->fieldGroupId,
                    defaultValue: $this->fieldDefaultValue ?: null,
                    isRequired: $this->fieldRequired,
                    isActive: $this->fieldActive,
                    validationRules: null,
                    order: null
                );
                $this->dispatch('notify', type: 'success', message: 'Campo actualizado correctamente');
            } else {
                $service->createField(
                    entityType: $this->selectedEntityType,
                    label: $this->fieldLabel,
                    type: $this->fieldType,
                    groupId: $this->fieldGroupId,
                    defaultValue: $this->fieldDefaultValue ?: null,
                    isRequired: $this->fieldRequired,
                    validationRules: null,
                    order: null,
                    options: []
                );
                $this->dispatch('notify', type: 'success', message: 'Campo creado correctamente');
            }

            $this->closeFieldModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function deleteField(string $fieldId): void
    {
        if (! Auth::user()?->canDeleteCustomFields()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para eliminar campos');
            return;
        }

        $service = app(CustomFieldService::class);

        try {
            $service->deleteField($fieldId);
            $this->dispatch('notify', type: 'success', message: 'Campo eliminado correctamente');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function updateFieldsOrder(string $groupId, array $fieldIds): void
    {
        if (! Auth::user()?->canUpdateCustomFields()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para reordenar campos');
            return;
        }

        $service = app(CustomFieldService::class);

        try {
            // Crear array con orden: [field_id => position]
            $order = [];
            foreach ($fieldIds as $index => $fieldId) {
                $order[$fieldId] = $index + 1;
            }

            $service->reorderFields($order);
            $this->dispatch('notify', type: 'success', message: 'Orden actualizado correctamente');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function toggleFieldActive(string $fieldId, bool $isActive): void
    {
        if (! Auth::user()?->canUpdateCustomFields()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para cambiar el estado de campos');
            return;
        }

        $service = app(CustomFieldService::class);

        try {
            $service->toggleFieldActive($fieldId, $isActive);
            $message = $isActive ? 'Campo activado correctamente' : 'Campo inactivado correctamente';
            $this->dispatch('notify', type: 'success', message: $message);
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $service = app(CustomFieldService::class);
        $entityTypes = $service->getAvailableEntityTypes();
        $groups = $service->getGroupsByEntity($this->selectedEntityType);

        // Obtener campos agrupados por grupo (incluir inactivos para mostrar badge de sistema)
        $fieldsByGroup = [];
        foreach ($groups as $group) {
            $fieldsByGroup[$group->id] = $service->getFieldsByEntity(
                $this->selectedEntityType,
                activeOnly: false, // Mostrar todos para poder ver campos del sistema inactivos
                groupId: $group->id
            );
        }

        return view('livewire.custom-field.custom-field-index', [
            'entityTypes' => $entityTypes,
            'groups' => $groups,
            'fieldsByGroup' => $fieldsByGroup,
            'canCreate' => Auth::user()?->canCreateCustomFields() ?? false,
            'canUpdate' => Auth::user()?->canUpdateCustomFields() ?? false,
            'canDelete' => Auth::user()?->canDeleteCustomFields() ?? false,
        ])->layout('components.layouts.app', ['title' => 'Campos Personalizados']);
    }
}
