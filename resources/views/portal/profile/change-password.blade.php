<x-portal.layouts.app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Cambiar Password
            </h2>
            <a href="{{ route('portal.profile.show') }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dark-card p-6">
                <div class="mb-6">
                    <h3 class="font-semibold text-slate-800 mb-2">Actualizar Password</h3>
                    <p class="text-slate-600 text-sm">
                        Asegurese de utilizar un password seguro y unico para proteger su cuenta.
                    </p>
                </div>

                <form method="POST" action="{{ route('portal.profile.password.update') }}">
                    @csrf
                    @method('PUT')

                    <!-- Current Password -->
                    <div class="mb-4">
                        <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1">
                            Password Actual <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="current_password" name="current_password"
                               class="dark-input w-full" required autocomplete="current-password">
                        @error('current_password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">
                            Nuevo Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="password" name="password"
                               class="dark-input w-full" required autocomplete="new-password">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-slate-500 text-xs mt-1">
                            Minimo 8 caracteres, incluir mayusculas, minusculas y numeros
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">
                            Confirmar Nuevo Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="dark-input w-full" required autocomplete="new-password">
                    </div>

                    <!-- Requirements -->
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-6">
                        <p class="text-sm font-medium text-slate-700 mb-2">Requisitos del password:</p>
                        <ul class="text-sm text-slate-600 space-y-1">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-slate-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Minimo 8 caracteres
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-slate-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Al menos una letra mayuscula
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-slate-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Al menos una letra minuscula
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-slate-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Al menos un numero
                            </li>
                        </ul>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('portal.profile.show') }}" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary">Cambiar Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-portal.layouts.app>
