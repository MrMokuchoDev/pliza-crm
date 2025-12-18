<div>
    @if($show)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-80 transition-opacity" wire:click="close"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit="save">
                        <div class="bg-white dark:bg-gray-800 px-6 pt-6 pb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                {{ $dealId ? 'Editar Negocio' : 'Nuevo Negocio' }}
                            </h3>

                            {{-- Step 1: Select or Create Lead (only when creating new deal without leadId) --}}
                            @if($showLeadSearch && !$dealId)
                                <div class="mb-6">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Seleccionar Contacto</h4>

                                    {{-- Search input --}}
                                    <div class="relative">
                                        <svg wire:loading.remove wire:target="leadSearch" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        <svg wire:loading wire:target="leadSearch" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <input type="text"
                                               wire:model.live.debounce.500ms="leadSearch"
                                               class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                                               placeholder="Buscar por nombre, email o telefono...">
                                    </div>

                                    {{-- Error message --}}
                                    @if($leadHasOpenDealError)
                                        <div class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                            <p class="text-sm text-red-600 dark:text-red-400 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ $leadHasOpenDealError }}
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Search results --}}
                                    @if(strlen($leadSearch) >= 2)
                                        <div class="mt-3 border border-gray-200 dark:border-gray-700 rounded-lg divide-y divide-gray-100 dark:divide-gray-700 max-h-64 overflow-y-auto">
                                            @forelse($searchResults as $result)
                                                <button type="button"
                                                        wire:click="selectLead('{{ $result->id }}')"
                                                        class="w-full px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center justify-between gap-3 {{ $result->active_deals_count > 0 ? 'opacity-60' : '' }}">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                                                            {{ strtoupper(substr($result->name ?? 'C', 0, 1)) }}
                                                        </div>
                                                        <div class="min-w-0">
                                                            <p class="font-medium text-gray-900 dark:text-white truncate">{{ $result->name ?? 'Sin nombre' }}</p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                                @if($result->email) {{ $result->email }} @endif
                                                                @if($result->email && $result->phone) - @endif
                                                                @if($result->phone) {{ $result->phone }} @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                    @if($result->active_deals_count > 0)
                                                        <span class="text-xs px-2 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 rounded-full flex-shrink-0">
                                                            Negocio activo
                                                        </span>
                                                    @else
                                                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                        </svg>
                                                    @endif
                                                </button>
                                            @empty
                                                <div class="px-4 py-6 text-center">
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">No se encontraron contactos</p>
                                                    <button type="button"
                                                            wire:click="startNewLead"
                                                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                        Crear nuevo contacto
                                                    </button>
                                                </div>
                                            @endforelse
                                        </div>
                                    @else
                                        <div class="mt-3 p-6 text-center border border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                                            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Escribe al menos 2 caracteres para buscar contactos</p>
                                            <button type="button"
                                                    wire:click="startNewLead"
                                                    class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                O crear nuevo contacto
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{-- Main form (when lead is selected or creating new or editing) --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Columna izquierda: Datos del Negocio -->
                                    <div class="space-y-4">
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide border-b border-gray-200 dark:border-gray-700 pb-2">Datos del Negocio</h4>

                                        <!-- Deal Name -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre del Negocio *</label>
                                            <input type="text"
                                                   wire:model="name"
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                                                   placeholder="Ej: Proyecto Web Empresa X">
                                            @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Value -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-2 text-gray-500 dark:text-gray-400">$</span>
                                                <input type="number"
                                                       wire:model="value"
                                                       step="0.01"
                                                       min="0"
                                                       class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                                                       placeholder="0.00">
                                            </div>
                                            @error('value') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Sale Phase -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fase de Venta *</label>
                                            <select wire:model="salePhaseId"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                @foreach($phases as $phase)
                                                    <option value="{{ $phase->id }}">
                                                        {{ $phase->name }}
                                                        @if($phase->is_closed)
                                                            ({{ $phase->is_won ? 'Ganado' : 'Perdido' }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('salePhaseId') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Estimated Close Date -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Estimada de Cierre</label>
                                            <input type="date"
                                                   wire:model="estimatedCloseDate"
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                            @error('estimatedCloseDate') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Description -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripcion</label>
                                            <textarea wire:model="description"
                                                      rows="3"
                                                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                                                      placeholder="Detalles del negocio..."></textarea>
                                            @error('description') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Assigned To -->
                                        @if($canAssign)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Asignado a</label>
                                            <select wire:model="assigned_to"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                <option value="">Sin asignar</option>
                                                @foreach($assignableUsers as $user)
                                                    <option value="{{ $user->uuid }}">{{ $user->name }} ({{ $user->email }})</option>
                                                @endforeach
                                            </select>
                                            @error('assigned_to') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                        @endif
                                    </div>

                                    <!-- Columna derecha: Datos del Contacto -->
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                                {{ $createNewLead ? 'Nuevo Contacto' : 'Datos del Contacto' }}
                                            </h4>
                                            @if(!$dealId && ($leadId || $createNewLead) && $canEditLead)
                                                <button type="button"
                                                        wire:click="{{ $createNewLead ? 'backToSearch' : 'clearSelectedLead' }}"
                                                        class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                                                    Cambiar
                                                </button>
                                            @endif
                                        </div>

                                        @if($createNewLead)
                                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mb-2">
                                                <p class="text-sm text-blue-700 dark:text-blue-300 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Se creara un nuevo contacto junto con el negocio
                                                </p>
                                            </div>
                                        @elseif(!$canEditLead && $leadId)
                                            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3 mb-2">
                                                <p class="text-sm text-amber-700 dark:text-amber-300 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Solo lectura - No tienes permiso para editar este contacto
                                                </p>
                                            </div>
                                        @endif

                                        <!-- Lead Name -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
                                            <input type="text"
                                                   wire:model="leadName"
                                                   @if(!$canEditLead && !$createNewLead) readonly @endif
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 {{ !$canEditLead && !$createNewLead ? 'bg-gray-100 dark:bg-gray-600 cursor-not-allowed' : '' }}"
                                                   placeholder="Nombre del contacto">
                                            @error('leadName') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Lead Email -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                            <input type="email"
                                                   wire:model="leadEmail"
                                                   @if(!$canEditLead && !$createNewLead) readonly @endif
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 {{ !$canEditLead && !$createNewLead ? 'bg-gray-100 dark:bg-gray-600 cursor-not-allowed' : '' }}"
                                                   placeholder="email@ejemplo.com">
                                            @error('leadEmail') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Lead Phone -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telefono</label>
                                            <input type="text"
                                                   wire:model="leadPhone"
                                                   @if(!$canEditLead && !$createNewLead) readonly @endif
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 {{ !$canEditLead && !$createNewLead ? 'bg-gray-100 dark:bg-gray-600 cursor-not-allowed' : '' }}"
                                                   placeholder="+57 300 123 4567">
                                            @error('leadPhone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Quick Actions -->
                                        @if(($leadPhone || $leadEmail) && !$createNewLead)
                                            <div class="pt-2">
                                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Acciones Rapidas</label>
                                                <div class="flex gap-2">
                                                    @if($leadPhone)
                                                        @php
                                                            $cleanPhone = preg_replace('/[^0-9]/', '', $leadPhone);
                                                        @endphp
                                                        <a href="https://wa.me/{{ $cleanPhone }}"
                                                           target="_blank"
                                                           class="inline-flex items-center px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg text-sm hover:bg-green-200 dark:hover:bg-green-900/50 transition">
                                                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                            </svg>
                                                            WhatsApp
                                                        </a>
                                                        <a href="tel:{{ $leadPhone }}"
                                                           class="inline-flex items-center px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-lg text-sm hover:bg-blue-200 dark:hover:bg-blue-900/50 transition">
                                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                            </svg>
                                                            Llamar
                                                        </a>
                                                    @endif
                                                    @if($leadEmail)
                                                        <a href="mailto:{{ $leadEmail }}"
                                                           class="inline-flex items-center px-3 py-1.5 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 rounded-lg text-sm hover:bg-purple-200 dark:hover:bg-purple-900/50 transition">
                                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                            </svg>
                                                            Email
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 flex justify-end gap-3">
                            <button type="button"
                                    wire:click="close"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition">
                                Cancelar
                            </button>
                            @if(!$showLeadSearch || $dealId)
                                <button type="submit"
                                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                    {{ $dealId ? 'Actualizar' : 'Crear Negocio' }}
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
