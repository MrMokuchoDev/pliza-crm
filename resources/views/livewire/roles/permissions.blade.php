<div class="w-full" x-data="{
    expandedGroups: {},
    allGroups: @js($this->groupedPermissions->keys()->toArray()),
    expandAll() {
        this.allGroups.forEach(group => this.expandedGroups[group] = true);
    },
    collapseAll() {
        this.allGroups.forEach(group => this.expandedGroups[group] = false);
    }
}">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-4">
        <div class="p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Permisos por Rol</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Configura los permisos de cada rol del sistema</p>
                </div>

                <!-- Role Selector + Actions -->
                <div class="flex items-center gap-3">
                    <label for="role-select" class="text-sm font-medium text-gray-700 whitespace-nowrap">Rol:</label>
                    <select id="role-select"
                            wire:model.live="selectedRoleId"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm min-w-[180px]">
                        @foreach($this->roles as $role)
                            <option value="{{ $role->id }}">
                                {{ ucfirst($role->name) }}
                                @if($role->level >= 100)
                                    (Super Admin)
                                @elseif($role->level >= 50)
                                    (Gerente)
                                @else
                                    (Vendedor)
                                @endif
                            </option>
                        @endforeach
                    </select>

                    <!-- Botón Nuevo Rol -->
                    <button wire:click="openCreateRoleModal"
                            class="inline-flex items-center gap-1.5 px-3 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="hidden sm:inline">Nuevo Rol</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($this->selectedRole)
        <!-- Role Info & Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-4">
            <div class="p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <!-- Role Badge -->
                    <div class="flex items-center gap-3">
                        @php
                            $roleColors = [
                                'admin' => 'from-red-500 to-rose-600',
                                'manager' => 'from-blue-500 to-indigo-600',
                                'sales' => 'from-green-500 to-emerald-600',
                            ];
                            $bgColor = $roleColors[$this->selectedRole->name] ?? 'from-gray-500 to-gray-600';
                        @endphp
                        <div class="w-12 h-12 bg-gradient-to-br {{ $bgColor }} rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-sm">
                            {{ strtoupper(substr($this->selectedRole->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="font-semibold text-gray-900">{{ ucfirst($this->selectedRole->name) }}</h2>
                                <!-- Botones Editar/Eliminar Rol -->
                                <button wire:click="openEditRoleModal('{{ $this->selectedRole->id }}')"
                                        class="p-1 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded transition"
                                        title="Editar rol">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @if($this->selectedRole->name !== 'admin')
                                    <button wire:click="confirmDeleteRole('{{ $this->selectedRole->id }}')"
                                            class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition"
                                            title="Eliminar rol">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500">{{ $this->selectedRole->description ?? 'Sin descripcion' }}</p>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="flex flex-wrap items-center gap-2">
                        <button @click="expandAll()"
                                class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                            Expandir Todo
                        </button>
                        <button @click="collapseAll()"
                                class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                            Colapsar Todo
                        </button>
                        <span class="w-px h-5 bg-gray-200"></span>
                        <button wire:click="selectAll"
                                class="px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                            Todos
                        </button>
                        <button wire:click="deselectAll"
                                class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                            Ninguno
                        </button>
                        <button wire:click="resetToDefault"
                                class="px-3 py-1.5 text-xs font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg transition">
                            Defecto
                        </button>
                    </div>
                </div>

                <!-- Stats -->
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-gray-500">
                            <span class="font-semibold text-gray-900">{{ count($rolePermissions) }}</span>
                            de
                            <span class="font-semibold text-gray-900">{{ \App\Infrastructure\Persistence\Eloquent\PermissionModel::count() }}</span>
                            permisos activos
                        </span>
                        @if($hasChanges)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-xs font-medium">
                                <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                                Cambios sin guardar
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions Accordion Grid -->
        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
            @php
                $groupIcons = [
                    'Contactos' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
                    'Negocios' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    'Usuarios' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
                    'Fases' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>',
                    'Sitios' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>',
                    'Reportes' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                    'Sistema' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
                ];
            @endphp

            @foreach($this->groupedPermissions as $group => $permissions)
                @php
                    $icon = $groupIcons[$group] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>';
                    $selectedCount = $this->getSelectedCountInGroup($group);
                    $totalCount = $permissions->count();
                    $isFullySelected = $this->isGroupFullySelected($group);
                    $isPartiallySelected = $this->isGroupPartiallySelected($group);
                @endphp

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                     x-init="if (expandedGroups['{{ $group }}'] === undefined) expandedGroups['{{ $group }}'] = false">

                    <!-- Accordion Header (clickable) -->
                    <button @click="expandedGroups['{{ $group }}'] = !expandedGroups['{{ $group }}']"
                            class="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center
                                {{ $isFullySelected ? 'bg-blue-100' : ($isPartiallySelected ? 'bg-amber-50' : 'bg-gray-100') }}">
                                <svg class="w-5 h-5 {{ $isFullySelected ? 'text-blue-600' : ($isPartiallySelected ? 'text-amber-600' : 'text-gray-500') }}"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    {!! $icon !!}
                                </svg>
                            </div>
                            <div class="text-left">
                                <h3 class="font-semibold text-gray-900 text-sm">{{ $group }}</h3>
                                <p class="text-xs text-gray-500">
                                    <span class="{{ $isFullySelected ? 'text-blue-600 font-medium' : ($isPartiallySelected ? 'text-amber-600 font-medium' : '') }}">
                                        {{ $selectedCount }}/{{ $totalCount }}
                                    </span>
                                    permisos
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Status badge -->
                            @if($isFullySelected)
                                <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">Todos</span>
                            @elseif($isPartiallySelected)
                                <span class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded-full">Parcial</span>
                            @else
                                <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-500 rounded-full">Ninguno</span>
                            @endif

                            <!-- Chevron -->
                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                                 :class="{ 'rotate-180': expandedGroups['{{ $group }}'] }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    <!-- Accordion Content -->
                    <div x-show="expandedGroups['{{ $group }}']"
                         x-collapse
                         x-cloak>
                        <div class="px-4 pb-3 border-t border-gray-100">
                            <!-- Group Toggle Button -->
                            <div class="py-2 mb-1 flex justify-end">
                                <button wire:click="toggleGroup('{{ $group }}')"
                                        class="text-xs font-medium px-2 py-1 rounded transition
                                            {{ $isFullySelected ? 'text-red-600 hover:bg-red-50' : 'text-blue-600 hover:bg-blue-50' }}">
                                    {{ $isFullySelected ? 'Quitar todos' : 'Seleccionar todos' }}
                                </button>
                            </div>

                            <!-- Permissions List -->
                            <div class="space-y-1">
                                @foreach($permissions as $permission)
                                    <label class="flex items-center gap-3 p-2 rounded-lg cursor-pointer hover:bg-gray-50 transition group">
                                        <input type="checkbox"
                                               wire:click="togglePermission('{{ $permission->id }}')"
                                               {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                                        <div class="flex-1 min-w-0">
                                            <span class="block text-sm text-gray-900 group-hover:text-blue-600 transition">
                                                {{ $permission->display_name }}
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Save Bar with Preview (Sticky) -->
        @if($hasChanges)
            @php $preview = $this->changesPreview; @endphp
            <div class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur border-t border-gray-200 shadow-lg z-40">
                <!-- Changes Preview -->
                <div class="max-w-6xl mx-auto px-4 py-3 border-b border-gray-100">
                    <div class="flex items-start gap-4">
                        <div class="flex items-center gap-2 text-sm font-medium text-gray-700">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Cambios pendientes:
                        </div>

                        <div class="flex-1 flex flex-wrap gap-3 text-xs">
                            @if($preview['addedCount'] > 0)
                                <div class="flex items-start gap-1">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-green-100 text-green-700 rounded font-medium whitespace-nowrap">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        {{ $preview['addedCount'] }} agregados
                                    </span>
                                    <span class="text-gray-500">
                                        @foreach($preview['added'] as $group => $perms)
                                            <span class="font-medium text-gray-700">{{ $group }}:</span>
                                            {{ implode(', ', array_slice($perms, 0, 2)) }}{{ count($perms) > 2 ? ' +' . (count($perms) - 2) : '' }}@if(!$loop->last); @endif
                                        @endforeach
                                    </span>
                                </div>
                            @endif

                            @if($preview['removedCount'] > 0)
                                <div class="flex items-start gap-1">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-red-100 text-red-700 rounded font-medium whitespace-nowrap">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                        {{ $preview['removedCount'] }} quitados
                                    </span>
                                    <span class="text-gray-500">
                                        @foreach($preview['removed'] as $group => $perms)
                                            <span class="font-medium text-gray-700">{{ $group }}:</span>
                                            {{ implode(', ', array_slice($perms, 0, 2)) }}{{ count($perms) > 2 ? ' +' . (count($perms) - 2) : '' }}@if(!$loop->last); @endif
                                        @endforeach
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-end gap-3">
                    <button wire:click="loadRolePermissions"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Descartar
                    </button>
                    <button wire:click="save"
                            class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Guardar Cambios
                    </button>
                </div>
            </div>
            <!-- Spacer -->
            <div class="h-32"></div>
        @endif
    @else
        <!-- No Role Selected -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">Selecciona un rol</h3>
            <p class="text-gray-500">Elige un rol del selector para gestionar sus permisos</p>
        </div>
    @endif

    <!-- Modal Crear/Editar Rol -->
    @if($showRoleModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeRoleModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit="saveRole">
                        <div class="bg-white px-6 pt-6 pb-4">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ $editingRoleId ? 'Editar Rol' : 'Crear Nuevo Rol' }}
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        {{ $editingRoleId ? 'Modifica los datos del rol' : 'Define un nuevo rol para el sistema' }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <!-- Nombre del Rol -->
                                <div>
                                    <label for="roleName" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nombre del Rol <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="roleName"
                                           wire:model="roleName"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                           placeholder="ej: supervisor, soporte, etc."
                                           {{ $editingRoleId && $this->selectedRole?->name === 'admin' ? 'disabled' : '' }}>
                                    <p class="text-xs text-gray-500 mt-1">Solo minusculas y guion bajo, sin espacios</p>
                                    @error('roleName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Descripción -->
                                <div>
                                    <label for="roleDescription" class="block text-sm font-medium text-gray-700 mb-1">
                                        Descripcion
                                    </label>
                                    <input type="text"
                                           id="roleDescription"
                                           wire:model="roleDescription"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                           placeholder="Descripcion breve del rol">
                                    @error('roleDescription') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Nivel -->
                                <div>
                                    <label for="roleLevel" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nivel de Jerarquia <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number"
                                           id="roleLevel"
                                           wire:model="roleLevel"
                                           min="1"
                                           max="99"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                           {{ $editingRoleId && $this->selectedRole?->name === 'admin' ? 'disabled' : '' }}>
                                    <p class="text-xs text-gray-500 mt-1">1-99 (mayor = mas permisos). Admin es 100.</p>
                                    @error('roleLevel') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                            <button type="button" wire:click="closeRoleModal"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                {{ $editingRoleId ? 'Actualizar' : 'Crear Rol' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Eliminar Rol -->
    @if($showDeleteRoleModal)
        @php
            $deletingRole = \App\Infrastructure\Persistence\Eloquent\RoleModel::find($deletingRoleId);
            $usersCount = $deletingRole ? \App\Models\User::where('role_id', $deletingRole->id)->count() : 0;
        @endphp
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeDeleteRoleModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Eliminar Rol</h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    Estas a punto de eliminar el rol <strong>{{ $deletingRole?->name ?? 'desconocido' }}</strong>.
                                </p>
                            </div>
                        </div>

                        @if($usersCount > 0)
                            <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                                <p class="text-sm text-amber-800">
                                    <strong>Atencion:</strong> Este rol tiene {{ $usersCount }} usuario(s) asignado(s).
                                    Debes reasignarlos a otro rol antes de eliminar este.
                                </p>
                            </div>
                        @endif
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <button type="button" wire:click="closeDeleteRoleModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        @if($usersCount === 0)
                            <button type="button" wire:click="deleteRole"
                                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                                Eliminar Rol
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
