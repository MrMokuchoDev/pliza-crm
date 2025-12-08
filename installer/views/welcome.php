<!-- Bienvenida -->
<div class="p-8 text-center">
    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
    </div>

    <h2 class="text-2xl font-bold text-gray-900 mb-2">Bienvenido a MiniCRM</h2>
    <p class="text-gray-600 mb-8 max-w-md mx-auto">
        Este asistente te guiar&aacute; paso a paso para instalar y configurar MiniCRM en tu servidor.
    </p>

    <div class="bg-blue-50 rounded-xl p-6 mb-8 text-left max-w-md mx-auto">
        <h3 class="font-semibold text-blue-900 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Antes de comenzar, aseg&uacute;rate de tener:
        </h3>
        <ul class="space-y-2 text-sm text-blue-800">
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Base de datos MySQL creada (nombre, usuario y contrase&ntilde;a)
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                PHP 8.2 o superior con las extensiones requeridas
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Permisos de escritura en las carpetas storage/ y bootstrap/cache/
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Email y contrase&ntilde;a para el usuario administrador
            </li>
        </ul>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="?step=requirements"
           class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-indigo-700 transition shadow-lg shadow-blue-500/25">
            Comenzar Instalaci&oacute;n
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </a>
    </div>
</div>

<!-- Features -->
<div class="border-t border-gray-100 bg-gray-50 p-8">
    <h3 class="text-center text-sm font-semibold text-gray-500 uppercase tracking-wider mb-6">
        Caracter&iacute;sticas principales
    </h3>
    <div class="grid sm:grid-cols-3 gap-6">
        <div class="text-center">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h4 class="font-medium text-gray-900">Gesti&oacute;n de Leads</h4>
            <p class="text-sm text-gray-500 mt-1">Captura y gestiona contactos desde m&uacute;ltiples fuentes</p>
        </div>
        <div class="text-center">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/>
                </svg>
            </div>
            <h4 class="font-medium text-gray-900">Pipeline Visual</h4>
            <p class="text-sm text-gray-500 mt-1">Tablero Kanban para seguimiento de negocios</p>
        </div>
        <div class="text-center">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
            </div>
            <h4 class="font-medium text-gray-900">Widgets Embebibles</h4>
            <p class="text-sm text-gray-500 mt-1">Formularios y botones para tu sitio web</p>
        </div>
    </div>
</div>
