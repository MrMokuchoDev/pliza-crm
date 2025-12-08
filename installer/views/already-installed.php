<!-- Ya está instalado -->
<div class="p-8 text-center">
    <div class="w-20 h-20 bg-gradient-to-br from-amber-500 to-orange-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
    </div>

    <h2 class="text-2xl font-bold text-gray-900 mb-2">MiniCRM ya est&aacute; instalado</h2>
    <p class="text-gray-600 mb-8 max-w-md mx-auto">
        El sistema detecta que MiniCRM ya fue instalado anteriormente. Por seguridad, el instalador est&aacute; desactivado.
    </p>

    <div class="bg-gray-50 rounded-xl p-6 mb-8 text-left max-w-md mx-auto">
        <h3 class="font-semibold text-gray-900 mb-3">Si necesitas reinstalar:</h3>
        <ol class="space-y-2 text-sm text-gray-600 list-decimal list-inside">
            <li>Haz un backup de tu base de datos</li>
            <li>Elimina el archivo <code class="bg-gray-200 px-1.5 py-0.5 rounded text-xs">storage/installed.lock</code></li>
            <li>Opcionalmente, elimina el archivo <code class="bg-gray-200 px-1.5 py-0.5 rounded text-xs">.env</code></li>
            <li>Vuelve a acceder a esta p&aacute;gina</li>
        </ol>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="/"
           class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-indigo-700 transition shadow-lg shadow-blue-500/25">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Ir al Dashboard
        </a>
        <a href="/login"
           class="inline-flex items-center justify-center gap-2 px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            Iniciar Sesi&oacute;n
        </a>
    </div>
</div>

<div class="border-t border-gray-100 bg-gray-50 p-6">
    <div class="flex items-center justify-center gap-2 text-sm text-gray-500">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Si olvidaste tu contrase&ntilde;a, usa la opci&oacute;n "Recuperar contrase&ntilde;a" en el login</span>
    </div>
</div>
