<?php
$dbData = $data ?: $installer->getStepData('database');
?>

<!-- Configuración de Base de Datos -->
<div class="p-4 border-b border-gray-100">
    <h2 class="text-xl font-bold text-gray-900">Configuraci&oacute;n de Base de Datos</h2>
    <p class="text-gray-600 text-sm">Ingresa los datos de conexi&oacute;n a tu base de datos.</p>
</div>

<form method="POST" action="?step=database" class="p-4">
    <!-- Info compacta -->
    <div class="bg-blue-50 rounded-lg p-3 mb-4">
        <div class="flex items-center gap-2 text-sm text-blue-800">
            <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>La base de datos debe existir previamente. Cr&eacute;ala desde cPanel o phpMyAdmin.</span>
        </div>
    </div>

    <!-- Layout de dos columnas -->
    <div class="grid lg:grid-cols-2 gap-4">
        <!-- Columna izquierda: Conexión -->
        <div class="space-y-3">
            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Conexi&oacute;n</h3>

            <!-- Driver -->
            <div>
                <label for="db_driver" class="block text-sm font-medium text-gray-700 mb-1">Motor</label>
                <select name="db_driver" id="db_driver"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
                    <option value="mysql" <?= ($dbData['driver'] ?? 'mysql') === 'mysql' ? 'selected' : '' ?>>MySQL / MariaDB</option>
                    <option value="pgsql" <?= ($dbData['driver'] ?? '') === 'pgsql' ? 'selected' : '' ?>>PostgreSQL</option>
                </select>
            </div>

            <!-- Host y Puerto -->
            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-2">
                    <label for="db_host" class="block text-sm font-medium text-gray-700 mb-1">Host</label>
                    <input type="text" name="db_host" id="db_host"
                           value="<?= htmlspecialchars($dbData['host'] ?? 'localhost') ?>"
                           placeholder="localhost"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
                </div>
                <div>
                    <label for="db_port" class="block text-sm font-medium text-gray-700 mb-1">Puerto</label>
                    <input type="text" name="db_port" id="db_port"
                           value="<?= htmlspecialchars($dbData['port'] ?? '3306') ?>"
                           placeholder="3306"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
                </div>
            </div>

            <!-- Test Connection -->
            <div class="p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700">Probar conexi&oacute;n</span>
                    <button type="button" onclick="testConnection()"
                            class="px-3 py-1.5 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 transition">
                        Probar
                    </button>
                </div>
                <div id="connection-result" class="mt-2 hidden text-sm"></div>
            </div>
        </div>

        <!-- Columna derecha: Credenciales -->
        <div class="space-y-3">
            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Credenciales</h3>

            <!-- Database Name -->
            <div>
                <label for="db_database" class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre de la Base de Datos <span class="text-red-500">*</span>
                </label>
                <input type="text" name="db_database" id="db_database" required
                       value="<?= htmlspecialchars($dbData['database'] ?? '') ?>"
                       placeholder="pliza_crm"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
            </div>

            <!-- Username -->
            <div>
                <label for="db_username" class="block text-sm font-medium text-gray-700 mb-1">
                    Usuario <span class="text-red-500">*</span>
                </label>
                <input type="text" name="db_username" id="db_username" required
                       value="<?= htmlspecialchars($dbData['username'] ?? '') ?>"
                       placeholder="usuario_bd"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
            </div>

            <!-- Password -->
            <div>
                <label for="db_password" class="block text-sm font-medium text-gray-700 mb-1">
                    Contrase&ntilde;a
                </label>
                <input type="password" name="db_password" id="db_password"
                       value="<?= htmlspecialchars($dbData['password'] ?? '') ?>"
                       placeholder="••••••••"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
                <p class="text-xs text-gray-500 mt-1">Dejar vac&iacute;o si no tiene</p>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between">
        <a href="?step=requirements"
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
function testConnection() {
    const form = document.querySelector('form');
    const formData = new FormData(form);
    formData.append('action', 'test');

    const resultDiv = document.getElementById('connection-result');
    resultDiv.innerHTML = '<div class="flex items-center gap-2 text-gray-600"><svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Probando...</div>';
    resultDiv.classList.remove('hidden');

    fetch('?step=database&action=test', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = '<div class="flex items-center gap-2 text-green-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> ' + data.message + '</div>';
        } else {
            resultDiv.innerHTML = '<div class="flex items-center gap-2 text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> ' + data.message + '</div>';
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<div class="flex items-center gap-2 text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Error al probar</div>';
    });
}
</script>
