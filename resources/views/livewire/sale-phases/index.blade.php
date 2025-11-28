<div class="w-full">
    <!-- Actions Bar -->
    <div class="flex justify-end mb-6">
        <button
            wire:click="openCreateModal"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva Fase
        </button>
    </div>

    <!-- Phases List -->
    <div class="bg-white rounded-lg shadow">
        <ul
            id="phases-list"
            class="divide-y divide-gray-200"
            x-data
            x-init="
                new Sortable($el, {
                    animation: 150,
                    handle: '.drag-handle',
                    onEnd: function(evt) {
                        let items = Array.from($el.children).map(el => el.dataset.id);
                        $wire.updateOrder(items);
                    }
                })
            "
        >
            @forelse($phases as $phase)
                <li data-id="{{ $phase['id'] }}" class="flex items-center justify-between p-4 hover:bg-gray-50 transition">
                    <div class="flex items-center gap-4">
                        <!-- Drag Handle -->
                        <button class="drag-handle cursor-grab text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                            </svg>
                        </button>

                        <!-- Color Indicator -->
                        <div
                            class="w-4 h-4 rounded-full"
                            style="background-color: {{ $phase['color'] }}"
                        ></div>

                        <!-- Phase Info -->
                        <div>
                            <span class="font-medium text-gray-900">{{ $phase['name'] }}</span>
                            <div class="flex items-center gap-2 mt-1">
                                @if($phase['is_default'])
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">Por defecto</span>
                                @endif
                                @if($phase['is_closed'])
                                    <span class="text-xs {{ $phase['is_won'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} px-2 py-0.5 rounded">
                                        {{ $phase['is_won'] ? 'Ganado' : 'Perdido' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        @if(!$phase['is_default'] && !$phase['is_closed'])
                            <button
                                wire:click="setAsDefault('{{ $phase['id'] }}')"
                                class="text-gray-400 hover:text-blue-600 p-2"
                                title="Establecer como defecto"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            </button>
                        @endif
                        <button
                            wire:click="openEditModal('{{ $phase['id'] }}')"
                            class="text-gray-400 hover:text-blue-600 p-2"
                            title="Editar"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button
                            wire:click="openDeleteModal('{{ $phase['id'] }}')"
                            class="text-gray-400 hover:text-red-600 p-2"
                            title="Eliminar"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </li>
            @empty
                <li class="p-8 text-center text-gray-500">
                    No hay fases de venta configuradas
                </li>
            @endforelse
        </ul>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" x-data x-cloak>
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4" @click.away="$wire.closeModal()">
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="text-lg font-semibold">
                        {{ $editingId ? 'Editar Fase' : 'Nueva Fase' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit="save" class="p-4 space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                        <input
                            type="text"
                            wire:model="name"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ej: En negociación"
                        >
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                        <div class="flex items-center gap-3">
                            <input
                                type="color"
                                wire:model.live="color"
                                class="w-12 h-10 border border-gray-300 rounded cursor-pointer"
                            >
                            <input
                                type="text"
                                wire:model.live="color"
                                class="flex-1 border border-gray-300 rounded-lg px-3 py-2"
                                placeholder="#6B7280"
                            >
                        </div>
                    </div>

                    <!-- Is Closed -->
                    <div class="flex items-center gap-3">
                        <input
                            type="checkbox"
                            wire:model.live="isClosed"
                            id="isClosed"
                            class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                        >
                        <label for="isClosed" class="text-sm text-gray-700">Es fase de cierre</label>
                    </div>

                    <!-- Is Won (only if closed) -->
                    @if($isClosed)
                        <div class="flex items-center gap-3 ml-6">
                            <input
                                type="checkbox"
                                wire:model="isWon"
                                id="isWon"
                                class="w-4 h-4 text-green-600 rounded focus:ring-green-500"
                            >
                            <label for="isWon" class="text-sm text-gray-700">Es cierre ganado</label>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition flex items-center gap-2"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="save">Guardar</span>
                            <span wire:loading wire:target="save">Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" x-data x-cloak>
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-semibold text-red-600">Eliminar Fase</h3>
                </div>

                <div class="p-4 space-y-4">
                    <p class="text-gray-600">
                        ¿Estás seguro de eliminar esta fase? Esta acción no se puede deshacer.
                    </p>

                    @if(count($availablePhases) > 0)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Transferir leads a:
                            </label>
                            <select
                                wire:model="transferToPhaseId"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2"
                            >
                                <option value="">-- No transferir --</option>
                                @foreach($availablePhases as $phase)
                                    <option value="{{ $phase['id'] }}">{{ $phase['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button
                            wire:click="closeDeleteModal"
                            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                        >
                            Cancelar
                        </button>
                        <button
                            wire:click="delete"
                            class="px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-lg transition"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="delete">Eliminar</span>
                            <span wire:loading wire:target="delete">Eliminando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
