<x-layouts.app title="Kanban">
    <div class="w-full">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Vista Kanban</h1>
                <p class="text-gray-500 text-sm mt-1">Arrastra y suelta leads entre las columnas</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('leads.index') }}" class="text-gray-600 hover:text-gray-800 px-4 py-2 text-sm font-medium flex items-center gap-2 transition border border-gray-300 rounded-lg hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    Vista Lista
                </a>
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuevo Lead
                </button>
            </div>
        </div>

        <!-- Kanban Board -->
        <div class="flex gap-4 overflow-x-auto pb-4">
            <!-- Column: Sin Contactar -->
            <div class="flex-shrink-0 w-80">
                <div class="bg-gray-100 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-gray-400"></div>
                            <h3 class="font-semibold text-gray-700">Sin Contactar</h3>
                        </div>
                        <span class="bg-gray-200 text-gray-600 text-xs font-bold px-2 py-1 rounded-full">0</span>
                    </div>
                    <div class="space-y-3 min-h-[200px]">
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                            <p class="text-gray-400 text-sm">Sin leads</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column: Calificado -->
            <div class="flex-shrink-0 w-80">
                <div class="bg-blue-50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                            <h3 class="font-semibold text-gray-700">Calificado</h3>
                        </div>
                        <span class="bg-blue-200 text-blue-700 text-xs font-bold px-2 py-1 rounded-full">0</span>
                    </div>
                    <div class="space-y-3 min-h-[200px]">
                        <div class="border-2 border-dashed border-blue-200 rounded-lg p-6 text-center">
                            <p class="text-blue-300 text-sm">Sin leads</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column: Negociación -->
            <div class="flex-shrink-0 w-80">
                <div class="bg-purple-50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-purple-500"></div>
                            <h3 class="font-semibold text-gray-700">Negociación</h3>
                        </div>
                        <span class="bg-purple-200 text-purple-700 text-xs font-bold px-2 py-1 rounded-full">0</span>
                    </div>
                    <div class="space-y-3 min-h-[200px]">
                        <div class="border-2 border-dashed border-purple-200 rounded-lg p-6 text-center">
                            <p class="text-purple-300 text-sm">Sin leads</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drop Zones for Closed States -->
        <div class="grid grid-cols-2 gap-4 mt-6">
            <!-- Cerrado Ganado -->
            <div class="bg-green-50 border-2 border-dashed border-green-300 rounded-xl p-6 text-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-green-700">Cerrado Ganado</h3>
                <p class="text-green-500 text-sm mt-1">Arrastra aquí para marcar como ganado</p>
            </div>

            <!-- Cerrado Perdido -->
            <div class="bg-red-50 border-2 border-dashed border-red-300 rounded-xl p-6 text-center">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-red-700">Cerrado Perdido</h3>
                <p class="text-red-500 text-sm mt-1">Arrastra aquí para marcar como perdido</p>
            </div>
        </div>
    </div>
</x-layouts.app>
