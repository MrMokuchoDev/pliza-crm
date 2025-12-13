<?php
$adminData = $data ?: $installer->getStepData('admin');
?>

<!-- Crear Usuario Administrador -->
<div class="p-4 border-b border-gray-100">
    <h2 class="text-xl font-bold text-gray-900">Crear Usuario Administrador</h2>
    <p class="text-gray-600 text-sm">Este ser&aacute; el usuario principal para acceder al sistema.</p>
</div>

<form method="POST" action="?step=admin" class="p-4">
    <!-- Layout de dos columnas -->
    <div class="grid lg:grid-cols-2 gap-4">
        <!-- Columna izquierda: Datos personales -->
        <div class="space-y-3">
            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Datos del Usuario</h3>

            <!-- Name -->
            <div>
                <label for="admin_name" class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre Completo <span class="text-red-500">*</span>
                </label>
                <input type="text" name="admin_name" id="admin_name" required
                       value="<?= htmlspecialchars($adminData['name'] ?? '') ?>"
                       placeholder="Tu nombre"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
            </div>

            <!-- Email -->
            <div>
                <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-1">
                    Correo Electr&oacute;nico <span class="text-red-500">*</span>
                </label>
                <input type="email" name="admin_email" id="admin_email" required
                       value="<?= htmlspecialchars($adminData['email'] ?? '') ?>"
                       placeholder="admin@tudominio.com"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
                <p class="text-xs text-gray-500 mt-1">Ser&aacute; tu usuario para iniciar sesi&oacute;n</p>
            </div>

            <!-- Password Requirements (compacto) -->
            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-xs font-medium text-gray-700 mb-2">Requisitos:</p>
                <ul class="space-y-1 text-xs text-gray-500" id="password-requirements">
                    <li class="flex items-center gap-2" id="req-length">
                        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Al menos 8 caracteres
                    </li>
                    <li class="flex items-center gap-2" id="req-match">
                        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Las contrase&ntilde;as coinciden
                    </li>
                </ul>
            </div>
        </div>

        <!-- Columna derecha: Contraseña -->
        <div class="space-y-3">
            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Contrase&ntilde;a</h3>

            <!-- Password -->
            <div>
                <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1">
                    Contrase&ntilde;a <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" name="admin_password" id="admin_password" required
                           minlength="8"
                           placeholder="M&iacute;nimo 8 caracteres"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm pr-10">
                    <button type="button" onclick="togglePassword('admin_password', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Password Confirmation -->
            <div>
                <label for="admin_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                    Confirmar Contrase&ntilde;a <span class="text-red-500">*</span>
                </label>
                <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" required
                       minlength="8"
                       placeholder="Repite la contrase&ntilde;a"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
            </div>

            <!-- Info -->
            <div class="p-3 bg-blue-50 rounded-lg">
                <div class="flex items-start gap-2 text-xs text-blue-800">
                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Guarda estas credenciales en un lugar seguro. Las necesitar&aacute;s para iniciar sesi&oacute;n.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between">
        <a href="?step=application"
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
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const eyeOpen = button.querySelector('.eye-open');
    const eyeClosed = button.querySelector('.eye-closed');

    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.classList.add('hidden');
        eyeClosed.classList.remove('hidden');
    } else {
        input.type = 'password';
        eyeOpen.classList.remove('hidden');
        eyeClosed.classList.add('hidden');
    }
}

// Password validation
const password = document.getElementById('admin_password');
const confirmation = document.getElementById('admin_password_confirmation');
const reqLength = document.getElementById('req-length');
const reqMatch = document.getElementById('req-match');

function validatePassword() {
    // Length check
    if (password.value.length >= 8) {
        reqLength.querySelector('svg').classList.remove('text-gray-300');
        reqLength.querySelector('svg').classList.add('text-green-500');
        reqLength.classList.add('text-green-600');
    } else {
        reqLength.querySelector('svg').classList.add('text-gray-300');
        reqLength.querySelector('svg').classList.remove('text-green-500');
        reqLength.classList.remove('text-green-600');
    }

    // Match check
    if (password.value && confirmation.value && password.value === confirmation.value) {
        reqMatch.querySelector('svg').classList.remove('text-gray-300');
        reqMatch.querySelector('svg').classList.add('text-green-500');
        reqMatch.classList.add('text-green-600');
    } else {
        reqMatch.querySelector('svg').classList.add('text-gray-300');
        reqMatch.querySelector('svg').classList.remove('text-green-500');
        reqMatch.classList.remove('text-green-600');
    }
}

password.addEventListener('input', validatePassword);
confirmation.addEventListener('input', validatePassword);
</script>
