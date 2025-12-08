<?php
$dbData = $data ?: $installer->getStepData('database');
?>

<!-- Configuración de Base de Datos -->
<div class="p-6 border-b border-gray-100">
    <h2 class="text-xl font-bold text-gray-900">Configuraci&oacute;n de Base de Datos</h2>
    <p class="text-gray-600 mt-1">Ingresa los datos de conexi&oacute;n a tu base de datos MySQL.</p>
</div>

<form method="POST" action="?step=database" class="p-6">
    <div class="bg-blue-50 rounded-xl p-4 mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-blue-800">
                <p class="font-medium">La base de datos debe existir previamente</p>
                <p class="mt-1">Cr&eacute;ala desde cPanel (MySQL Databases) o phpMyAdmin antes de continuar.</p>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <!-- Driver -->
        <div>
            <label for="db_driver" class="block text-sm font-medium text-gray-700 mb-1">
                Motor de Base de Datos
            </label>
            <select name="db_driver" id="db_driver"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                <option value="mysql" <?= ($dbData['driver'] ?? 'mysql') === 'mysql' ? 'selected' : '' ?>>MySQL / MariaDB</option>
                <option value="pgsql" <?= ($dbData['driver'] ?? '') === 'pgsql' ? 'selected' : '' ?>>PostgreSQL</option>
            </select>
        </div>

        <!-- Host y Puerto -->
        <div class="grid sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <label for="db_host" class="block text-sm font-medium text-gray-700 mb-1">
                    Host del Servidor
                </label>
                <input type="text" name="db_host" id="db_host"
                       value="<?= htmlspecialchars($dbData['host'] ?? 'localhost') ?>"
                       placeholder="localhost"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                <p class="text-xs text-gray-500 mt-1">Generalmente es "localhost" o "127.0.0.1"</p>
            </div>
            <div>
                <label for="db_port" class="block text-sm font-medium text-gray-700 mb-1">
                    Puerto
                </label>
                <input type="text" name="db_port" id="db_port"
                       value="<?= htmlspecialchars($dbData['port'] ?? '3306') ?>"
                       placeholder="3306"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            </div>
        </div>

        <!-- Database Name -->
        <div>
            <label for="db_database" class="block text-sm font-medium text-gray-700 mb-1">
                Nombre de la Base de Datos <span class="text-red-500">*</span>
            </label>
            <input type="text" name="db_database" id="db_database" required
                   value="<?= htmlspecialchars($dbData['database'] ?? '') ?>"
                   placeholder="minicrm"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            <p class="text-xs text-gray-500 mt-1">Nombre exacto de la base de datos creada</p>
        </div>

        <!-- Username -->
        <div>
            <label for="db_username" class="block text-sm font-medium text-gray-700 mb-1">
                Usuario <span class="text-red-500">*</span>
            </label>
            <input type="text" name="db_username" id="db_username" required
                   value="<?= htmlspecialchars($dbData['username'] ?? '') ?>"
                   placeholder="usuario_bd"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            <p class="text-xs text-gray-500 mt-1">Usuario con permisos en la base de datos</p>
        </div>

        <!-- Password -->
        <div>
            <label for="db_password" class="block text-sm font-medium text-gray-700 mb-1">
                Contrase&ntilde;a
            </label>
            <input type="password" name="db_password" id="db_password"
                   value="<?= htmlspecialchars($dbData['password'] ?? '') ?>"
                   placeholder="••••••••"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            <p class="text-xs text-gray-500 mt-1">Dejar vac&iacute;o si no tiene contrase&ntilde;a</p>
        </div>
    </div>

    <!-- Test Connection Button -->
    <div class="mt-6 p-4 bg-gray-50 rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <p class="font-medium text-gray-900">Probar conexi&oacute;n</p>
                <p class="text-sm text-gray-500">Verifica que los datos sean correctos antes de continuar</p>
            </div>
            <button type="button" onclick="testConnection()"
                    class="px-4 py-2 bg-gray-800 text-white font-medium rounded-lg hover:bg-gray-900 transition">
                Probar
            </button>
        </div>
        <div id="connection-result" class="mt-3 hidden"></div>
    </div>

    <!-- Actions -->
    <div class="mt-6 pt-6 border-t border-gray-100 flex justify-between">
        <a href="?step=requirements"
           class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 font-medium rounded-lg hover:bg-gray-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
            </svg>
            Atr&aacute;s
        </a>

        <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 transition shadow-sm">
            Continuar
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    resultDiv.innerHTML = '<div class="flex items-center gap-2 text-gray-600"><svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Probando conexi&oacute;n...</div>';
    resultDiv.classList.remove('hidden');

    fetch('?step=database&action=test', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = '<div class="flex items-center gap-2 text-green-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> ' + data.message + '</div>';
        } else {
            resultDiv.innerHTML = '<div class="flex items-center gap-2 text-red-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> ' + data.message + '</div>';
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<div class="flex items-center gap-2 text-red-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Error al probar la conexi&oacute;n</div>';
    });
}
</script>
