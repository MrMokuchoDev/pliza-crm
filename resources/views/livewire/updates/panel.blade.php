<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Actualizaciones del Sistema</h1>
        <p class="text-gray-600">Gestiona las actualizaciones de Pliza CRM</p>
    </div>

    {{-- Current Version Info --}}
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Versi&oacute;n Actual</h2>
                <p class="text-3xl font-bold text-indigo-600 mt-1">v{{ $currentVersion }}</p>
                @if($lastChecked)
                    <p class="text-sm text-gray-500 mt-2">&Uacute;ltima verificaci&oacute;n: {{ $lastChecked }}</p>
                @endif
            </div>
            <div>
                <button
                    wire:click="checkForUpdates"
                    wire:loading.attr="disabled"
                    wire:target="checkForUpdates"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                >
                    <span wire:loading.remove wire:target="checkForUpdates">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </span>
                    <span wire:loading wire:target="checkForUpdates">
                        <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <span wire:loading.remove wire:target="checkForUpdates">Verificar Actualizaciones</span>
                    <span wire:loading wire:target="checkForUpdates">Verificando...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Error Alert --}}
    @if($error)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-red-800">Error</h3>
                    <p class="text-sm text-red-700 mt-1">{{ $error }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Update Available --}}
    @if($updateAvailable && $latestRelease)
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
            <div class="flex items-start justify-between">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-green-800">Nueva Versi&oacute;n Disponible</h3>
                        <p class="text-green-700 mt-1">
                            Versi&oacute;n <strong>v{{ $latestRelease['version'] }}</strong> disponible
                            @if($latestRelease['published_at'])
                                <span class="text-green-600">({{ \Carbon\Carbon::parse($latestRelease['published_at'])->format('d/m/Y') }})</span>
                            @endif
                        </p>
                    </div>
                </div>
                <button
                    wire:click="performFullUpdate"
                    wire:loading.attr="disabled"
                    wire:target="performFullUpdate,applyUpdate"
                    wire:confirm="¿Iniciar actualización a v{{ $latestRelease['version'] }}? Se creará un backup automático antes de actualizar."
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    {{ $isUpdating ? 'disabled' : '' }}
                >
                    <span wire:loading.remove wire:target="performFullUpdate,applyUpdate">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </span>
                    <span wire:loading wire:target="performFullUpdate,applyUpdate">
                        <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <span wire:loading.remove wire:target="performFullUpdate,applyUpdate">Actualizar Ahora</span>
                    <span wire:loading wire:target="performFullUpdate,applyUpdate">Actualizando...</span>
                </button>
            </div>

            {{-- Changelog --}}
            @if($latestRelease['changelog'])
                <div class="mt-4 pt-4 border-t border-green-200">
                    <h4 class="text-sm font-semibold text-green-800 mb-2">Notas de la Versi&oacute;n:</h4>
                    <div class="bg-white rounded-lg p-4 text-sm text-gray-700 prose prose-sm max-w-none">
                        {!! \Illuminate\Support\Str::markdown($latestRelease['changelog']) !!}
                    </div>
                </div>
            @endif
        </div>
    @elseif($latestVersion && !$updateAvailable && !$error)
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-6">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Sistema Actualizado</h3>
                    <p class="text-gray-600">Ya tienes la &uacute;ltima versi&oacute;n disponible.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Logs Console --}}
    @if(count($logs) > 0)
        <div class="bg-gray-900 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-300">Consola de Actualizaci&oacute;n</h3>
                <button
                    wire:click="clearLogs"
                    class="text-xs text-gray-400 hover:text-gray-200 transition"
                >
                    Limpiar
                </button>
            </div>
            <div class="font-mono text-sm text-green-400 space-y-1 max-h-64 overflow-y-auto">
                @foreach($logs as $log)
                    <div class="{{ str_contains($log, 'ERROR') ? 'text-red-400' : '' }}">{{ $log }}</div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Backups Section --}}
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Backups Disponibles</h2>
                <p class="text-sm text-gray-500">Copias de seguridad creadas antes de actualizar</p>
            </div>
            <button
                wire:click="createBackup"
                wire:loading.attr="disabled"
                wire:target="createBackup"
                class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition text-sm"
            >
                <span wire:loading.remove wire:target="createBackup">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                </span>
                <span wire:loading wire:target="createBackup">
                    <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
                <span wire:loading.remove wire:target="createBackup">Crear Backup Manual</span>
                <span wire:loading wire:target="createBackup">Creando...</span>
            </button>
        </div>

        @if(count($backups) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tama&ntilde;o</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ubicaci&oacute;n</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($backups as $backup)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $backup['date'] }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $backup['size'] }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 font-mono">{{ $backup['name'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                <p>No hay backups disponibles</p>
            </div>
        @endif
    </div>

    {{-- Info Section --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-blue-700">
                <p class="font-medium mb-1">Informaci&oacute;n sobre actualizaciones</p>
                <ul class="list-disc list-inside space-y-1 text-blue-600">
                    <li>Las actualizaciones se descargan desde GitHub Releases</li>
                    <li>Se crea un backup autom&aacute;tico antes de cada actualizaci&oacute;n</li>
                    <li>Los archivos .env y uploads no se sobrescriben</li>
                    <li>Las migraciones de BD se ejecutan autom&aacute;ticamente</li>
                </ul>
            </div>
        </div>
    </div>
</div>
