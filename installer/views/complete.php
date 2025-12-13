<?php
$adminEmail = $_GET['email'] ?? '';
$appUrl = $installer->getStepData('application')['app_url'] ?? '';
?>

<!-- Instalación Completada -->
<div class="p-8 text-center">
    <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <h2 class="text-2xl font-bold text-gray-900 mb-2">&iexcl;Instalaci&oacute;n Completada!</h2>
    <p class="text-gray-600 mb-6 max-w-lg mx-auto">
        Pliza CRM se ha instalado correctamente. Ya puedes comenzar a gestionar tus leads y negocios.
    </p>

    <!-- Credenciales -->
    <div class="bg-blue-50 rounded-xl p-6 mb-8 text-left max-w-md mx-auto">
        <h3 class="font-semibold text-blue-900 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            Credenciales de Acceso
        </h3>
        <div class="space-y-2 text-sm">
            <div class="flex items-center justify-between p-2 bg-white rounded-lg">
                <span class="text-blue-700">Email:</span>
                <span class="font-mono text-blue-900"><?= htmlspecialchars($adminEmail) ?></span>
            </div>
            <div class="flex items-center justify-between p-2 bg-white rounded-lg">
                <span class="text-blue-700">Contrase&ntilde;a:</span>
                <span class="text-blue-900">La que configuraste</span>
            </div>
        </div>
    </div>

    <!-- Warning -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-8 text-left max-w-md mx-auto">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="text-sm text-amber-800">
                <p class="font-medium">Importante</p>
                <p class="mt-1">Por seguridad, el instalador ha sido desactivado. Si necesitas reinstalar, elimina el archivo <code class="bg-amber-100 px-1 rounded">storage/installed.lock</code></p>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="grid sm:grid-cols-3 gap-4 max-w-lg mx-auto mb-8">
        <a href="<?= htmlspecialchars($appUrl) ?>/login"
           class="p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-blue-200 transition">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-900">Iniciar Sesi&oacute;n</p>
        </a>
        <a href="<?= htmlspecialchars($appUrl) ?>/contactos"
           class="p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-green-200 transition">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-900">Contactos</p>
        </a>
        <a href="<?= htmlspecialchars($appUrl) ?>/pipeline"
           class="p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-purple-200 transition">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-900">Pipeline</p>
        </a>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/login"
       class="inline-flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-indigo-700 transition shadow-lg shadow-blue-500/25">
        Ir al Panel de Administraci&oacute;n
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
    </a>
</div>

<!-- Next Steps -->
<div class="border-t border-gray-100 bg-gray-50 p-6">
    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4 text-center">
        Pr&oacute;ximos pasos recomendados
    </h3>
    <div class="grid sm:grid-cols-2 gap-4 max-w-lg mx-auto">
        <div class="flex items-start gap-3 p-3 bg-white rounded-lg">
            <div class="w-6 h-6 bg-blue-100 rounded flex items-center justify-center flex-shrink-0 mt-0.5">
                <span class="text-xs font-bold text-blue-600">1</span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-900">Configurar fases de venta</p>
                <p class="text-xs text-gray-500">Personaliza tu pipeline seg&uacute;n tu proceso comercial</p>
            </div>
        </div>
        <div class="flex items-start gap-3 p-3 bg-white rounded-lg">
            <div class="w-6 h-6 bg-blue-100 rounded flex items-center justify-center flex-shrink-0 mt-0.5">
                <span class="text-xs font-bold text-blue-600">2</span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-900">Registrar sitios web</p>
                <p class="text-xs text-gray-500">Agrega los dominios donde usar&aacute;s los widgets</p>
            </div>
        </div>
        <div class="flex items-start gap-3 p-3 bg-white rounded-lg">
            <div class="w-6 h-6 bg-blue-100 rounded flex items-center justify-center flex-shrink-0 mt-0.5">
                <span class="text-xs font-bold text-blue-600">3</span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-900">Instalar widgets</p>
                <p class="text-xs text-gray-500">Copia el c&oacute;digo en tus p&aacute;ginas web</p>
            </div>
        </div>
        <div class="flex items-start gap-3 p-3 bg-white rounded-lg">
            <div class="w-6 h-6 bg-blue-100 rounded flex items-center justify-center flex-shrink-0 mt-0.5">
                <span class="text-xs font-bold text-blue-600">4</span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-900">Crear tu primer contacto</p>
                <p class="text-xs text-gray-500">Prueba el sistema agregando un lead manual</p>
            </div>
        </div>
    </div>
</div>
