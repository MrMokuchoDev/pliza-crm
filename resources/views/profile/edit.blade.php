<x-layouts.app title="Mi Perfil">
    <div class="w-full">
        <!-- Profile Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 mb-6 text-white shadow-lg">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                    <span class="text-2xl font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold">{{ auth()->user()->name }}</h1>
                    <p class="text-blue-100 text-sm">{{ auth()->user()->email }}</p>
                    <p class="text-blue-200 text-xs mt-0.5">Miembro desde {{ auth()->user()->created_at->format('d M, Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid lg:grid-cols-2 gap-6">
            <!-- Update Profile Information -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden h-fit">
                <div class="border-b border-gray-100 dark:border-gray-700 px-5 py-3 bg-gray-50 dark:bg-gray-700/50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white">Información Personal</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Actualiza tu nombre y correo</p>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                        @csrf
                    </form>

                    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
                        @csrf
                        @method('patch')

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nombre</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name', $user->name) }}"
                                required
                                autofocus
                                autocomplete="name"
                                class="block w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm placeholder-gray-400 dark:placeholder-gray-500"
                            >
                            <x-input-error class="mt-1" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Correo Electrónico</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email', $user->email) }}"
                                required
                                autocomplete="username"
                                class="block w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm placeholder-gray-400 dark:placeholder-gray-500"
                            >
                            <x-input-error class="mt-1" :messages="$errors->get('email')" />
                        </div>

                        <div class="flex items-center gap-3 pt-1">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Guardar
                            </button>

                            @if (session('status') === 'profile-updated')
                                <p
                                    x-data="{ show: true }"
                                    x-show="show"
                                    x-transition
                                    x-init="setTimeout(() => show = false, 2000)"
                                    class="text-sm text-green-600 dark:text-green-400 font-medium"
                                >Guardado</p>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Update Password -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden h-fit">
                <div class="border-b border-gray-100 dark:border-gray-700 px-5 py-3 bg-gray-50 dark:bg-gray-700/50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white">Cambiar Contraseña</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Usa una contraseña segura</p>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                        @csrf
                        @method('put')

                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Contraseña Actual</label>
                            <input
                                id="current_password"
                                name="current_password"
                                type="password"
                                autocomplete="current-password"
                                class="block w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm placeholder-gray-400 dark:placeholder-gray-500"
                            >
                            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nueva</label>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    autocomplete="new-password"
                                    class="block w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm placeholder-gray-400 dark:placeholder-gray-500"
                                >
                                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Confirmar</label>
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    autocomplete="new-password"
                                    class="block w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm placeholder-gray-400 dark:placeholder-gray-500"
                                >
                                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-1">
                            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Actualizar
                            </button>

                            @if (session('status') === 'password-updated')
                                <p
                                    x-data="{ show: true }"
                                    x-show="show"
                                    x-transition
                                    x-init="setTimeout(() => show = false, 2000)"
                                    class="text-sm text-green-600 dark:text-green-400 font-medium"
                                >Actualizada</p>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Account - Full Width -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-red-100 dark:border-red-900/50 overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-5 gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-red-800 dark:text-red-400">Zona de Peligro</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Una vez eliminada, no podrás recuperar tu cuenta ni sus datos.</p>
                        </div>
                    </div>

                    <form method="post" action="{{ route('profile.destroy') }}" class="flex-shrink-0" x-data="{ showConfirm: false }">
                        @csrf
                        @method('delete')

                        <button
                            type="button"
                            @click="showConfirm = true"
                            class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center gap-2 text-sm"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Eliminar Cuenta
                        </button>

                        <!-- Confirmation Modal -->
                        <div x-show="showConfirm" class="fixed inset-0 bg-black bg-opacity-50 dark:bg-opacity-70 flex items-center justify-center z-50" x-cloak>
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md mx-4 p-6" @click.away="showConfirm = false">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">¿Estás seguro?</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Esta acción no se puede deshacer. Escribe tu contraseña para confirmar.</p>

                                <div class="mb-4">
                                    <label for="delete_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Contraseña</label>
                                    <input
                                        id="delete_password"
                                        name="password"
                                        type="password"
                                        class="block w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm placeholder-gray-400 dark:placeholder-gray-500"
                                        placeholder="Tu contraseña actual"
                                    >
                                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1" />
                                </div>

                                <div class="flex justify-end gap-3">
                                    <button type="button" @click="showConfirm = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition text-sm">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition text-sm">
                                        Sí, Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
