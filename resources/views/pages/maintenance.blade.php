<x-layouts.app title="Mantenimiento">
    <div class="w-full space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Panel de Mantenimiento</h1>
                <p class="text-gray-500 text-sm mt-1">Ejecuta comandos de mantenimiento sin acceso SSH</p>
            </div>
        </div>

        <!-- Commands Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Cache Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Caché</h3>
                            <p class="text-xs text-gray-500">Limpiar cachés del sistema</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 space-y-2">
                    <button class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-between group">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Limpiar Caché App</p>
                            <p class="text-xs text-gray-400">cache:clear</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                    <button class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-between group">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Limpiar Caché Config</p>
                            <p class="text-xs text-gray-400">config:clear</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                    <button class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-between group">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Limpiar Caché Rutas</p>
                            <p class="text-xs text-gray-400">route:clear</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                    <button class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-between group">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Limpiar Caché Vistas</p>
                            <p class="text-xs text-gray-400">view:clear</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium text-sm transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Limpiar Todo
                    </button>
                </div>
            </div>

            <!-- Database Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Base de Datos</h3>
                            <p class="text-xs text-gray-500">Migraciones y seeders</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 space-y-2">
                    <button class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-between group">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Ejecutar Migraciones</p>
                            <p class="text-xs text-gray-400">migrate --force</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-amber-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                    <button class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-between group">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Ejecutar Seeders</p>
                            <p class="text-xs text-gray-400">db:seed --force</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-amber-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                    <button class="w-full text-left px-4 py-3 rounded-lg border border-red-200 hover:bg-red-50 hover:border-red-300 transition flex items-center justify-between group">
                        <div>
                            <p class="font-medium text-red-700 text-sm">Rollback Migración</p>
                            <p class="text-xs text-red-400">migrate:rollback</p>
                        </div>
                        <svg class="w-5 h-5 text-red-400 group-hover:text-red-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Optimization Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Optimización</h3>
                            <p class="text-xs text-gray-500">Mejorar rendimiento</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 space-y-2">
                    <button class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-between group">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Optimizar App</p>
                            <p class="text-xs text-gray-400">optimize</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                    <button class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-between group">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Crear Storage Link</p>
                            <p class="text-xs text-gray-400">storage:link</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Output Console -->
        <div class="bg-gray-900 rounded-xl overflow-hidden">
            <div class="px-4 py-3 bg-gray-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex gap-1.5">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    </div>
                    <span class="text-gray-400 text-sm ml-2">Consola de Salida</span>
                </div>
                <button class="text-gray-400 hover:text-white text-sm transition">Limpiar</button>
            </div>
            <div class="p-4 font-mono text-sm text-gray-300 min-h-[150px]">
                <p class="text-gray-500">// Ejecuta un comando para ver la salida aquí...</p>
            </div>
        </div>
    </div>
</x-layouts.app>
