<div>
    <!-- Header compacto -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('leads.index') }}"
               class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold">
                {{ strtoupper(substr($lead->name ?? 'C', 0, 1)) }}
            </div>
            <div>
                <h1 class="text-lg font-semibold text-gray-900">{{ $lead->name ?? 'Sin nombre' }}</h1>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span>{{ $lead->created_at->diffForHumans() }}</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 font-medium rounded-full
                        {{ $lead->source_type?->value === 'manual' ? 'bg-gray-100 text-gray-700' : '' }}
                        {{ $lead->source_type?->value === 'whatsapp_button' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $lead->source_type?->value === 'phone_button' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $lead->source_type?->value === 'contact_form' ? 'bg-purple-100 text-purple-700' : '' }}">
                        {{ $lead->source_type?->label() ?? 'Manual' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-1">
            <button wire:click="openEditModal"
                    class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition"
                    title="Editar contacto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </button>
            <button wire:click="openDeleteModal"
                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                    title="Eliminar contacto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="grid lg:grid-cols-5 gap-4">
        <!-- Columna izquierda: Info + Negocios (2 cols) -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Card de contacto y acciones -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4">
                    <!-- Datos de contacto compactos -->
                    <div class="space-y-3">
                        @if($lead->email)
                            <div class="flex items-center justify-between">
                                <a href="mailto:{{ $lead->email }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-2 truncate">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="truncate">{{ $lead->email }}</span>
                                </a>
                                <a href="mailto:{{ $lead->email }}"
                                   class="ml-2 p-1.5 text-purple-600 hover:bg-purple-50 rounded-lg transition flex-shrink-0"
                                   title="Enviar email">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </a>
                            </div>
                        @endif
                        @if($lead->phone)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    {{ $lead->phone }}
                                </span>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}"
                                       target="_blank"
                                       class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition"
                                       title="WhatsApp">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                        </svg>
                                    </a>
                                    <a href="tel:{{ $lead->phone }}"
                                       class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                       title="Llamar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endif
                        @if(!$lead->email && !$lead->phone)
                            <p class="text-sm text-gray-400 text-center py-2">Sin datos de contacto</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Negocios del contacto -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-900">Negocios</h3>
                    @php
                        $hasOpenDeal = $lead->deals->contains(fn($d) => !$d->salePhase?->is_closed);
                    @endphp
                    @if(!$hasOpenDeal)
                        <button wire:click="openCreateDealModal"
                                class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nuevo
                        </button>
                    @endif
                </div>
                <div class="p-4">
                    @if($lead->deals->count() > 0)
                        <div class="space-y-3">
                            @foreach($lead->deals->sortByDesc('created_at') as $deal)
                                <div class="bg-gray-50 rounded-lg p-3 group hover:bg-gray-100 transition">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('deals.show', $deal->id) }}"
                                                   wire:navigate
                                                   class="font-medium text-gray-900 text-sm truncate hover:text-blue-600 transition">
                                                    {{ $deal->name }}
                                                </a>
                                                @if($deal->salePhase)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                                          style="background-color: {{ $deal->salePhase->color }}20; color: {{ $deal->salePhase->color }}">
                                                        {{ $deal->salePhase->name }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($deal->value)
                                                <p class="text-sm font-semibold text-green-600 mt-1">{{ $deal->formatted_value }}</p>
                                            @endif
                                            @if($deal->description)
                                                <p class="text-xs text-gray-500 mt-1 truncate">{{ $deal->description }}</p>
                                            @endif
                                        </div>
                                        <button wire:click="openEditDealModal('{{ $deal->id }}')"
                                                class="p-1 text-gray-400 hover:text-blue-600 opacity-0 group-hover:opacity-100 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-200">
                                        <span class="text-xs text-gray-400">{{ $deal->created_at->format('d/m/Y') }}</span>
                                        @if($deal->salePhase?->is_closed)
                                            <span class="text-xs px-1.5 py-0.5 rounded-full {{ $deal->salePhase->is_won ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $deal->salePhase->is_won ? 'Ganado' : 'Perdido' }}
                                            </span>
                                        @else
                                            <span class="text-xs px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700">Activo</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500">Sin negocios</p>
                            <button wire:click="openCreateDealModal"
                                    class="mt-2 text-xs text-blue-600 hover:text-blue-800 font-medium">
                                Crear primer negocio
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Mensaje del lead -->
            @if($lead->message)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="text-sm font-medium text-gray-900">Mensaje</h3>
                    </div>
                    <div class="p-4">
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $lead->message }}</p>
                    </div>
                </div>
            @endif

            <!-- Info adicional -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-medium text-gray-900">Detalles</h3>
                </div>
                <div class="p-4">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Creado</dt>
                            <dd class="text-gray-900">{{ $lead->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Actualizado</dt>
                            <dd class="text-gray-900">{{ $lead->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        @if($lead->sourceSite)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Sitio</dt>
                                <dd class="text-gray-900">{{ $lead->sourceSite->name }}</dd>
                            </div>
                        @endif
                        @if($lead->source_url)
                            <div class="pt-2 border-t border-gray-100">
                                <dt class="text-gray-500 mb-1">URL de origen</dt>
                                <dd>
                                    <a href="{{ $lead->source_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-xs break-all">
                                        {{ $lead->source_url }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Metadata -->
            @if($lead->metadata && count($lead->metadata) > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="text-sm font-medium text-gray-900">Datos Adicionales</h3>
                    </div>
                    <div class="p-4">
                        <dl class="space-y-2 text-sm">
                            @foreach($lead->metadata as $key => $value)
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                    <dd class="text-gray-900">{{ is_array($value) ? json_encode($value) : $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </div>
            @endif
        </div>

        <!-- Columna derecha: Notas (3 cols) -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-900">Notas</h3>
                    <span class="text-xs text-gray-400">{{ $lead->notes->count() }} {{ $lead->notes->count() === 1 ? 'nota' : 'notas' }}</span>
                </div>
                <div class="p-4 flex-1 flex flex-col">
                    <!-- Add/Edit Note Form -->
                    <form wire:submit="addNote" class="mb-4">
                        <div class="flex gap-2">
                            <textarea wire:model="noteContent"
                                      rows="2"
                                      class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm resize-none"
                                      placeholder="Escribe una nota..."></textarea>
                            <div class="flex flex-col gap-1">
                                <button type="submit"
                                        class="px-3 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition">
                                    {{ $editingNoteId ? 'Actualizar' : 'Agregar' }}
                                </button>
                                @if($editingNoteId)
                                    <button type="button"
                                            wire:click="cancelEditNote"
                                            class="px-3 py-2 text-xs text-gray-600 hover:text-gray-900 border border-gray-300 rounded-lg transition">
                                        Cancelar
                                    </button>
                                @endif
                            </div>
                        </div>
                        @error('noteContent') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </form>

                    <!-- Notes List -->
                    <div class="flex-1 overflow-y-auto -mx-4 px-4" style="max-height: 400px;">
                        @if($lead->notes->count() > 0)
                            <div class="space-y-3">
                                @foreach($lead->notes->sortByDesc('created_at') as $note)
                                    <div class="bg-gray-50 rounded-lg p-3 group">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $note->content }}</p>
                                                <p class="text-xs text-gray-400 mt-1.5">{{ $note->created_at->format('d/m/Y H:i') }} - {{ $note->created_at->diffForHumans() }}</p>
                                            </div>
                                            <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition flex-shrink-0">
                                                <button wire:click="editNote('{{ $note->id }}')"
                                                        class="p-1 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded transition"
                                                        title="Editar">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                <button wire:click="confirmDeleteNote('{{ $note->id }}')"
                                                        class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition"
                                                        title="Eliminar">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                    </svg>
                                </div>
                                <p class="text-gray-500 text-sm">Sin notas</p>
                                <p class="text-gray-400 text-xs mt-1">Agrega la primera nota arriba</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Componentes modales -->
    @livewire('lead-form-modal')
    @livewire('deal-form-modal')

    <!-- Delete Lead Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showDeleteModal', false)"></div>
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
                                <h3 class="text-lg font-semibold text-gray-900">Eliminar Contacto</h3>
                                <p class="text-sm text-gray-500 mt-1">Esta accion no se puede deshacer. El contacto, sus negocios y notas seran eliminados.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <button type="button" wire:click="$set('showDeleteModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        <button type="button" wire:click="deleteLead"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                            Si, eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Note Modal -->
    @if($showDeleteNoteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showDeleteNoteModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Eliminar Nota</h3>
                                <p class="text-sm text-gray-500 mt-1">Estas seguro de eliminar esta nota?</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <button type="button" wire:click="$set('showDeleteNoteModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        <button type="button" wire:click="deleteNote"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
