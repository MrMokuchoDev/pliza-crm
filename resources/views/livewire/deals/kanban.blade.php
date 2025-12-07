<div class="h-full flex flex-col" x-data="kanbanBoard()">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pipeline de Ventas</h1>
            <p class="text-gray-500 text-sm mt-1">
                {{ $totalDeals }} negocios activos
                @if($totalValue > 0)
                    <span class="mx-2">|</span>
                    <span class="text-green-600 font-semibold">${{ number_format($totalValue, 0, ',', '.') }}</span> en pipeline
                @endif
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <!-- Search -->
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Buscar negocios..."
                       class="pl-10 pr-4 py-2.5 w-full sm:w-64 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            </div>

            <!-- View Toggle -->
            <div class="flex items-center bg-gray-100 rounded-xl p-1">
                <a href="{{ route('deals.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 rounded-lg transition">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    Lista
                </a>
                <span class="px-4 py-2 text-sm font-medium bg-white text-gray-900 rounded-lg shadow-sm">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                    Kanban
                </span>
            </div>

            <!-- New Deal -->
            <button wire:click="openCreateModal"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white text-sm font-medium rounded-xl shadow-lg shadow-blue-500/25 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Negocio
            </button>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="flex-1 overflow-x-auto pb-4">
        <div class="flex gap-5 min-h-[500px]" style="min-width: max-content;">
            @foreach($openPhases as $phase)
                @php
                    $phaseDeals = $dealsByPhase[$phase->id] ?? collect();
                    $phaseValue = $phaseDeals->sum('value');
                @endphp
                <div class="w-80 flex-shrink-0">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100"
                             style="background: linear-gradient(135deg, {{ $phase->color }}15, {{ $phase->color }}05);">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full shadow-sm" style="background-color: {{ $phase->color }}"></div>
                                    <h3 class="font-semibold text-gray-800">{{ $phase->name }}</h3>
                                </div>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full"
                                      style="background-color: {{ $phase->color }}20; color: {{ $phase->color }}">
                                    {{ $phaseDeals->count() }}
                                </span>
                            </div>
                            @if($phaseValue > 0)
                                <div class="mt-1 text-xs text-gray-500">${{ number_format($phaseValue, 0, ',', '.') }}</div>
                            @endif
                        </div>

                        <div class="p-3 space-y-3 min-h-[400px] max-h-[calc(100vh-380px)] overflow-y-auto kanban-column"
                             data-phase-id="{{ $phase->id }}"
                             @dragover.prevent="onDragOver($event)"
                             @drop="onDrop($event, '{{ $phase->id }}')">

                            @forelse($phaseDeals as $deal)
                                <div class="bg-white border border-gray-100 rounded-xl p-4 cursor-grab active:cursor-grabbing hover:shadow-md hover:border-gray-200 transition-all duration-200 group"
                                     draggable="true"
                                     data-deal-id="{{ $deal->id }}"
                                     @dragstart="onDragStart($event, '{{ $deal->id }}')"
                                     @dragend="onDragEnd($event)">

                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-semibold text-gray-900 truncate">{{ $deal->name }}</h4>
                                            @if($deal->value)
                                                <p class="text-sm font-bold text-green-600 mt-0.5">{{ $deal->formatted_value }}</p>
                                            @endif
                                        </div>
                                        <button wire:click="openEditModal('{{ $deal->id }}')"
                                                class="opacity-0 group-hover:opacity-100 p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>
                                    </div>

                                    @if($deal->lead)
                                        <div class="bg-gray-50 rounded-lg p-2 mb-3">
                                            <div class="flex items-center gap-2 text-sm text-gray-700">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                <span class="truncate font-medium">{{ $deal->lead->name ?: 'Sin nombre' }}</span>
                                            </div>
                                            @if($deal->lead->phone)
                                                <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                    </svg>
                                                    <span>{{ $deal->lead->phone }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between pt-2 border-t border-gray-50">
                                        @if($deal->lead)
                                            @php
                                                $sourceColors = [
                                                    'manual' => 'bg-gray-100 text-gray-600',
                                                    'whatsapp_button' => 'bg-green-100 text-green-700',
                                                    'phone_button' => 'bg-blue-100 text-blue-700',
                                                    'contact_form' => 'bg-purple-100 text-purple-700',
                                                ];
                                                $sourceLabels = [
                                                    'manual' => 'Manual',
                                                    'whatsapp_button' => 'WhatsApp',
                                                    'phone_button' => 'Llamada',
                                                    'contact_form' => 'Formulario',
                                                ];
                                            @endphp
                                            <span class="text-xs font-medium px-2 py-1 rounded-md {{ $sourceColors[$deal->lead->source_type?->value] ?? 'bg-gray-100 text-gray-600' }}">
                                                {{ $sourceLabels[$deal->lead->source_type?->value] ?? 'Otro' }}
                                            </span>
                                        @else
                                            <span></span>
                                        @endif
                                        <span class="text-xs text-gray-400">{{ $deal->updated_at->diffForHumans(short: true) }}</span>
                                    </div>

                                    @if($deal->lead && $deal->lead->phone)
                                        <div class="flex items-center gap-1 mt-3 pt-3 border-t border-gray-50 opacity-0 group-hover:opacity-100 transition">
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $deal->lead->phone) }}"
                                               target="_blank"
                                               class="flex-1 flex items-center justify-center gap-1 py-1.5 text-xs font-medium text-green-600 hover:bg-green-50 rounded-lg transition">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                </svg>
                                                WhatsApp
                                            </a>
                                            <a href="tel:{{ $deal->lead->phone }}"
                                               class="flex-1 flex items-center justify-center gap-1 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                                Llamar
                                            </a>
                                            <a href="{{ route('deals.show', $deal->id) }}"
                                               class="flex-1 flex items-center justify-center gap-1 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 rounded-lg transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                Ver
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-12 text-center">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm text-gray-400">Sin negocios</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Drop Zones -->
    @if($closedPhases->isNotEmpty())
        <div x-cloak x-show="dragging"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4"
             class="fixed bottom-6 z-50 flex justify-center gap-6"
             style="left: 256px; right: 0;">
            @foreach($closedPhases as $phase)
                <div class="rounded-xl px-20 py-4 text-center transition-all duration-200 drop-zone cursor-pointer"
                     style="background: {{ $phase->color }}15; border: 3px dashed {{ $phase->color }};"
                     data-phase-id="{{ $phase->id }}"
                     @dragover.prevent="onDragOver($event)"
                     @drop="onDrop($event, '{{ $phase->id }}')"
                     @dragenter="$el.style.background = '{{ $phase->color }}30'; $el.style.borderStyle = 'solid'; $el.style.transform = 'scale(1.05)'"
                     @dragleave="$el.style.background = '{{ $phase->color }}15'; $el.style.borderStyle = 'dashed'; $el.style.transform = 'scale(1)'">
                    <div class="flex items-center justify-center gap-3">
                        @if($phase->is_won)
                            <svg class="w-6 h-6" style="color: {{ $phase->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @else
                            <svg class="w-6 h-6" style="color: {{ $phase->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                        <span class="font-bold" style="color: {{ $phase->color }}">{{ $phase->name }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @livewire('deal-form-modal')

    <!-- Value Modal for Won Phase -->
    @if($showValueModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelWonPhase"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <form wire:submit="confirmWonWithValue">
                        <div class="bg-white px-6 pt-6 pb-4">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Cerrar como Ganado</h3>
                                    <p class="text-sm text-gray-500 mt-1">Ingresa el valor final del negocio para marcarlo como ganado.</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label for="dealValueKanban" class="block text-sm font-medium text-gray-700 mb-1">
                                    Valor del Negocio <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                                    <input type="number"
                                           id="dealValueKanban"
                                           wire:model="dealValue"
                                           step="0.01"
                                           min="0"
                                           class="w-full pl-8 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-lg"
                                           placeholder="0.00"
                                           autofocus>
                                </div>
                                @error('dealValue')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                            <button type="button" wire:click="cancelWonPhase"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                                Confirmar Ganado
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @script
    <script>
        Alpine.data('kanbanBoard', () => ({
            dragging: false,
            draggedDealId: null,
            onDragStart(event, dealId) {
                this.dragging = true;
                this.draggedDealId = dealId;
                event.target.classList.add('opacity-50', 'rotate-2');
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', dealId);
            },
            onDragEnd(event) {
                this.dragging = false;
                this.draggedDealId = null;
                event.target.classList.remove('opacity-50', 'rotate-2');
            },
            onDragOver(event) {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
            },
            async onDrop(event, phaseId) {
                event.preventDefault();
                const dealId = event.dataTransfer.getData('text/plain');
                if (dealId && phaseId) {
                    // Wait for Livewire to process the move before resetting drag state
                    await $wire.moveToPhase(dealId, phaseId);
                }
                this.dragging = false;
                this.draggedDealId = null;
            }
        }));
    </script>
    @endscript

    <style>
        .kanban-column { scrollbar-width: thin; scrollbar-color: #e5e7eb transparent; }
        .kanban-column::-webkit-scrollbar { width: 6px; }
        .kanban-column::-webkit-scrollbar-track { background: transparent; }
        .kanban-column::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 3px; }
        .kanban-column::-webkit-scrollbar-thumb:hover { background-color: #d1d5db; }
    </style>
</div>
