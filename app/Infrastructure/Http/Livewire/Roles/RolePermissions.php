<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Roles;

use App\Infrastructure\Persistence\Eloquent\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\RoleModel;
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

    public function mount(): void
    {
        // La verificación de acceso se hace en el middleware de la ruta

        // Seleccionar el primer rol por defecto (ordenado por nivel descendente)
        $firstRole = RoleModel::orderBy('level', 'desc')->first();
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

        $role = RoleModel::with('permissions')->find($this->selectedRoleId);
        if (! $role) {
            $this->rolePermissions = [];
            $this->originalPermissions = [];

            return;
        }

        $this->rolePermissions = $role->permissions->pluck('id')->toArray();
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
        $permissions = PermissionModel::where('group', $group)->get();
        $groupPermissionIds = $permissions->pluck('id')->toArray();

        // Verificar si todos los permisos del grupo están seleccionados
        $allSelected = empty(array_diff($groupPermissionIds, $this->rolePermissions));

        if ($allSelected) {
            // Deseleccionar todos
            $this->rolePermissions = array_values(array_diff($this->rolePermissions, $groupPermissionIds));
        } else {
            // Seleccionar todos
            $this->rolePermissions = array_values(array_unique(array_merge($this->rolePermissions, $groupPermissionIds)));
        }

        $this->calculateChanges();
    }

    public function selectAll(): void
    {
        $this->rolePermissions = PermissionModel::pluck('id')->toArray();
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
     * Obtiene los cambios pendientes agrupados por categoría.
     *
     * @return array{added: array, removed: array}
     */
    public function getChangesPreviewProperty(): array
    {
        $addedIds = array_diff($this->rolePermissions, $this->originalPermissions);
        $removedIds = array_diff($this->originalPermissions, $this->rolePermissions);

        $added = [];
        $removed = [];

        if (! empty($addedIds)) {
            $addedPermissions = PermissionModel::whereIn('id', $addedIds)->get();
            foreach ($addedPermissions as $permission) {
                $group = $permission->group;
                if (! isset($added[$group])) {
                    $added[$group] = [];
                }
                $added[$group][] = $permission->display_name;
            }
        }

        if (! empty($removedIds)) {
            $removedPermissions = PermissionModel::whereIn('id', $removedIds)->get();
            foreach ($removedPermissions as $permission) {
                $group = $permission->group;
                if (! isset($removed[$group])) {
                    $removed[$group] = [];
                }
                $removed[$group][] = $permission->display_name;
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
        // Verificar permisos antes de guardar
        if (! Auth::user()?->canManageRoles()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para gestionar roles');

            return;
        }

        if (! $this->selectedRoleId) {
            return;
        }

        $role = RoleModel::find($this->selectedRoleId);
        if (! $role) {
            return;
        }

        // Sincronizar permisos
        $role->permissions()->sync($this->rolePermissions);

        // Actualizar permisos originales
        $this->originalPermissions = $this->rolePermissions;
        $this->hasChanges = false;

        $this->dispatch('notify', type: 'success', message: 'Permisos actualizados correctamente');
    }

    public function resetToDefault(): void
    {
        // Verificar permisos
        if (! Auth::user()?->canManageRoles()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para gestionar roles');

            return;
        }

        if (! $this->selectedRoleId) {
            return;
        }

        $role = RoleModel::find($this->selectedRoleId);
        if (! $role) {
            return;
        }

        // Obtener permisos por defecto según el rol
        $defaultPermissions = $this->getDefaultPermissionsForRole($role->name);

        // Obtener IDs de permisos
        $permissionIds = PermissionModel::whereIn('name', $defaultPermissions)->pluck('id')->toArray();

        $this->rolePermissions = $permissionIds;
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

        $role = RoleModel::find($roleId);
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

        if ($this->editingRoleId) {
            // Actualizar rol existente
            $role = RoleModel::find($this->editingRoleId);
            if (! $role) {
                $this->dispatch('notify', type: 'error', message: 'Rol no encontrado');

                return;
            }

            // No permitir cambiar el nombre del rol admin
            if ($role->name === 'admin' && $this->roleName !== 'admin') {
                $this->dispatch('notify', type: 'error', message: 'No puedes cambiar el nombre del rol admin');

                return;
            }

            $role->update([
                'name' => $this->roleName,
                'description' => $this->roleDescription ?: null,
                'level' => $role->name === 'admin' ? 100 : $this->roleLevel,
            ]);

            $this->dispatch('notify', type: 'success', message: 'Rol actualizado correctamente');
        } else {
            // Verificar que no exista un rol con el mismo nombre
            if (RoleModel::where('name', $this->roleName)->exists()) {
                $this->dispatch('notify', type: 'error', message: 'Ya existe un rol con ese nombre');

                return;
            }

            // Crear nuevo rol
            $role = RoleModel::create([
                'name' => $this->roleName,
                'description' => $this->roleDescription ?: null,
                'level' => $this->roleLevel,
            ]);

            // Seleccionar el nuevo rol
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

        $role = RoleModel::find($roleId);
        if (! $role) {
            return;
        }

        // No permitir eliminar rol admin
        if ($role->name === 'admin') {
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

        $role = RoleModel::find($this->deletingRoleId);
        if (! $role) {
            $this->closeDeleteRoleModal();

            return;
        }

        // No permitir eliminar rol admin
        if ($role->name === 'admin') {
            $this->dispatch('notify', type: 'error', message: 'No puedes eliminar el rol de administrador');
            $this->closeDeleteRoleModal();

            return;
        }

        // Verificar si hay usuarios con este rol
        $usersCount = \App\Models\User::where('role_id', $role->id)->count();
        if ($usersCount > 0) {
            $this->dispatch('notify', type: 'error', message: "No puedes eliminar este rol porque tiene {$usersCount} usuario(s) asignado(s)");
            $this->closeDeleteRoleModal();

            return;
        }

        // Eliminar permisos del rol
        $role->permissions()->detach();

        // Eliminar rol
        $role->delete();

        // Si el rol eliminado era el seleccionado, seleccionar otro
        if ($this->selectedRoleId === $this->deletingRoleId) {
            $firstRole = RoleModel::orderBy('level', 'desc')->first();
            $this->selectedRoleId = $firstRole?->id;
            $this->loadRolePermissions();
        }

        $this->dispatch('notify', type: 'success', message: 'Rol eliminado correctamente');
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

    /**
     * Obtiene los permisos por defecto para un rol.
     *
     * @return string[]
     */
    private function getDefaultPermissionsForRole(string $roleName): array
    {
        return match ($roleName) {
            'admin' => PermissionModel::pluck('name')->toArray(),

            'manager' => [
                'leads.view_all',
                'leads.view_own',
                'leads.create',
                'leads.update_all',
                'leads.delete_all',
                'leads.assign',
                'deals.view_all',
                'deals.view_own',
                'deals.create',
                'deals.update_all',
                'deals.delete_all',
                'deals.assign',
                'phases.manage',
                'reports.view_all',
            ],

            'sales' => [
                'leads.view_own',
                'leads.create',
                'leads.update_own',
                'leads.delete_own',
                'deals.view_own',
                'deals.create',
                'deals.update_own',
                'deals.delete_own',
                'reports.view_own',
            ],

            default => [],
        };
    }

    public function getGroupedPermissionsProperty(): Collection
    {
        return PermissionModel::getGrouped();
    }

    public function getRolesProperty(): Collection
    {
        return RoleModel::orderBy('level', 'desc')->get();
    }

    public function getSelectedRoleProperty(): ?RoleModel
    {
        return $this->selectedRoleId ? RoleModel::find($this->selectedRoleId) : null;
    }

    public function isGroupFullySelected(string $group): bool
    {
        $permissions = PermissionModel::where('group', $group)->get();
        $groupPermissionIds = $permissions->pluck('id')->toArray();

        return empty(array_diff($groupPermissionIds, $this->rolePermissions));
    }

    public function isGroupPartiallySelected(string $group): bool
    {
        $permissions = PermissionModel::where('group', $group)->get();
        $groupPermissionIds = $permissions->pluck('id')->toArray();

        $selectedInGroup = array_intersect($groupPermissionIds, $this->rolePermissions);

        return ! empty($selectedInGroup) && count($selectedInGroup) < count($groupPermissionIds);
    }

    public function getSelectedCountInGroup(string $group): int
    {
        $permissions = PermissionModel::where('group', $group)->get();
        $groupPermissionIds = $permissions->pluck('id')->toArray();

        return count(array_intersect($groupPermissionIds, $this->rolePermissions));
    }

    public function render()
    {
        return view('livewire.roles.permissions')
            ->layout('components.layouts.app', ['title' => 'Gestión de Roles']);
    }
}
