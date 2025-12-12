<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <button onclick="window.history.back()"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </button>
            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                {{ strtoupper(substr($deal->name ?? 'N', 0, 1)) }}
            </div>
            <div>
                <h1 class="text-lg font-semibold text-gray-900">{{ $deal->name }}</h1>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span>{{ $deal->created_at->diffForHumans() }}</span>
                    @if($deal->value)
                        <span class="font-semibold text-green-600">{{ $deal->formatted_value }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-1">
            @if($canEditDeal)
                <button wire:click="openEditModal"
                        class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition"
                        title="Editar negocio">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </button>
            @endif
            @if($canDeleteDeal)
                <button wire:click="openDeleteModal"
                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                        title="Eliminar negocio">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            @endif
        </div>
    </div>

    <div class="grid lg:grid-cols-5 gap-4">
        <!-- Columna izquierda: Info del negocio y contacto (2 cols) -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Fase de venta -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-medium text-gray-900">Estado del Negocio</h3>
                </div>
                <div class="p-4">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Fase de Venta</label>
                    @if($canEditDeal)
                        <select wire:key="phase-select-{{ $phaseSelectKey }}"
                                wire:model="salePhaseId"
                                wire:change="updatePhase"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                            @foreach($phases as $phase)
                                <option value="{{ $phase->id }}">{{ $phase->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="w-full px-3 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-600">
                            {{ $deal->salePhase->name ?? 'Sin fase' }}
                        </div>
                        <p class="text-xs text-amber-600 mt-1">Solo lectura - No tienes permiso para modificar este negocio</p>
                    @endif
                    <div class="mt-2 flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $deal->salePhase->color ?? '#6B7280' }}"></div>
                        <span class="text-xs text-gray-600">{{ $deal->salePhase->name ?? 'Sin fase' }}</span>
                        @if($deal->salePhase?->is_closed)
                            <span class="ml-auto text-xs px-1.5 py-0.5 rounded-full {{ $deal->salePhase->is_won ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $deal->salePhase->is_won ? 'Ganado' : 'Perdido' }}
                            </span>
                        @else
                            <span class="ml-auto text-xs px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700">Activo</span>
                        @endif
                    </div>
                    @if($deal->days_in_phase > 0)
                        <p class="text-xs text-gray-400 mt-2">{{ $deal->days_in_phase }} dias en esta fase</p>
                    @endif
                </div>
            </div>

            <!-- Datos del contacto -->
            @if($deal->lead)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-medium text-gray-900">Contacto</h3>
                        <a href="{{ route('leads.show', $deal->lead->id) }}"
                           class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                            Ver perfil
                        </a>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold">
                                {{ strtoupper(substr($deal->lead->name ?? 'C', 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $deal->lead->name ?? 'Sin nombre' }}</p>
                                @if($deal->lead->source_type)
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full
                                        {{ $deal->lead->source_type->value === 'manual' ? 'bg-gray-100 text-gray-700' : '' }}
                                        {{ $deal->lead->source_type->value === 'whatsapp_button' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $deal->lead->source_type->value === 'phone_button' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $deal->lead->source_type->value === 'contact_form' ? 'bg-purple-100 text-purple-700' : '' }}">
                                        {{ $deal->lead->source_type->label() }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Acciones rapidas -->
                        <div class="space-y-2">
                            @if($deal->lead->phone)
                                @php $cleanPhone = preg_replace('/[^0-9]/', '', $deal->lead->phone); @endphp
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        <span class="text-sm text-gray-700">{{ $deal->lead->phone }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <a href="https://wa.me/{{ $cleanPhone }}"
                                           target="_blank"
                                           wire:click="setCommentType('whatsapp')"
                                           class="p-1.5 text-green-600 hover:bg-green-100 rounded-lg transition"
                                           title="WhatsApp">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                            </svg>
                                        </a>
                                        <a href="tel:{{ $deal->lead->phone }}"
                                           wire:click="setCommentType('call')"
                                           class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition"
                                           title="Llamar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endif
                            @if($deal->lead->email)
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-sm text-gray-700 truncate">{{ $deal->lead->email }}</span>
                                    </div>
                                    <a href="mailto:{{ $deal->lead->email }}"
                                       wire:click="setCommentType('email')"
                                       class="p-1.5 text-purple-600 hover:bg-purple-100 rounded-lg transition"
                                       title="Enviar email">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Descripcion del negocio -->
            @if($deal->description)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="text-sm font-medium text-gray-900">Descripcion</h3>
                    </div>
                    <div class="p-4">
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $deal->description }}</p>
                    </div>
                </div>
            @endif

            <!-- Detalles -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-medium text-gray-900">Detalles</h3>
                </div>
                <div class="p-4">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Valor</dt>
                            <dd class="font-semibold {{ $deal->value ? 'text-green-600' : 'text-gray-400' }}">
                                {{ $deal->value ? $deal->formatted_value : 'Sin definir' }}
                            </dd>
                        </div>
                        @if($deal->estimated_close_date)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Cierre estimado</dt>
                                <dd class="text-gray-900">{{ $deal->estimated_close_date->format('d/m/Y') }}</dd>
                            </div>
                        @endif
                        @if($deal->close_date)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Fecha de cierre</dt>
                                <dd class="text-gray-900">{{ $deal->close_date->format('d/m/Y') }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Creado</dt>
                            <dd class="text-gray-900">{{ $deal->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Actualizado</dt>
                            <dd class="text-gray-900">{{ $deal->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Comentarios (3 cols) -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-900">Comentarios</h3>
                    <span class="text-xs text-gray-400">{{ $deal->comments->count() }} {{ $deal->comments->count() === 1 ? 'comentario' : 'comentarios' }}</span>
                </div>
                <div class="p-4 flex-1 flex flex-col">
                    <!-- Add/Edit Comment Form -->
                    <form wire:submit="addComment" class="mb-4">
                        <!-- Type selector -->
                        <div class="flex items-center gap-1 mb-2">
                            <span class="text-xs text-gray-500 mr-2">Tipo:</span>
                            <button type="button"
                                    wire:click="setCommentType('general')"
                                    class="px-2 py-1 text-xs rounded-lg transition {{ $commentType === 'general' ? 'bg-gray-200 text-gray-800' : 'text-gray-500 hover:bg-gray-100' }}">
                                Comentario
                            </button>
                            <button type="button"
                                    wire:click="setCommentType('call')"
                                    class="px-2 py-1 text-xs rounded-lg transition {{ $commentType === 'call' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-gray-100' }}">
                                Llamada
                            </button>
                            <button type="button"
                                    wire:click="setCommentType('whatsapp')"
                                    class="px-2 py-1 text-xs rounded-lg transition {{ $commentType === 'whatsapp' ? 'bg-green-100 text-green-700' : 'text-gray-500 hover:bg-gray-100' }}">
                                WhatsApp
                            </button>
                            <button type="button"
                                    wire:click="setCommentType('email')"
                                    class="px-2 py-1 text-xs rounded-lg transition {{ $commentType === 'email' ? 'bg-purple-100 text-purple-700' : 'text-gray-500 hover:bg-gray-100' }}">
                                Email
                            </button>
                        </div>
                        <div class="flex gap-2">
                            <textarea wire:model="commentContent"
                                      rows="2"
                                      class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm resize-none"
                                      placeholder="Escribe un comentario sobre la conversacion..."></textarea>
                            <div class="flex flex-col gap-1">
                                <button type="submit"
                                        class="px-3 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition">
                                    {{ $editingCommentId ? 'Actualizar' : 'Agregar' }}
                                </button>
                                @if($editingCommentId)
                                    <button type="button"
                                            wire:click="cancelEditComment"
                                            class="px-3 py-2 text-xs text-gray-600 hover:text-gray-900 border border-gray-300 rounded-lg transition">
                                        Cancelar
                                    </button>
                                @endif
                            </div>
                        </div>
                        @error('commentContent') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </form>

                    <!-- Comments List -->
                    <div class="flex-1 overflow-y-auto -mx-4 px-4" style="max-height: 500px;">
                        @if($deal->comments->count() > 0)
                            <div class="space-y-3">
                                @foreach($deal->comments as $comment)
                                    @php
                                        $typeColors = [
                                            'call' => 'border-l-blue-500 bg-blue-50',
                                            'whatsapp' => 'border-l-green-500 bg-green-50',
                                            'email' => 'border-l-purple-500 bg-purple-50',
                                            'general' => 'border-l-gray-300 bg-gray-50',
                                        ];
                                        $typeIcons = [
                                            'call' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>',
                                            'email' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
                                            'general' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>',
                                        ];
                                    @endphp
                                    <div class="rounded-lg p-3 border-l-4 group {{ $typeColors[$comment->type] ?? $typeColors['general'] }}">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    @if($comment->type === 'whatsapp')
                                                        <svg class="w-3.5 h-3.5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-3.5 h-3.5 {{ $comment->type === 'call' ? 'text-blue-600' : ($comment->type === 'email' ? 'text-purple-600' : 'text-gray-500') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            {!! $typeIcons[$comment->type] ?? $typeIcons['general'] !!}
                                                        </svg>
                                                    @endif
                                                    <span class="text-xs font-medium {{ $comment->type === 'call' ? 'text-blue-700' : ($comment->type === 'whatsapp' ? 'text-green-700' : ($comment->type === 'email' ? 'text-purple-700' : 'text-gray-600')) }}">
                                                        {{ $comment->type_label }}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $comment->content }}</p>
                                                <p class="text-xs text-gray-400 mt-1.5">{{ $comment->created_at->format('d/m/Y H:i') }} - {{ $comment->created_at->diffForHumans() }}</p>
                                            </div>
                                            <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition flex-shrink-0">
                                                <button wire:click="editComment('{{ $comment->id }}')"
                                                        class="p-1 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded transition"
                                                        title="Editar">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                <button wire:click="confirmDeleteComment('{{ $comment->id }}')"
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
                                <p class="text-gray-500 text-sm">Sin comentarios</p>
                                <p class="text-gray-400 text-xs mt-1">Registra tus conversaciones con el cliente</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deal Form Modal -->
    @livewire('deal-form-modal')

    <!-- Delete Deal Modal -->
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
                                <h3 class="text-lg font-semibold text-gray-900">Eliminar Negocio</h3>
                                <p class="text-sm text-gray-500 mt-1">Esta accion no se puede deshacer. El negocio y todos sus comentarios seran eliminados.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <button type="button" wire:click="$set('showDeleteModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        <button type="button" wire:click="deleteDeal"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                            Si, eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                                <label for="dealValue" class="block text-sm font-medium text-gray-700 mb-1">
                                    Valor del Negocio <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                                    <input type="number"
                                           id="dealValue"
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

    <!-- Delete Comment Modal -->
    @if($showDeleteCommentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showDeleteCommentModal', false)"></div>
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
                                <h3 class="text-lg font-semibold text-gray-900">Eliminar Comentario</h3>
                                <p class="text-sm text-gray-500 mt-1">Estas seguro de eliminar este comentario?</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <button type="button" wire:click="$set('showDeleteCommentModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        <button type="button" wire:click="deleteComment"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
