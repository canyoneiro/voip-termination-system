<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('customers.index') }}" class="text-gray-400 hover:text-white mr-3 p-1 hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-white">Nuevo Cliente</h2>
                <p class="text-sm text-gray-400 mt-0.5">Configura un nuevo cliente para terminacion VoIP</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dark-card">
                <form action="{{ route('customers.store') }}" method="POST" class="p-6">
                    @csrf

                    <!-- Informacion basica -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Informacion Basica</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-300">Nombre *</label>
                                <input type="text" name="name" id="name" required value="{{ old('name') }}"
                                    class="dark-input mt-1 w-full py-2 px-3" placeholder="Nombre del cliente">
                                @error('name')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="company" class="block text-sm font-medium text-gray-300">Empresa</label>
                                <input type="text" name="company" id="company" value="{{ old('company') }}"
                                    class="dark-input mt-1 w-full py-2 px-3" placeholder="Nombre de la empresa">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                    class="dark-input mt-1 w-full py-2 px-3" placeholder="contacto@empresa.com">
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-300">Telefono</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                    class="dark-input mt-1 w-full py-2 px-3" placeholder="+34 600 000 000">
                            </div>
                        </div>
                    </div>

                    <!-- Limites -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Limites de Servicio</h3>
                        <p class="text-xs text-gray-500 mb-4">Define los limites de uso para este cliente. Estos limites se verifican en tiempo real.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="max_channels" class="block text-sm font-medium text-gray-300">Canales Simultaneos *</label>
                                <input type="number" name="max_channels" id="max_channels" required min="1" value="{{ old('max_channels', 10) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                                <p class="mt-1 text-xs text-gray-500">Maximo de llamadas activas al mismo tiempo</p>
                            </div>
                            <div>
                                <label for="max_cps" class="block text-sm font-medium text-gray-300">CPS Maximo *</label>
                                <input type="number" name="max_cps" id="max_cps" required min="1" value="{{ old('max_cps', 5) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                                <p class="mt-1 text-xs text-gray-500">Llamadas por segundo permitidas</p>
                            </div>
                            <div>
                                <label for="max_daily_minutes" class="block text-sm font-medium text-gray-300">Minutos Diarios</label>
                                <input type="number" name="max_daily_minutes" id="max_daily_minutes" min="0" value="{{ old('max_daily_minutes') }}"
                                    class="dark-input mt-1 w-full py-2 px-3" placeholder="Sin limite">
                                <p class="mt-1 text-xs text-gray-500">Dejar vacio o 0 para sin limite</p>
                            </div>
                            <div>
                                <label for="max_monthly_minutes" class="block text-sm font-medium text-gray-300">Minutos Mensuales</label>
                                <input type="number" name="max_monthly_minutes" id="max_monthly_minutes" min="0" value="{{ old('max_monthly_minutes') }}"
                                    class="dark-input mt-1 w-full py-2 px-3" placeholder="Sin limite">
                                <p class="mt-1 text-xs text-gray-500">Dejar vacio o 0 para sin limite</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notificaciones -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Notificaciones</h3>
                        <p class="text-xs text-gray-500 mb-4">Configura donde enviar alertas cuando se alcancen los limites.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="alert_email" class="block text-sm font-medium text-gray-300">Email de Alertas</label>
                                <input type="email" name="alert_email" id="alert_email" value="{{ old('alert_email') }}"
                                    class="dark-input mt-1 w-full py-2 px-3" placeholder="alertas@empresa.com">
                            </div>
                            <div>
                                <label for="alert_telegram_chat_id" class="block text-sm font-medium text-gray-300">Telegram Chat ID</label>
                                <input type="text" name="alert_telegram_chat_id" id="alert_telegram_chat_id" value="{{ old('alert_telegram_chat_id') }}"
                                    class="dark-input mt-1 w-full py-2 px-3" placeholder="123456789">
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="mb-8">
                        <label for="notes" class="block text-sm font-medium text-gray-300">Notas Internas</label>
                        <textarea name="notes" id="notes" rows="3"
                            class="dark-input mt-1 w-full py-2 px-3" placeholder="Notas internas sobre este cliente...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-700/50">
                        <a href="{{ route('customers.index') }}" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary">Crear Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
