<div>
    @if($show)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-80 transition-opacity" wire:click="close"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit="save">
                        <div class="bg-white dark:bg-gray-800 px-6 pt-6 pb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                {{ $leadId ? 'Editar Contacto' : 'Nuevo Contacto' }}
                            </h3>

                            <div class="space-y-4">
                                <!-- Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
                                    <input type="text"
                                           wire:model="name"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                                           placeholder="Nombre del contacto">
                                    @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                    <input type="email"
                                           wire:model="email"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                                           placeholder="email@ejemplo.com">
                                    @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tel&eacute;fono</label>
                                    <input type="text"
                                           wire:model="phone"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                                           placeholder="+57 300 123 4567">
                                    @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Message -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mensaje / Notas</label>
                                    <textarea wire:model="message"
                                              rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                                              placeholder="Informaci&oacute;n adicional sobre el contacto..."></textarea>
                                    @error('message') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Assigned To -->
                                @if($canAssign)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Asignado a</label>
                                    <select wire:model="assigned_to"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <option value="">Sin asignar</option>
                                        @foreach($assignableUsers as $user)
                                            <option value="{{ $user->uuid }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    @error('assigned_to') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 flex justify-end gap-3">
                            <button type="button"
                                    wire:click="close"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                {{ $leadId ? 'Actualizar' : 'Crear Contacto' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
