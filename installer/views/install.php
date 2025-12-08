<!-- Proceso de Instalación -->
<div class="p-6 border-b border-gray-100">
    <h2 class="text-xl font-bold text-gray-900">Instalar MiniCRM</h2>
    <p class="text-gray-600 mt-1">Todo est&aacute; listo. Haz clic en instalar para comenzar.</p>
</div>

<div class="p-6">
    <!-- Layout de dos columnas -->
    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Columna izquierda: Resumen de configuración -->
        <div>
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">Resumen de Configuraci&oacute;n</h3>

            <div class="bg-gray-50 rounded-xl divide-y divide-gray-200">
                <!-- Base de datos -->
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Base de Datos</p>
                                <?php $db = $installer->getStepData('database'); ?>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($db['database'] ?? 'N/A') ?> @ <?= htmlspecialchars($db['host'] ?? 'localhost') ?></p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>

                <!-- Aplicación -->
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Aplicaci&oacute;n</p>
                                <?php $app = $installer->getStepData('application'); ?>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($app['app_url'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>

                <!-- Administrador -->
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Administrador</p>
                                <?php $admin = $installer->getStepData('admin'); ?>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($admin['email'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Pasos de instalación -->
        <div>
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">Pasos de Instalaci&oacute;n</h3>

            <div class="space-y-2" id="installation-steps">
                <div class="step-item flex items-center gap-3 p-3 bg-gray-50 rounded-lg" data-step="env">
                    <div class="step-icon w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                        <span class="step-number text-xs font-medium text-gray-600">1</span>
                        <svg class="step-check w-4 h-4 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg class="step-spinner w-4 h-4 text-white animate-spin hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <span class="step-text text-sm text-gray-600">Crear archivo de configuraci&oacute;n</span>
                </div>

                <div class="step-item flex items-center gap-3 p-3 bg-gray-50 rounded-lg" data-step="key">
                    <div class="step-icon w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                        <span class="step-number text-xs font-medium text-gray-600">2</span>
                        <svg class="step-check w-4 h-4 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg class="step-spinner w-4 h-4 text-white animate-spin hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <span class="step-text text-sm text-gray-600">Generar clave de aplicaci&oacute;n</span>
                </div>

                <div class="step-item flex items-center gap-3 p-3 bg-gray-50 rounded-lg" data-step="migrations">
                    <div class="step-icon w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                        <span class="step-number text-xs font-medium text-gray-600">3</span>
                        <svg class="step-check w-4 h-4 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg class="step-spinner w-4 h-4 text-white animate-spin hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <span class="step-text text-sm text-gray-600">Ejecutar migraciones de base de datos</span>
                </div>

                <div class="step-item flex items-center gap-3 p-3 bg-gray-50 rounded-lg" data-step="seeders">
                    <div class="step-icon w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                        <span class="step-number text-xs font-medium text-gray-600">4</span>
                        <svg class="step-check w-4 h-4 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg class="step-spinner w-4 h-4 text-white animate-spin hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <span class="step-text text-sm text-gray-600">Crear fases de venta predeterminadas</span>
                </div>

                <div class="step-item flex items-center gap-3 p-3 bg-gray-50 rounded-lg" data-step="admin">
                    <div class="step-icon w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                        <span class="step-number text-xs font-medium text-gray-600">5</span>
                        <svg class="step-check w-4 h-4 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg class="step-spinner w-4 h-4 text-white animate-spin hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <span class="step-text text-sm text-gray-600">Crear usuario administrador</span>
                </div>

                <div class="step-item flex items-center gap-3 p-3 bg-gray-50 rounded-lg" data-step="finalize">
                    <div class="step-icon w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                        <span class="step-number text-xs font-medium text-gray-600">6</span>
                        <svg class="step-check w-4 h-4 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg class="step-spinner w-4 h-4 text-white animate-spin hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <span class="step-text text-sm text-gray-600">Finalizar y optimizar</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Error display (full width) -->
    <div id="installation-error" class="hidden mt-6 p-4 bg-red-50 border border-red-200 rounded-xl">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-red-800">Error durante la instalaci&oacute;n</p>
                <p class="text-sm text-red-600 mt-1 break-words" id="error-message"></p>
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-between">
    <a href="?step=admin" id="back-button"
       class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 font-medium rounded-lg hover:bg-gray-100 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
        </svg>
        Atr&aacute;s
    </a>

    <button type="button" id="install-button" onclick="startInstallation()"
            class="inline-flex items-center gap-2 px-6 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-medium rounded-lg hover:from-green-700 hover:to-emerald-700 transition shadow-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Instalar MiniCRM
    </button>
</div>

<script>
function startInstallation() {
    const installButton = document.getElementById('install-button');
    const backButton = document.getElementById('back-button');

    // Disable buttons
    installButton.disabled = true;
    installButton.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Instalando...';
    installButton.classList.add('opacity-75', 'cursor-not-allowed');
    backButton.classList.add('pointer-events-none', 'opacity-50');

    // Start installation via AJAX
    fetch('?step=install', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=install'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mark all steps as complete
            document.querySelectorAll('.step-item').forEach(item => {
                markStepComplete(item);
            });

            // Redirect to complete page
            setTimeout(() => {
                window.location.href = '?step=complete&email=' + encodeURIComponent(data.admin_email || '');
            }, 1000);
        } else {
            showError(data.message, data.debug_output || null);
            installButton.disabled = false;
            installButton.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Reintentar';
            installButton.classList.remove('opacity-75', 'cursor-not-allowed');
            backButton.classList.remove('pointer-events-none', 'opacity-50');
        }
    })
    .catch(error => {
        showError('Error de conexi\u00f3n: ' + error.message + '. Revisa la consola del navegador para m\u00e1s detalles.');
        console.error('Installation error:', error);
        installButton.disabled = false;
        installButton.innerHTML = 'Reintentar';
        installButton.classList.remove('opacity-75', 'cursor-not-allowed');
        backButton.classList.remove('pointer-events-none', 'opacity-50');
    });

    // Simulate step progress for visual feedback
    simulateSteps();
}

function simulateSteps() {
    const steps = document.querySelectorAll('.step-item');
    let delay = 0;

    steps.forEach((step, index) => {
        setTimeout(() => {
            markStepRunning(step);
        }, delay);

        delay += 800;

        setTimeout(() => {
            markStepComplete(step);
        }, delay);

        delay += 200;
    });
}

function markStepRunning(step) {
    const icon = step.querySelector('.step-icon');
    const number = step.querySelector('.step-number');
    const spinner = step.querySelector('.step-spinner');

    icon.classList.remove('bg-gray-200');
    icon.classList.add('bg-blue-500');
    number.classList.add('hidden');
    spinner.classList.remove('hidden');
    step.querySelector('.step-text').classList.remove('text-gray-600');
    step.querySelector('.step-text').classList.add('text-blue-600', 'font-medium');
}

function markStepComplete(step) {
    const icon = step.querySelector('.step-icon');
    const number = step.querySelector('.step-number');
    const check = step.querySelector('.step-check');
    const spinner = step.querySelector('.step-spinner');

    icon.classList.remove('bg-gray-200', 'bg-blue-500');
    icon.classList.add('bg-green-500');
    number.classList.add('hidden');
    spinner.classList.add('hidden');
    check.classList.remove('hidden');
    step.querySelector('.step-text').classList.remove('text-gray-600', 'text-blue-600');
    step.querySelector('.step-text').classList.add('text-green-600');
}

function showError(message, debugOutput = null) {
    const errorDiv = document.getElementById('installation-error');
    const errorMessage = document.getElementById('error-message');

    let fullMessage = message;
    if (debugOutput) {
        fullMessage += '\n\nDebug: ' + debugOutput;
    }

    errorMessage.textContent = fullMessage;
    errorDiv.classList.remove('hidden');

    // Scroll al error
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>
