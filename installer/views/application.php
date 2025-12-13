<?php
$appData = $data ?: $installer->getStepData('application');

// Detectar URL actual
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$detectedUrl = $protocol . '://' . $host;

// Remover /install.php si está presente
$detectedUrl = preg_replace('/\/install\.php.*$/', '', $detectedUrl);

// Timezones comunes
$timezones = [
    'America/Bogota' => 'Colombia (Bogotá)',
    'America/Mexico_City' => 'México (Ciudad de México)',
    'America/Lima' => 'Perú (Lima)',
    'America/Santiago' => 'Chile (Santiago)',
    'America/Buenos_Aires' => 'Argentina (Buenos Aires)',
    'America/Caracas' => 'Venezuela (Caracas)',
    'America/New_York' => 'Estados Unidos (Nueva York)',
    'America/Los_Angeles' => 'Estados Unidos (Los Ángeles)',
    'Europe/Madrid' => 'España (Madrid)',
    'UTC' => 'UTC (Tiempo Universal)',
];
?>

<!-- Configuración de Aplicación -->
<div class="p-4 border-b border-gray-100">
    <h2 class="text-xl font-bold text-gray-900">Configuraci&oacute;n de la Aplicaci&oacute;n</h2>
    <p class="text-gray-600 text-sm">Configura los par&aacute;metros generales de Pliza CRM.</p>
</div>

<form method="POST" action="?step=application" class="p-4">
    <!-- Layout de dos columnas -->
    <div class="grid lg:grid-cols-2 gap-4">
        <!-- Columna izquierda -->
        <div class="space-y-3">
            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider">General</h3>

            <!-- App URL -->
            <div>
                <label for="app_url" class="block text-sm font-medium text-gray-700 mb-1">
                    URL del Sitio <span class="text-red-500">*</span>
                </label>
                <input type="url" name="app_url" id="app_url" required
                       value="<?= htmlspecialchars($appData['app_url'] ?? $detectedUrl) ?>"
                       placeholder="https://tudominio.com"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
                <p class="text-xs text-gray-500 mt-1">Sin barra final</p>
            </div>

            <!-- App Name -->
            <div>
                <label for="app_name" class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre de la Aplicaci&oacute;n
                </label>
                <input type="text" name="app_name" id="app_name"
                       value="<?= htmlspecialchars($appData['app_name'] ?? 'Pliza CRM') ?>"
                       placeholder="Pliza CRM"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
            </div>

            <!-- Timezone and Locale -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="app_timezone" class="block text-sm font-medium text-gray-700 mb-1">Zona Horaria</label>
                    <select name="app_timezone" id="app_timezone"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
                        <?php foreach ($timezones as $tz => $label): ?>
                        <option value="<?= $tz ?>" <?= ($appData['app_timezone'] ?? 'America/Bogota') === $tz ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="app_locale" class="block text-sm font-medium text-gray-700 mb-1">Idioma</label>
                    <select name="app_locale" id="app_locale"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
                        <option value="es" <?= ($appData['app_locale'] ?? 'es') === 'es' ? 'selected' : '' ?>>Espa&ntilde;ol</option>
                        <option value="en" <?= ($appData['app_locale'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Columna derecha -->
        <div class="space-y-3">
            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Entorno</h3>

            <!-- Environment -->
            <div class="space-y-2">
                <label class="relative flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition
                    <?= ($appData['app_env'] ?? 'production') === 'production' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' ?>">
                    <input type="radio" name="app_env" value="production"
                           <?= ($appData['app_env'] ?? 'production') === 'production' ? 'checked' : '' ?>
                           class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                    <div class="ml-3">
                        <span class="block text-sm font-medium text-gray-900">Producci&oacute;n</span>
                        <span class="block text-xs text-gray-500">Oculta errores. Recomendado.</span>
                    </div>
                </label>
                <label class="relative flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition
                    <?= ($appData['app_env'] ?? '') === 'local' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' ?>">
                    <input type="radio" name="app_env" value="local"
                           <?= ($appData['app_env'] ?? '') === 'local' ? 'checked' : '' ?>
                           class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                    <div class="ml-3">
                        <span class="block text-sm font-medium text-gray-900">Desarrollo</span>
                        <span class="block text-xs text-gray-500">Muestra errores detallados.</span>
                    </div>
                </label>
            </div>

            <!-- Info Box compacto -->
            <div class="p-3 bg-amber-50 rounded-lg border border-amber-200">
                <div class="flex items-start gap-2 text-sm text-amber-800">
                    <svg class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="text-xs">Si usas HTTPS, configura el certificado SSL antes de usar la aplicaci&oacute;n.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between">
        <a href="?step=database"
           class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 font-medium rounded-lg hover:bg-gray-100 transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
            </svg>
            Atr&aacute;s
        </a>

        <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 transition shadow-sm text-sm">
            Continuar
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>
    </div>
</form>

<script>
// Highlight selected radio option
document.querySelectorAll('input[name="app_env"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('input[name="app_env"]').forEach(r => {
            r.closest('label').classList.remove('border-blue-500', 'bg-blue-50');
            r.closest('label').classList.add('border-gray-200');
        });
        if (this.checked) {
            this.closest('label').classList.remove('border-gray-200');
            this.closest('label').classList.add('border-blue-500', 'bg-blue-50');
        }
    });
});
</script>
