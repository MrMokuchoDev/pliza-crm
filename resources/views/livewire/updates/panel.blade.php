<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Actualizaciones del Sistema</h1>
        <p class="text-gray-600 dark:text-gray-400">Gestiona las actualizaciones de Pliza CRM</p>
    </div>

    {{-- Error Alert (full width) --}}
    @if($error)
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 dark:text-red-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Error</h3>
                    <p class="text-sm text-red-700 dark:text-red-400 mt-1">{{ $error }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Main Grid Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Column: Version Info + Update Status --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Current Version Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Versi&oacute;n Actual</h2>
                <p class="text-4xl font-bold text-indigo-600 dark:text-indigo-400">v{{ $currentVersion }}</p>
                @if($lastChecked)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">&Uacute;ltima verificaci&oacute;n: {{ $lastChecked }}</p>
                @endif
                <button
                    wire:click="checkForUpdates"
                    wire:loading.attr="disabled"
                    wire:target="checkForUpdates"
                    class="mt-4 w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition text-sm"
                >
                    <span wire:loading.remove wire:target="checkForUpdates">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </span>
                    <span wire:loading wire:target="checkForUpdates">
                        <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <span wire:loading.remove wire:target="checkForUpdates">Verificar</span>
                    <span wire:loading wire:target="checkForUpdates">Verificando...</span>
                </button>
            </div>

            {{-- Update Status Card --}}
            @if($updateAvailable && $latestRelease)
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-5">
                    <div class="flex items-center mb-3">
                        <svg class="w-5 h-5 text-green-500 dark:text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-sm font-semibold text-green-800 dark:text-green-300">Nueva Versi&oacute;n</h3>
                    </div>
                    <p class="text-2xl font-bold text-green-700 dark:text-green-400 mb-1">v{{ $latestRelease['version'] }}</p>
                    @if($latestRelease['published_at'])
                        <p class="text-xs text-green-600 dark:text-green-400 mb-4">{{ \Carbon\Carbon::parse($latestRelease['published_at'])->format('d/m/Y') }}</p>
                    @endif
                    <button
                        wire:click="performFullUpdate"
                        wire:loading.attr="disabled"
                        wire:target="performFullUpdate,applyUpdate"
                        wire:confirm="&iquest;Iniciar actualizaci&oacute;n a v{{ $latestRelease['version'] }}? Se crear&aacute; un backup autom&aacute;tico antes de actualizar."
                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition text-sm"
                        {{ $isUpdating ? 'disabled' : '' }}
                    >
                        <span wire:loading.remove wire:target="performFullUpdate,applyUpdate">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                        </span>
                        <span wire:loading wire:target="performFullUpdate,applyUpdate">
                            <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="performFullUpdate,applyUpdate">Actualizar Ahora</span>
                        <span wire:loading wire:target="performFullUpdate,applyUpdate">Actualizando...</span>
                    </button>
                </div>
            @elseif($latestVersion && !$updateAvailable && !$error)
                <div class="bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg p-5">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Sistema Actualizado</h3>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Ya tienes la &uacute;ltima versi&oacute;n disponible.</p>
                </div>
            @endif

            {{-- Info Section --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-4 h-4 text-blue-500 dark:text-blue-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-xs text-blue-700 dark:text-blue-300">
                        <p class="font-medium mb-1">Informaci&oacute;n</p>
                        <ul class="space-y-1 text-blue-600 dark:text-blue-400">
                            <li>&bull; Descarga desde GitHub Releases</li>
                            <li>&bull; Backup autom&aacute;tico</li>
                            <li>&bull; .env y uploads protegidos</li>
                            <li>&bull; Migraciones autom&aacute;ticas</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Changelog, Console, Backups --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Changelog (only when update available) --}}
            @if($updateAvailable && $latestRelease && $latestRelease['changelog'])
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">Notas de la Versi&oacute;n v{{ $latestRelease['version'] }}</h3>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-sm text-gray-700 dark:text-gray-300 prose prose-sm dark:prose-invert max-w-none max-h-48 overflow-y-auto">
                        {!! \Illuminate\Support\Str::markdown($latestRelease['changelog']) !!}
                    </div>
                </div>
            @endif

            {{-- Logs Console --}}
            <div class="bg-gray-900 rounded-lg p-4 {{ count($logs) > 0 ? '' : 'hidden' }}" wire:key="console-{{ count($logs) }}">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-300">Consola de Actualizaci&oacute;n</h3>
                    <button
                        wire:click="clearLogs"
                        class="text-xs text-gray-400 hover:text-gray-200 transition"
                    >
                        Limpiar
                    </button>
                </div>
                <div class="font-mono text-xs text-green-400 space-y-1 max-h-40 overflow-y-auto">
                    @foreach($logs as $log)
                        <div class="{{ str_contains($log, 'ERROR') ? 'text-red-400' : '' }}">{{ $log }}</div>
                    @endforeach
                </div>
            </div>

            {{-- Backups Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Backups Disponibles</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Copias de seguridad antes de actualizar</p>
                    </div>
                    <button
                        wire:click="createBackup"
                        wire:loading.attr="disabled"
                        wire:target="createBackup"
                        class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition text-xs"
                    >
                        <span wire:loading.remove wire:target="createBackup">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                            </svg>
                        </span>
                        <span wire:loading wire:target="createBackup">
                            <svg class="animate-spin w-3.5 h-3.5 mr-1.5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="createBackup">Crear Backup</span>
                        <span wire:loading wire:target="createBackup">Creando...</span>
                    </button>
                </div>

                @if(count($backups) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tama&ntilde;o</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Archivo</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($backups as $backup)
                                    <tr>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 dark:text-gray-100">{{ $backup['date'] }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">{{ $backup['size'] }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 font-mono truncate max-w-xs">{{ $backup['name'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                        <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        <p class="text-sm">No hay backups disponibles</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
