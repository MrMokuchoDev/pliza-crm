<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Roles;

use App\Application\Role\DTOs\RoleDTO;
use App\Application\Role\Services\RoleService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RolePermissions extends Component
{
    public ?string $selectedRoleId = null;

    public array $rolePermissions = [];

    public array $originalPermissions = [];

    public bool $hasChanges = false;

    // Modal para crear/editar rol
    public bool $showRoleModal = false;

    public ?string $editingRoleId = null;

    public string $roleName = '';

    public string $roleDescription = '';

    public int $roleLevel = 10;

    // Modal para eliminar rol
    public bool $showDeleteRoleModal = false;

    public ?string $deletingRoleId = null;

    private function getRoleService(): RoleService
    {
        return app(RoleService::class);
    }

    public function mount(): void
    {
        $firstRole = $this->getRoleService()->getFirst();
        if ($firstRole) {
            $this->selectedRoleId = $firstRole->id;
            $this->loadRolePermissions();
        }
    }

    public function updatedSelectedRoleId(): void
    {
        $this->loadRolePermissions();
        $this->hasChanges = false;
    }

    public function loadRolePermissions(): void
    {
        if (! $this->selectedRoleId) {
            $this->rolePermissions = [];
            $this->originalPermissions = [];

            return;
        }

        $this->rolePermissions = $this->getRoleService()->getRolePermissionIds($this->selectedRoleId);
        $this->originalPermissions = $this->rolePermissions;
        $this->hasChanges = false;
    }

    public function togglePermission(string $permissionId): void
    {
        if (in_array($permissionId, $this->rolePermissions)) {
            $this->rolePermissions = array_values(array_diff($this->rolePermissions, [$permissionId]));
        } else {
            $this->rolePermissions[] = $permissionId;
        }

        $this->calculateChanges();
    }

    public function toggleGroup(string $group): void
    {
        $service = $this->getRoleService();
        $permissions = $service->getPermissionsByGroup($group);
        $groupPermissionIds = $permissions->pluck('id')->toArray();

        $allSelected = empty(array_diff($groupPermissionIds, $this->rolePermissions));

        if ($allSelected) {
            $this->rolePermissions = array_values(array_diff($this->rolePermissions, $groupPermissionIds));
        } else {
            $this->rolePermissions = array_values(array_unique(array_merge($this->rolePermissions, $groupPermissionIds)));
        }

        $this->calculateChanges();
    }

    public function selectAll(): void
    {
        $service = $this->getRoleService();
        $allPermissions = $service->getGroupedPermissions()->flatten();
        $this->rolePermissions = $allPermissions->pluck('id')->toArray();
        $this->calculateChanges();
    }

    public function deselectAll(): void
    {
        $this->rolePermissions = [];
        $this->calculateChanges();
    }

    private function calculateChanges(): void
    {
        $added = array_diff($this->rolePermissions, $this->originalPermissions);
        $removed = array_diff($this->originalPermissions, $this->rolePermissions);

        $this->hasChanges = ! empty($added) || ! empty($removed);
    }

    /**
     * @return array{added: array, removed: array, addedCount: int, removedCount: int}
     */
    public function getChangesPreviewProperty(): array
    {
        $service = $this->getRoleService();
        $addedIds = array_diff($this->rolePermissions, $this->originalPermissions);
        $removedIds = array_diff($this->originalPermissions, $this->rolePermissions);

        $added = [];
        $removed = [];

        $groupedPermissions = $service->getGroupedPermissions();

        foreach ($groupedPermissions as $group => $permissions) {
            foreach ($permissions as $permission) {
                if (in_array($permission->id, $addedIds)) {
                    if (! isset($added[$group])) {
                        $added[$group] = [];
                    }
                    $added[$group][] = $permission->displayName;
                }
                if (in_array($permission->id, $removedIds)) {
                    if (! isset($removed[$group])) {
                        $removed[$group] = [];
                    }
                    $removed[$group][] = $permission->displayName;
                }
            }
        }

        return [
            'added' => $added,
            'removed' => $removed,
            'addedCount' => count($addedIds),
            'removedCount' => count($removedIds),
        ];
    }

    public function save(): void
    {
        if (! Auth::user()?->canManageRoles()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para gestionar roles');

            return;
        }

        if (! $this->selectedRoleId) {
            return;
        }

        $service = $this->getRoleService();
        $role = $service->getById($this->selectedRoleId);

        // Proteger rol admin: no permitir quitar permisos
        if ($role && $service->isAdminRole($role)) {
            $allPermissions = $service->getGroupedPermissions()->flatten()->pluck('id')->toArray();
            $missingPermissions = array_diff($allPermissions, $this->rolePermissions);

            if (! empty($missingPermissions)) {
                $this->dispatch('notify', type: 'error', message: 'El rol administrador debe tener todos los permisos');
                $this->rolePermissions = $allPermissions;
                $this->calculateChanges();

                return;
            }
        }

        $success = $service->syncPermissions($this->selectedRoleId, $this->rolePermissions);

        if ($success) {
            $this->originalPermissions = $this->rolePermissions;
            $this->hasChanges = false;
            $this->dispatch('notify', type: 'success', message: 'Permisos actualizados correctamente');
        } else {
            $this->dispatch('notify', type: 'error', message: 'Error al actualizar permisos');
        }
    }

    public function resetToDefault(): void
    {
        if (! Auth::user()?->canManageRoles()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para gestionar roles');

            return;
        }

        if (! $this->selectedRoleId) {
            return;
        }

        $service = $this->getRoleService();
        $role = $service->getById($this->selectedRoleId);

        if (! $role) {
            return;
        }

        $this->rolePermissions = $service->getDefaultPermissionIds($role->name);
        $this->calculateChanges();

        $this->dispatch('notify', type: 'info', message: 'Permisos restaurados a valores por defecto. Guarda para aplicar.');
    }

    // ========================================
    // Métodos para gestión de roles (CRUD)
    // ========================================

    public function openCreateRoleModal(): void
    {
        if (! Auth::user()?->canManageRoles()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para crear roles');

            return;
        }

        $this->resetRoleForm();
        $this->showRoleModal = true;
    }

    public function openEditRoleModal(string $roleId): void
    {
        if (! Auth::user()?->canManageRoles()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para editar roles');

            return;
        }

        $service = $this->getRoleService();
        $role = $service->getById($roleId);

        if (! $role) {
            return;
        }

        $this->editingRoleId = $roleId;
        $this->roleName = $role->name;
        $this->roleDescription = $role->description ?? '';
        $this->roleLevel = $role->level;
        $this->showRoleModal = true;
    }

    public function saveRole(): void
    {
        if (! Auth::user()?->canManageRoles()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para gestionar roles');

            return;
        }

        $this->validate([
            'roleName' => 'required|string|max:50|regex:/^[a-z_]+$/',
            'roleDescription' => 'nullable|string|max:255',
            'roleLevel' => 'required|integer|min:1|max:99',
        ], [
            'roleName.required' => 'El nombre del rol es obligatorio',
            'roleName.regex' => 'El nombre debe ser en minusculas y sin espacios (usa guion bajo)',
            'roleName.max' => 'El nombre no puede exceder 50 caracteres',
            'roleLevel.min' => 'El nivel debe ser al menos 1',
            'roleLevel.max' => 'El nivel no puede ser mayor a 99 (reservado para admin)',
        ]);

        $service = $this->getRoleService();

        if ($this->editingRoleId) {
            $existingRole = $service->getById($this->editingRoleId);

            if (! $existingRole) {
                $this->dispatch('notify', type: 'error', message: 'Rol no encontrado');

                return;
            }

            if ($existingRole->name === 'admin' && $this->roleName !== 'admin') {
                $this->dispatch('notify', type: 'error', message: 'No puedes cambiar el nombre del rol admin');

                return;
            }

            $role = $service->update(
                $this->editingRoleId,
                $this->roleName,
                $this->roleDescription ?: null,
                $this->roleLevel
            );

            $this->dispatch('notify', type: 'success', message: 'Rol actualizado correctamente');
        } else {
            if ($service->existsByName($this->roleName)) {
                $this->dispatch('notify', type: 'error', message: 'Ya existe un rol con ese nombre');

                return;
            }

            $role = $service->create(
                $this->roleName,
                $this->roleDescription ?: null,
                $this->roleLevel
            );

            $this->selectedRoleId = $role->id;
            $this->loadRolePermissions();

            $this->dispatch('notify', type: 'success', message: 'Rol creado correctamente');
        }

        $this->closeRoleModal();
    }

    public function closeRoleModal(): void
    {
        $this->showRoleModal = false;
        $this->resetRoleForm();
    }

    public function confirmDeleteRole(string $roleId): void
    {
        if (! Auth::user()?->canManageRoles()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para eliminar roles');

            return;
        }

        $service = $this->getRoleService();
        $role = $service->getById($roleId);

        if (! $role) {
            return;
        }

        if ($service->isAdminRole($role)) {
            $this->dispatch('notify', type: 'error', message: 'No puedes eliminar el rol de administrador');

            return;
        }

        $this->deletingRoleId = $roleId;
        $this->showDeleteRoleModal = true;
    }

    public function deleteRole(): void
    {
        if (! Auth::user()?->canManageRoles()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para eliminar roles');

            return;
        }

        if (! $this->deletingRoleId) {
            return;
        }

        $service = $this->getRoleService();
        $result = $service->delete($this->deletingRoleId);

        if ($result['success']) {
            if ($this->selectedRoleId === $this->deletingRoleId) {
                $firstRole = $service->getFirst();
                $this->selectedRoleId = $firstRole?->id;
                $this->loadRolePermissions();
            }

            $this->dispatch('notify', type: 'success', message: $result['message']);
        } else {
            $this->dispatch('notify', type: 'error', message: $result['message']);
        }

        $this->closeDeleteRoleModal();
    }

    public function closeDeleteRoleModal(): void
    {
        $this->showDeleteRoleModal = false;
        $this->deletingRoleId = null;
    }

    private function resetRoleForm(): void
    {
        $this->editingRoleId = null;
        $this->roleName = '';
        $this->roleDescription = '';
        $this->roleLevel = 10;
    }

    // ========================================
    // Computed Properties para la vista
    // ========================================

    public function getGroupedPermissionsProperty(): Collection
    {
        return $this->getRoleService()->getGroupedPermissions();
    }

    public function getRolesProperty(): Collection
    {
        return $this->getRoleService()->getAll();
    }

    public function getSelectedRoleProperty(): ?RoleDTO
    {
        return $this->selectedRoleId
            ? $this->getRoleService()->getById($this->selectedRoleId)
            : null;
    }

    public function getDeletingRoleProperty(): ?RoleDTO
    {
        return $this->deletingRoleId
            ? $this->getRoleService()->getById($this->deletingRoleId)
            : null;
    }

    public function getDeletingRoleUsersCountProperty(): int
    {
        if (! $this->deletingRoleId) {
            return 0;
        }

        $result = $this->getRoleService()->checkHasUsers($this->deletingRoleId);

        return $result['count'];
    }

    public function isGroupFullySelected(string $group): bool
    {
        $permissions = $this->getRoleService()->getPermissionsByGroup($group);
        $groupPermissionIds = $permissions->pluck('id')->toArray();

        return empty(array_diff($groupPermissionIds, $this->rolePermissions));
    }

    public function isGroupPartiallySelected(string $group): bool
    {
        $permissions = $this->getRoleService()->getPermissionsByGroup($group);
        $groupPermissionIds = $permissions->pluck('id')->toArray();

        $selectedInGroup = array_intersect($groupPermissionIds, $this->rolePermissions);

        return ! empty($selectedInGroup) && count($selectedInGroup) < count($groupPermissionIds);
    }

    public function getSelectedCountInGroup(string $group): int
    {
        $permissions = $this->getRoleService()->getPermissionsByGroup($group);
        $groupPermissionIds = $permissions->pluck('id')->toArray();

        return count(array_intersect($groupPermissionIds, $this->rolePermissions));
    }

    public function getTotalPermissionsCountProperty(): int
    {
        return $this->getRoleService()->getGroupedPermissions()->flatten()->count();
    }

    public function render()
    {
        return view('livewire.roles.permissions')
            ->layout('components.layouts.app', ['title' => 'Gestión de Roles']);
    }
}
