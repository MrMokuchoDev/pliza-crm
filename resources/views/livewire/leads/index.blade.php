<div class="w-full max-w-full overflow-x-hidden">
    <!-- Header + Filters + Stats -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-4">
        <div class="p-4">
            <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                <!-- Search -->
                <div class="relative lg:w-64">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Buscar contacto..."
                           class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </div>

                <!-- Filter by Source -->
                <select wire:model.live="filterSource"
                        class="lg:w-48 px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    <option value="">Todos los origenes</option>
                    @foreach($sourceTypes as $source)
                        <option value="{{ $source->value }}">{{ $source->label() }}</option>
                    @endforeach
                </select>

                @if($search || $filterSource)
                    <button wire:click="clearFilters" class="text-sm text-gray-500 hover:text-gray-700">
                        Limpiar
                    </button>
                @endif

                <!-- Nuevo Contacto Button -->
                @if($canCreate)
                <button wire:click="openCreateModal"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 transition shadow-sm whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="hidden sm:inline">Nuevo Contacto</span>
                    <span class="sm:hidden">Nuevo</span>
                </button>
                @endif

                <!-- Stats compactos -->
                <div class="flex items-center gap-3 lg:gap-4 lg:ml-auto overflow-x-auto pb-1 lg:pb-0">
                    <div class="flex items-center gap-1.5 text-sm whitespace-nowrap">
                        <span class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0"></span>
                        <span class="text-gray-500 hidden sm:inline">Total:</span>
                        <span class="font-semibold text-gray-900">{{ $totalLeads }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-sm whitespace-nowrap">
                        <span class="w-2 h-2 bg-green-500 rounded-full flex-shrink-0"></span>
                        <span class="text-gray-500 hidden sm:inline">Con negocios:</span>
                        <span class="font-semibold text-gray-900">{{ $leadsWithDeals }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-sm whitespace-nowrap">
                        <span class="w-2 h-2 bg-orange-500 rounded-full flex-shrink-0"></span>
                        <span class="text-gray-500 hidden sm:inline">Sin negocios:</span>
                        <span class="font-semibold text-gray-900">{{ $leadsWithoutDeals }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leads Table (Desktop) / Cards (Mobile) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($leads->count() > 0)
            <!-- Vista Desktop: Tabla -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contacto</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Datos</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Origen</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Asignado</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Negocio</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($leads as $lead)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                            {{ strtoupper(substr($lead->name ?? 'C', 0, 1)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('leads.show', $lead->id) }}" class="font-medium text-gray-900 hover:text-blue-600">
                                                {{ $lead->name ?? 'Sin nombre' }}
                                            </a>
                                            @if($lead->message)
                                                <p class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($lead->message, 40) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        @if($lead->email)
                                            <a href="mailto:{{ $lead->email }}" class="text-xs text-gray-600 hover:text-blue-600 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                                {{ $lead->email }}
                                            </a>
                                        @endif
                                        @if($lead->phone)
                                            <div class="flex items-center gap-2">
                                                <a href="tel:{{ $lead->phone }}" class="text-xs text-gray-600 hover:text-blue-600 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                    </svg>
                                                    {{ $lead->phone }}
                                                </a>
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}"
                                                   target="_blank"
                                                   class="p-1 text-green-600 hover:bg-green-50 rounded transition"
                                                   title="WhatsApp">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        @endif
                                        @if(!$lead->email && !$lead->phone)
                                            <span class="text-xs text-gray-400">Sin datos de contacto</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ $lead->source_type ? $lead->source_type->label() : 'Manual' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($lead->assignedTo)
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-xs font-medium">
                                                {{ strtoupper(substr($lead->assignedTo->name, 0, 1)) }}
                                            </div>
                                            <span class="text-sm text-gray-700">{{ $lead->assignedTo->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">Sin asignar</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        @if($lead->active_deals_count > 0)
                                            @php $activeDeal = $lead->activeDeals->first(); @endphp
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full animate-pulse" style="background-color: {{ $activeDeal->salePhase->color ?? '#10B981' }}"></span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background-color: {{ $activeDeal->salePhase->color ?? '#10B981' }}20; color: {{ $activeDeal->salePhase->color ?? '#10B981' }}">
                                                    {{ $activeDeal->salePhase->name ?? 'En proceso' }}
                                                </span>
                                            </div>
                                            <span class="text-xs text-gray-500 truncate max-w-[140px]" title="{{ $activeDeal->name }}">
                                                {{ Str::limit($activeDeal->name, 20) }}
                                            </span>
                                        @elseif($lead->deals_count > 0)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                {{ $lead->deals_count }} cerrado{{ $lead->deals_count !== 1 ? 's' : '' }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">Sin negocios</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600">
                                        {{ $lead->created_at->format('d/m/Y') }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $lead->created_at->format('H:i') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('leads.show', $lead->id) }}"
                                           class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                           title="Ver detalle">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        @if($lead->active_deals_count === 0)
                                            <button wire:click="openCreateDealModal('{{ $lead->id }}')"
                                                    class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition"
                                                    title="Crear negocio">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                        @endif
                                        @if($canEdit)
                                        <button wire:click="openEditModal('{{ $lead->id }}')"
                                                class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition"
                                                title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        @endif
                                        @if($canDelete)
                                        <button wire:click="openDeleteModal('{{ $lead->id }}')"
                                                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                                title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Vista Mobile: Tarjetas -->
            <div class="lg:hidden divide-y divide-gray-100">
                @foreach($leads as $lead)
                    <div class="p-4 overflow-hidden">
                        <!-- Header: Avatar + Nombre + Acciones -->
                        <div class="flex items-start justify-between mb-3">
                            <a href="{{ route('leads.show', $lead->id) }}" class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-base flex-shrink-0">
                                    {{ strtoupper(substr($lead->name ?? 'C', 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-semibold text-gray-900 truncate">{{ $lead->name ?? 'Sin nombre' }}</h3>
                                    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            {{ $lead->source_type ? $lead->source_type->label() : 'Manual' }}
                                        </span>
                                        @if($lead->assignedTo)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                {{ $lead->assignedTo->name }}
                                            </span>
                                        @endif
                                        <span class="text-xs text-gray-400">{{ $lead->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            </a>
                            <!-- Menú de acciones -->
                            <div class="flex items-center gap-1 flex-shrink-0">
                                @if($canEdit)
                                <button wire:click="openEditModal('{{ $lead->id }}')"
                                        class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @endif
                                @if($canDelete)
                                <button wire:click="openDeleteModal('{{ $lead->id }}')"
                                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </div>

                        <!-- Mensaje si existe -->
                        @if($lead->message)
                            <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ $lead->message }}</p>
                        @endif

                        <!-- Estado del negocio -->
                        @if($lead->active_deals_count > 0)
                            @php $activeDeal = $lead->activeDeals->first(); @endphp
                            <div class="flex items-center gap-2 mb-3 p-2 rounded-lg" style="background-color: {{ $activeDeal->salePhase->color ?? '#10B981' }}10">
                                <span class="w-2 h-2 rounded-full animate-pulse" style="background-color: {{ $activeDeal->salePhase->color ?? '#10B981' }}"></span>
                                <span class="text-sm font-medium" style="color: {{ $activeDeal->salePhase->color ?? '#10B981' }}">
                                    {{ $activeDeal->salePhase->name ?? 'En proceso' }}
                                </span>
                                <span class="text-sm text-gray-500">- {{ Str::limit($activeDeal->name, 25) }}</span>
                            </div>
                        @elseif($lead->deals_count > 0)
                            <div class="flex items-center gap-2 mb-3 p-2 bg-gray-50 rounded-lg">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-sm text-gray-600">{{ $lead->deals_count }} negocio{{ $lead->deals_count !== 1 ? 's' : '' }} cerrado{{ $lead->deals_count !== 1 ? 's' : '' }}</span>
                            </div>
                        @endif

                        <!-- Botones de contacto -->
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($lead->phone)
                                <a href="tel:{{ $lead->phone }}"
                                   class="inline-flex items-center gap-2 px-3 py-2 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium hover:bg-blue-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    Llamar
                                </a>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-2 px-3 py-2 bg-green-50 text-green-700 rounded-lg text-sm font-medium hover:bg-green-100 transition">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                    WhatsApp
                                </a>
                            @endif
                            @if($lead->email)
                                <a href="mailto:{{ $lead->email }}"
                                   class="inline-flex items-center gap-2 px-3 py-2 bg-gray-50 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Email
                                </a>
                            @endif
                            @if($lead->active_deals_count === 0)
                                <button wire:click="openCreateDealModal('{{ $lead->id }}')"
                                        class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Negocio
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="px-4 lg:px-6 py-4 border-t border-gray-100">
                {{ $leads->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No hay contactos todavia</h3>
                <p class="text-gray-500 mb-4">Comienza agregando tu primer contacto o configura los widgets de captura</p>
                <div class="flex items-center justify-center gap-3">
                    @if($canCreate)
                    <button wire:click="openCreateModal"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Crear Contacto
                    </button>
                    @endif
                    <a href="{{ route('sites.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                        Configurar Widgets
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Lead Form Modal -->
    @livewire('lead-form-modal')

    <!-- Deal Form Modal -->
    @livewire('deal-form-modal')

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeDeleteModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="text-center mb-4">
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Eliminar a {{ $deletingName }}</h3>
                            <p class="text-sm text-gray-500 mt-1">Esta acción no se puede deshacer.</p>
                        </div>

                        @if($deletingDealsCount > 0 || $deletingNotesCount > 0)
                            <div class="p-3 bg-red-50 rounded-lg border border-red-200">
                                <p class="text-sm font-medium text-red-800">También se eliminarán:</p>
                                <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                                    @if($deletingDealsCount > 0)
                                        <li>{{ $deletingDealsCount }} negocio(s)</li>
                                    @endif
                                    @if($deletingNotesCount > 0)
                                        <li>{{ $deletingNotesCount }} nota(s)</li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <button type="button"
                                wire:click="closeDeleteModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        <button type="button"
                                wire:click="delete"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                            Sí, eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
