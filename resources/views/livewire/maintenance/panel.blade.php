<div>
    <div class="w-full space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Panel de Mantenimiento</h1>
                <p class="text-gray-500 text-sm mt-1">Ejecuta comandos de mantenimiento sin acceso SSH</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500">Laravel {{ app()->version() }}</span>
                <span class="w-2 h-2 rounded-full {{ $isRunning ? 'bg-yellow-500 animate-pulse' : 'bg-green-500' }}"></span>
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
                            <h3 class="font-semibold text-gray-800">Cach&eacute;</h3>
                            <p class="text-xs text-gray-500">Limpiar cach&eacute;s del sistema</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 space-y-2">
                    <button wire:click="runCommand('cache:clear')"
                            wire:loading.attr="disabled"
                            class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-between group disabled:opacity-50">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Limpiar Cach&eacute; App</p>
                            <p class="text-xs text-gray-400">cache:clear</p>
                        </div>
                        <svg wire:loading.remove wire:target="runCommand('cache:clear')" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                        <svg wire:loading wire:target="runCommand('cache:clear')" class="w-5 h-5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </button>

                    <button wire:click="runCommand('config:clear')"
                            wire:loading.attr="disabled"
                            class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-between group disabled:opacity-50">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Limpiar Cach&eacute; Config</p>
                            <p class="text-xs text-gray-400">config:clear</p>
                        </div>
                        <svg wire:loading.remove wire:target="runCommand('config:clear')" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                        <svg wire:loading wire:target="runCommand('config:clear')" class="w-5 h-5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </button>

                    <button wire:click="runCommand('route:clear')"
                            wire:loading.attr="disabled"
                            class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-between group disabled:opacity-50">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Limpiar Cach&eacute; Rutas</p>
                            <p class="text-xs text-gray-400">route:clear</p>
                        </div>
                        <svg wire:loading.remove wire:target="runCommand('route:clear')" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                        <svg wire:loading wire:target="runCommand('route:clear')" class="w-5 h-5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </button>

                    <button wire:click="runCommand('view:clear')"
                            wire:loading.attr="disabled"
                            class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-between group disabled:opacity-50">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Limpiar Cach&eacute; Vistas</p>
                            <p class="text-xs text-gray-400">view:clear</p>
                        </div>
                        <svg wire:loading.remove wire:target="runCommand('view:clear')" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                        <svg wire:loading wire:target="runCommand('view:clear')" class="w-5 h-5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </button>

                    <button wire:click="runCommand('optimize:clear')"
                            wire:loading.attr="disabled"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium text-sm transition flex items-center justify-center gap-2 disabled:opacity-50">
                        <svg wire:loading.remove wire:target="runCommand('optimize:clear')" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <svg wire:loading wire:target="runCommand('optimize:clear')" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
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
                    <button wire:click="runCommand('migrate:status')"
                            wire:loading.attr="disabled"
                            class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-between group disabled:opacity-50">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Estado Migraciones</p>
                            <p class="text-xs text-gray-400">migrate:status</p>
                        </div>
                        <svg wire:loading.remove wire:target="runCommand('migrate:status')" class="w-5 h-5 text-gray-400 group-hover:text-amber-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <svg wire:loading wire:target="runCommand('migrate:status')" class="w-5 h-5 text-amber-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </button>

                    <button wire:click="runCommand('migrate')"
                            wire:loading.attr="disabled"
                            wire:confirm="&iquest;Ejecutar migraciones pendientes? Esta acci&oacute;n puede modificar la base de datos."
                            class="w-full text-left px-4 py-3 rounded-lg border border-amber-200 hover:bg-amber-50 hover:border-amber-300 transition flex items-center justify-between group disabled:opacity-50">
                        <div>
                            <p class="font-medium text-amber-700 text-sm">Ejecutar Migraciones</p>
                            <p class="text-xs text-amber-500">migrate --force</p>
                        </div>
                        <svg wire:loading.remove wire:target="runCommand('migrate')" class="w-5 h-5 text-amber-400 group-hover:text-amber-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                        <svg wire:loading wire:target="runCommand('migrate')" class="w-5 h-5 text-amber-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </button>

                    <button wire:click="runCommand('db:seed')"
                            wire:loading.attr="disabled"
                            wire:confirm="&iquest;Ejecutar seeders? Esta acci&oacute;n puede modificar datos existentes."
                            class="w-full text-left px-4 py-3 rounded-lg border border-amber-200 hover:bg-amber-50 hover:border-amber-300 transition flex items-center justify-between group disabled:opacity-50">
                        <div>
                            <p class="font-medium text-amber-700 text-sm">Ejecutar Seeders</p>
                            <p class="text-xs text-amber-500">db:seed --force</p>
                        </div>
                        <svg wire:loading.remove wire:target="runCommand('db:seed')" class="w-5 h-5 text-amber-400 group-hover:text-amber-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                        <svg wire:loading wire:target="runCommand('db:seed')" class="w-5 h-5 text-amber-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </button>

                    <button wire:click="runCommand('migrate:rollback')"
                            wire:loading.attr="disabled"
                            wire:confirm="&iexcl;PELIGRO! &iquest;Revertir la &uacute;ltima migraci&oacute;n? Esta acci&oacute;n puede causar p&eacute;rdida de datos."
                            class="w-full text-left px-4 py-3 rounded-lg border border-red-200 hover:bg-red-50 hover:border-red-300 transition flex items-center justify-between group disabled:opacity-50">
                        <div>
                            <p class="font-medium text-red-700 text-sm">Rollback Migraci&oacute;n</p>
                            <p class="text-xs text-red-400">migrate:rollback</p>
                        </div>
                        <svg wire:loading.remove wire:target="runCommand('migrate:rollback')" class="w-5 h-5 text-red-400 group-hover:text-red-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <svg wire:loading wire:target="runCommand('migrate:rollback')" class="w-5 h-5 text-red-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
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
                            <h3 class="font-semibold text-gray-800">Optimizaci&oacute;n</h3>
                            <p class="text-xs text-gray-500">Mejorar rendimiento</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 space-y-2">
                    <button wire:click="runCommand('optimize')"
                            wire:loading.attr="disabled"
                            class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-between group disabled:opacity-50">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Optimizar App</p>
                            <p class="text-xs text-gray-400">optimize</p>
                        </div>
                        <svg wire:loading.remove wire:target="runCommand('optimize')" class="w-5 h-5 text-gray-400 group-hover:text-green-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                        <svg wire:loading wire:target="runCommand('optimize')" class="w-5 h-5 text-green-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </button>

                    <button wire:click="runCommand('storage:link')"
                            wire:loading.attr="disabled"
                            class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-between group disabled:opacity-50">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Crear Storage Link</p>
                            <p class="text-xs text-gray-400">storage:link</p>
                        </div>
                        <svg wire:loading.remove wire:target="runCommand('storage:link')" class="w-5 h-5 text-gray-400 group-hover:text-green-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        <svg wire:loading wire:target="runCommand('storage:link')" class="w-5 h-5 text-green-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </button>

                    <!-- System Info -->
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">Informaci&oacute;n del Sistema</h4>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">PHP</dt>
                                <dd class="text-gray-900 font-medium">{{ phpversion() }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Laravel</dt>
                                <dd class="text-gray-900 font-medium">{{ app()->version() }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Entorno</dt>
                                <dd class="text-gray-900 font-medium">{{ app()->environment() }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Debug</dt>
                                <dd>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ config('app.debug') ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                        {{ config('app.debug') ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
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
                    @if($lastCommand)
                        <span class="text-gray-500 text-xs ml-2">- &Uacute;ltimo: {{ $lastCommand }}</span>
                    @endif
                </div>
                <button wire:click="clearOutput" class="text-gray-400 hover:text-white text-sm transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Limpiar
                </button>
            </div>
            <div class="p-4 font-mono text-sm text-gray-300 min-h-[200px] max-h-[400px] overflow-y-auto" id="console-output">
                @if($output)
                    {!! $output !!}
                @else
                    <p class="text-gray-500">// Ejecuta un comando para ver la salida aqu&iacute;...</p>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', ({ el, component }) => {
                const console = document.getElementById('console-output');
                if (console) {
                    console.scrollTop = console.scrollHeight;
                }
            });
        });
    </script>
</div>
