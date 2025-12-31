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

    // Delete group confirmation
    public bool $showDeleteGroupModal = false;
    public ?string $deletingGroupId = null;
    public ?string $targetGroupId = null;
    public int $fieldsCount = 0;

    // Field management
    public bool $showFieldModal = false;
    public ?string $editingFieldId = null;
    public ?string $fieldGroupId = null;
    public string $fieldLabel = '';
    public string $fieldType = 'text';
    public bool $fieldRequired = false;
    public bool $fieldActive = true;
    public string $fieldDefaultValue = '';
    public string $fieldOptions = ''; // Opciones para select/radio/multiselect (una por línea)
    public array $openAccordions = [];

    // Delete field confirmation
    public bool $showDeleteFieldModal = false;
    public ?string $deletingFieldId = null;
    public ?string $deletingFieldLabel = null;
    public int $deletingFieldValuesCount = 0;
    public ?int $deletingFieldOptionsCount = null;

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

    public function openDeleteGroupModal(string $groupId): void
    {
        if (! Auth::user()?->canDeleteCustomFields()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para eliminar grupos');
            return;
        }

        $service = app(CustomFieldService::class);

        // Contar campos del grupo
        $fields = $service->getFieldsByEntity($this->selectedEntityType, activeOnly: false, groupId: $groupId);
        $this->fieldsCount = count($fields);

        $this->deletingGroupId = $groupId;
        $this->targetGroupId = null;
        $this->showDeleteGroupModal = true;
    }

    public function closeDeleteGroupModal(): void
    {
        $this->showDeleteGroupModal = false;
        $this->reset(['deletingGroupId', 'targetGroupId', 'fieldsCount']);
    }

    public function confirmDeleteGroup(): void
    {
        if (! Auth::user()?->canDeleteCustomFields()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para eliminar grupos');
            return;
        }

        if (!$this->deletingGroupId) {
            return;
        }

        // Si hay campos y no se seleccionó grupo destino, mostrar error
        if ($this->fieldsCount > 0 && empty($this->targetGroupId)) {
            $this->dispatch('notify', type: 'error', message: 'Debes seleccionar un grupo destino para transferir los campos');
            return;
        }

        $service = app(CustomFieldService::class);

        try {
            $service->deleteGroup($this->deletingGroupId, $this->targetGroupId);
            $this->closeDeleteGroupModal();
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
        $this->reset(['editingFieldId', 'fieldLabel', 'fieldType', 'fieldRequired', 'fieldActive', 'fieldDefaultValue', 'fieldOptions']);
        $this->fieldGroupId = $groupId;
        $this->fieldActive = true; // Por defecto activo
        $this->resetValidation();
        $this->showFieldModal = true;
    }

    public function closeFieldModal(): void
    {
        $this->showFieldModal = false;
        $this->reset(['editingFieldId', 'fieldGroupId', 'fieldLabel', 'fieldType', 'fieldRequired', 'fieldActive', 'fieldDefaultValue', 'fieldOptions']);
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

            // Cargar opciones desde tabla dinámica si el campo requiere opciones
            $this->fieldOptions = '';
            if (in_array($field->type, ['select', 'radio', 'multiselect'])) {
                try {
                    $optionsManager = app(\App\Domain\CustomField\Services\CustomFieldOptionsTableManagerInterface::class);
                    $fieldName = \App\Domain\CustomField\ValueObjects\FieldName::fromString($field->name);

                    if ($optionsManager->tableExists($fieldName)) {
                        $options = $optionsManager->getOptions($fieldName);
                        $labels = array_column($options, 'label');
                        $this->fieldOptions = implode("\n", $labels);
                    }
                } catch (\Exception $e) {
                    // Si falla, dejar vacío
                }
            }

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
            'fieldOptions' => 'nullable|string',
        ]);

        // Procesar opciones para campos select/radio/multiselect
        $options = [];
        if (in_array($this->fieldType, ['select', 'radio', 'multiselect']) && !empty($this->fieldOptions)) {
            $rawOptions = array_values(array_filter(
                array_map('trim', explode("\n", $this->fieldOptions)),
                fn($option) => !empty($option)
            ));

            // Convertir a formato esperado por el handler: ['label' => 'X', 'value' => 'X', 'order' => N]
            foreach ($rawOptions as $index => $optionLabel) {
                $options[] = [
                    'label' => $optionLabel,
                    'value' => $optionLabel, // Por defecto, value = label
                    'order' => $index,
                ];
            }
        }

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
                    order: null,
                    options: $options
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
                    options: $options
                );
                $this->dispatch('notify', type: 'success', message: 'Campo creado correctamente');
            }

            $this->closeFieldModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function openDeleteFieldModal(string $fieldId): void
    {
        if (! Auth::user()?->canDeleteCustomFields()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para eliminar campos');
            return;
        }

        $service = app(CustomFieldService::class);
        $field = $service->getFieldById($fieldId);

        if (!$field) {
            $this->dispatch('notify', type: 'error', message: 'Campo no encontrado');
            return;
        }

        $this->deletingFieldId = $fieldId;
        $this->deletingFieldLabel = $field->label;
        $this->deletingFieldValuesCount = $service->countFieldValues($fieldId);
        $this->deletingFieldOptionsCount = $service->countFieldOptions($fieldId);
        $this->showDeleteFieldModal = true;
    }

    public function closeDeleteFieldModal(): void
    {
        $this->showDeleteFieldModal = false;
        $this->reset(['deletingFieldId', 'deletingFieldLabel', 'deletingFieldValuesCount', 'deletingFieldOptionsCount']);
    }

    public function confirmDeleteField(): void
    {
        if (! Auth::user()?->canDeleteCustomFields()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para eliminar campos');
            return;
        }

        if (!$this->deletingFieldId) {
            return;
        }

        $service = app(CustomFieldService::class);

        try {
            $service->deleteField($this->deletingFieldId);
            $this->closeDeleteFieldModal();
            $this->dispatch('notify', type: 'success', message: 'Campo eliminado correctamente');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function deleteField(string $fieldId): void
    {
        // Método legacy - redirigir al nuevo flujo
        $this->openDeleteFieldModal($fieldId);
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
