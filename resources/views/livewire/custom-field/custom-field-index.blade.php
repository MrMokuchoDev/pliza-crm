<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Campos Personalizados</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Gestiona campos personalizados para tus entidades
            </p>
        </div>
    </div>

    {{-- Entity Type Selector --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Tipo de Entidad
        </label>
        <select wire:model.live="selectedEntityType"
                class="w-full max-w-xs px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent">
            @foreach($entityTypes as $type)
                <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
            @endforeach
        </select>
    </div>

    {{-- Tabs --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        {{-- Tab Headers --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px" aria-label="Tabs">
                <button wire:click="changeTab('groups')"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'groups' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    Grupos de Campos
                </button>
                <button wire:click="changeTab('fields')"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'fields' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    Campos
                </button>
                {{-- TODO: Implementar gestión de Entity Types
                     Este feature requiere integración con el sistema de módulos
                     para leer dinámicamente los módulos disponibles (Lead, Deal, etc.)
                <button wire:click="changeTab('entity-types')"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'entity-types' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    Tipos de Entidad
                </button>
                --}}
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="p-6 max-h-[calc(100vh-400px)] overflow-y-auto">
            @if($activeTab === 'groups')
                {{-- Groups Tab --}}
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Grupos para {{ collect($entityTypes)->firstWhere('value', $selectedEntityType)['label'] ?? $selectedEntityType }}
                        </h3>
                        @if($canCreate)
                            <button wire:click="openCreateGroupModal"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                                Nuevo Grupo
                            </button>
                        @endif
                    </div>

                    @if(count($groups) === 0)
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay grupos</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Crea un grupo para organizar tus campos personalizados
                            </p>
                        </div>
                    @else
                        <div id="groups-list"
                             class="space-y-3"
                             x-data
                             x-init="
                                new Sortable($el, {
                                    animation: 150,
                                    handle: '.drag-handle',
                                    onEnd: function(evt) {
                                        let items = Array.from($el.children).map(el => el.dataset.id);
                                        $wire.updateGroupsOrder(items);
                                    }
                                })
                             ">
                            @foreach($groups as $group)
                                <div data-id="{{ $group->id }}" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-300 dark:hover:border-blue-600 transition">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            {{-- Drag Handle --}}
                                            <button class="drag-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                                </svg>
                                            </button>
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                            </svg>
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $group->name }}</h4>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Orden: {{ $group->order }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($canUpdate)
                                                <button wire:click="openEditGroupModal('{{ $group->id }}')"
                                                        class="p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                            @endif
                                            @if($canDelete)
                                                <button wire:click="openDeleteGroupModal('{{ $group->id }}')"
                                                        class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            @elseif($activeTab === 'fields')
                {{-- Fields Tab --}}
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Campos para {{ collect($entityTypes)->firstWhere('value', $selectedEntityType)['label'] ?? $selectedEntityType }}
                        </h3>
                    </div>

                    @if(count($groups) === 0)
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay grupos</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Primero crea grupos en la pestaña "Grupos de Campos"
                            </p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($groups as $group)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                    {{-- Accordion Header --}}
                                    <button wire:click="toggleAccordion('{{ $group->id }}')"
                                            class="w-full px-4 py-3 flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                            </svg>
                                            <div class="text-left">
                                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $group->name }}</h4>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ count($fieldsByGroup[$group->id] ?? []) }} campo(s)
                                                </p>
                                            </div>
                                        </div>
                                        <svg class="w-5 h-5 text-gray-400 transition-transform {{ in_array($group->id, $openAccordions) ? 'rotate-180' : '' }}"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    {{-- Accordion Content --}}
                                    @if(in_array($group->id, $openAccordions))
                                        <div class="p-4 bg-white dark:bg-gray-800">
                                            {{-- Add Field Button --}}
                                            @if($canCreate)
                                                <button wire:click="openCreateFieldModal('{{ $group->id }}')"
                                                        class="mb-4 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition inline-flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    Agregar Campo
                                                </button>
                                            @endif

                                            {{-- Fields List --}}
                                            @if(count($fieldsByGroup[$group->id] ?? []) === 0)
                                                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">
                                                    No hay campos en este grupo. Agrega uno nuevo.
                                                </p>
                                            @else
                                                <div id="fields-list-{{ $group->id }}"
                                                     class="grid grid-cols-1 md:grid-cols-2 gap-3"
                                                     x-data
                                                     x-init="
                                                        setTimeout(() => {
                                                            if (typeof Sortable !== 'undefined') {
                                                                new Sortable($el, {
                                                                    animation: 150,
                                                                    handle: '.field-drag-handle',
                                                                    ghostClass: 'sortable-ghost',
                                                                    chosenClass: 'sortable-chosen',
                                                                    dragClass: 'sortable-drag',
                                                                    forceFallback: true,
                                                                    onEnd: function(evt) {
                                                                        let items = Array.from($el.children).map(el => el.dataset.id);
                                                                        $wire.updateFieldsOrder('{{ $group->id }}', items);
                                                                    }
                                                                });
                                                                console.log('Sortable inicializado para grupo {{ $group->id }}');
                                                            } else {
                                                                console.error('Sortable.js no está disponible');
                                                            }
                                                        }, 100);
                                                     ">
                                                    @foreach($fieldsByGroup[$group->id] as $index => $field)
                                                        <div data-id="{{ $field->id }}"
                                                             class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 bg-white dark:bg-gray-800 hover:border-blue-300 dark:hover:border-blue-600 transition cursor-move">
                                                            <div class="flex items-start justify-between gap-2">
                                                                {{-- Order Badge + Drag Handle + Content --}}
                                                                <div class="flex items-start gap-2 flex-1 min-w-0">
                                                                    {{-- Order Number Badge --}}
                                                                    <span class="flex-shrink-0 inline-flex items-center justify-center w-6 h-6 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30 rounded">
                                                                        {{ $field->order ?? ($index + 1) }}
                                                                    </span>
                                                                    {{-- Drag Handle --}}
                                                                    <button class="field-drag-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition mt-0.5 flex-shrink-0">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                                                        </svg>
                                                                    </button>
                                                                    <div class="flex-1 min-w-0">
                                                                        <div class="flex items-center gap-2 flex-wrap">
                                                                            <h5 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                                                {{ $field->label }}
                                                                                @if($field->isRequired)
                                                                                    <span class="text-red-500">*</span>
                                                                                @endif
                                                                            </h5>
                                                                            @if($field->isSystem)
                                                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs font-medium rounded">
                                                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                                                    </svg>
                                                                                    Sistema
                                                                                </span>
                                                                            @endif
                                                                        </div>
                                                                        <div class="flex flex-col gap-0.5 mt-1">
                                                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                                Tipo: <span class="font-medium">{{ $field->type }}</span>
                                                                            </p>
                                                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                                                <span class="font-mono">{{ $field->name }}</span>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                {{-- Actions --}}
                                                                <div class="flex items-center gap-1 flex-shrink-0">
                                                                    @if($field->isSystem)
                                                                        {{-- Campos del sistema: permitir editar pero no activar/inactivar ni eliminar --}}
                                                                        @if($canUpdate)
                                                                            <button wire:click="openEditFieldModal('{{ $field->id }}')"
                                                                                    title="Editar campo del sistema"
                                                                                    class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                                </svg>
                                                                            </button>
                                                                        @endif
                                                                        {{-- Icono de candado indicando que no se puede eliminar ni inactivar --}}
                                                                        <div title="Campo del sistema: no se puede desactivar ni eliminar"
                                                                             class="p-1.5 text-purple-500 dark:text-purple-400 cursor-help">
                                                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                                            </svg>
                                                                        </div>
                                                                    @else
                                                                        {{-- Campos personalizados: mostrar todas las acciones --}}
                                                                        {{-- Toggle Active/Inactive --}}
                                                                        @if($canUpdate)
                                                                            <button wire:click="toggleFieldActive('{{ $field->id }}', {{ $field->isActive ? 'false' : 'true' }})"
                                                                                    title="{{ $field->isActive ? 'Inactivar campo' : 'Activar campo' }}"
                                                                                    class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition {{ $field->isActive ? 'text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300' }}">
                                                                                @if($field->isActive)
                                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                                                    </svg>
                                                                                @else
                                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                                                                    </svg>
                                                                                @endif
                                                                            </button>
                                                                        @endif
                                                                        @if($canUpdate)
                                                                            <button wire:click="openEditFieldModal('{{ $field->id }}')"
                                                                                    title="Editar campo"
                                                                                    class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                                </svg>
                                                                            </button>
                                                                        @endif
                                                                        @if($canDelete)
                                                                            <button wire:click="openDeleteFieldModal('{{ $field->id }}')"
                                                                                    title="Eliminar campo"
                                                                                    class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                                </svg>
                                                                            </button>
                                                                        @endif
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            {{-- TODO: Implementar Entity Types Tab
                 Requiere integración con sistema de módulos dinámico
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Gestión de Tipos de Entidad</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Próximamente: CRUD de tipos de entidad
                    </p>
                </div>
            --}}
            @endif
        </div>
    </div>

    {{-- Group Modal --}}
    @if($showGroupModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-init="document.body.style.overflow = 'hidden'" x-destroy="document.body.style.overflow = 'auto'">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75" wire:click="closeGroupModal"></div>

                <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 pt-5 pb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            @if($editingGroupId)
                                Editar Grupo: <span class="text-blue-600 dark:text-blue-400">{{ $groupName }}</span>
                            @else
                                Nuevo Grupo
                            @endif
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nombre del Grupo
                                </label>
                                <input type="text"
                                       wire:model="groupName"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="Ej: Información Personal">
                                @error('groupName')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Orden
                                </label>
                                <input type="number"
                                       wire:model="groupOrder"
                                       min="0"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('groupOrder')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                        <button wire:click="closeGroupModal"
                                type="button"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition">
                            Cancelar
                        </button>
                        <button wire:click="saveGroup"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                            {{ $editingGroupId ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Field Modal --}}
    @if($showFieldModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-init="document.body.style.overflow = 'hidden'" x-destroy="document.body.style.overflow = 'auto'">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75" wire:click="closeFieldModal"></div>

                <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="px-6 pt-5 pb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            {{ $editingFieldId ? 'Editar Campo' : 'Nuevo Campo' }}
                        </h3>

                        <div class="space-y-4">
                            {{-- Label --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Etiqueta del Campo <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       wire:model="fieldLabel"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="Ej: Número de DNI">
                                @error('fieldLabel')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Group --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Grupo
                                </label>
                                <select wire:model="fieldGroupId"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Sin grupo</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Selecciona el grupo al que pertenece este campo
                                </p>
                                @error('fieldGroupId')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Type (solo editable en creación) --}}
                            @if(!$editingFieldId)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Tipo de Campo <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="fieldType"
                                            class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="text">Texto corto</option>
                                        <option value="textarea">Texto largo</option>
                                        <option value="email">Email</option>
                                        <option value="tel">Teléfono</option>
                                        <option value="number">Número</option>
                                        <option value="select">Lista desplegable</option>
                                        <option value="multiselect">Lista desplegable (múltiple)</option>
                                        <option value="radio">Opción única (Radio)</option>
                                        <option value="checkbox">Casilla de verificación</option>
                                        <option value="date">Fecha</option>
                                        <option value="url">URL</option>
                                    </select>
                                    @error('fieldType')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            @else
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Tipo de Campo
                                    </label>
                                    <input type="text"
                                           value="{{ $fieldType }}"
                                           disabled
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">El tipo de campo no se puede cambiar después de crearlo</p>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-4">
                                {{-- Required --}}
                                <div class="flex items-center">
                                    <input type="checkbox"
                                           wire:model="fieldRequired"
                                           id="fieldRequired"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="fieldRequired" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Campo requerido
                                    </label>
                                </div>

                                {{-- Active (solo en edición) --}}
                                @if($editingFieldId)
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                               wire:model="fieldActive"
                                               id="fieldActive"
                                               class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        <label for="fieldActive" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Campo activo
                                        </label>
                                    </div>
                                @endif
                            </div>

                            {{-- Options (para select, radio, multiselect) --}}
                            <div x-data="{ fieldType: @entangle('fieldType') }" x-show="['select', 'radio', 'multiselect'].includes(fieldType)">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Opciones <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="fieldOptions"
                                          rows="5"
                                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                                          placeholder="Una opción por línea:&#10;Opción 1&#10;Opción 2&#10;Opción 3"></textarea>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Escribe una opción por línea. Las líneas vacías serán ignoradas.</p>
                                @error('fieldOptions')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Default Value --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Valor por Defecto (opcional)
                                </label>
                                <input type="text"
                                       wire:model="fieldDefaultValue"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="Valor predeterminado">
                                @error('fieldDefaultValue')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                        <button wire:click="closeFieldModal"
                                type="button"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition">
                            Cancelar
                        </button>
                        <button wire:click="saveField"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                            {{ $editingFieldId ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Group Confirmation Modal --}}
    @if($showDeleteGroupModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" aria-hidden="true" wire:click="closeDeleteGroupModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Confirmar Eliminación de Grupo
                        </h3>
                    </div>

                    <div class="px-6 py-4 space-y-4">
                        @if($fieldsCount > 0)
                            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
                                            Este grupo contiene {{ $fieldsCount }} campo(s)
                                        </h4>
                                        <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-400">
                                            Debes seleccionar un grupo destino para transferir los campos antes de eliminar este grupo.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Grupo Destino <span class="text-red-500">*</span>
                                </label>
                                <select wire:model="targetGroupId"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Selecciona un grupo...</option>
                                    @foreach($groups as $group)
                                        @if($group->id !== $deletingGroupId)
                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Los campos de este grupo se transferirán al grupo seleccionado.
                                </p>
                            </div>
                        @else
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Este grupo no contiene campos. ¿Estás seguro de que deseas eliminarlo?
                            </p>
                        @endif
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                        <button wire:click="closeDeleteGroupModal"
                                type="button"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition">
                            Cancelar
                        </button>
                        <button wire:click="confirmDeleteGroup"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                            Eliminar Grupo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Field Confirmation Modal --}}
    @if($showDeleteFieldModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity" wire:click="closeDeleteFieldModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                ⚠️ Confirmar Eliminación de Campo
                            </h3>
                            <button wire:click="closeDeleteFieldModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">
                            Vas a eliminar el campo "<span class="font-bold">{{ $deletingFieldLabel }}</span>"
                        </p>

                        @if($deletingFieldValuesCount > 0 || $deletingFieldOptionsCount > 0)
                            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg mb-4">
                                <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-300 mb-2">
                                    ⚠️ Impacto de la eliminación:
                                </h4>
                                <ul class="text-sm text-yellow-700 dark:text-yellow-400 space-y-1">
                                    @if($deletingFieldValuesCount > 0)
                                        <li class="flex items-start gap-2">
                                            <span class="font-bold">•</span>
                                            <span><strong>{{ $deletingFieldValuesCount }}</strong> registro(s) con datos se eliminarán permanentemente</span>
                                        </li>
                                    @endif
                                    @if($deletingFieldOptionsCount > 0)
                                        <li class="flex items-start gap-2">
                                            <span class="font-bold">•</span>
                                            <span>La tabla de opciones será eliminada (<strong>{{ $deletingFieldOptionsCount }}</strong> opcion(es))</span>
                                        </li>
                                    @endif
                                    <li class="flex items-start gap-2">
                                        <span class="font-bold">•</span>
                                        <span class="font-bold">Esta acción NO se puede deshacer</span>
                                    </li>
                                </ul>
                            </div>
                        @else
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Este campo no contiene datos. ¿Estás seguro de que deseas eliminarlo?
                            </p>
                        @endif

                        @if($deletingFieldValuesCount > 0)
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <p class="text-xs text-blue-700 dark:text-blue-400">
                                    💡 <strong>Sugerencia:</strong> Considera inactivar el campo en lugar de eliminarlo. Los datos históricos se preservarán y el campo no aparecerá en formularios nuevos.
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                        <button wire:click="closeDeleteFieldModal"
                                type="button"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition">
                            Cancelar
                        </button>
                        <button wire:click="confirmDeleteField"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                            {{ $deletingFieldValuesCount > 0 ? 'Eliminar de todas formas' : 'Eliminar Campo' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
