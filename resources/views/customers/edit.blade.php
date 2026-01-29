<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('customers.show', $customer) }}" class="text-gray-400 hover:text-white mr-3 p-1 hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-white">Editar: {{ $customer->name }}</h2>
                <p class="text-sm text-gray-400 mt-0.5">Modifica la configuracion del cliente</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dark-card">
                <form action="{{ route('customers.update', $customer) }}" method="POST" class="p-6">
                    @csrf @method('PUT')

                    <!-- Estado -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between p-4 bg-gray-800/50 rounded-lg">
                            <div>
                                <h3 class="text-sm font-semibold text-white">Estado del Cliente</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Activa o desactiva este cliente para recibir trafico</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="active" value="0">
                                <input type="checkbox" name="active" value="1" class="sr-only peer" {{ $customer->active ? 'checked' : '' }}>
                                <div class="w-14 h-7 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-500"></div>
                                <span class="ml-3 text-sm font-medium {{ $customer->active ? 'text-green-400' : 'text-gray-400' }}">{{ $customer->active ? 'Activo' : 'Inactivo' }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Informacion basica -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Informacion Basica</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-300">Nombre *</label>
                                <input type="text" name="name" id="name" required value="{{ old('name', $customer->name) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                                @error('name')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="company" class="block text-sm font-medium text-gray-300">Empresa</label>
                                <input type="text" name="company" id="company" value="{{ old('company', $customer->company) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $customer->email) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-300">Telefono</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $customer->phone) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                        </div>
                    </div>

                    <!-- Limites -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Limites de Servicio</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="max_channels" class="block text-sm font-medium text-gray-300">Canales Simultaneos *</label>
                                <input type="number" name="max_channels" id="max_channels" required min="1" value="{{ old('max_channels', $customer->max_channels) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                            <div>
                                <label for="max_cps" class="block text-sm font-medium text-gray-300">CPS Maximo *</label>
                                <input type="number" name="max_cps" id="max_cps" required min="1" value="{{ old('max_cps', $customer->max_cps) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                            <div>
                                <label for="max_daily_minutes" class="block text-sm font-medium text-gray-300">Minutos Diarios</label>
                                <input type="number" name="max_daily_minutes" id="max_daily_minutes" min="0" value="{{ old('max_daily_minutes', $customer->max_daily_minutes) }}"
                                    class="dark-input mt-1 w-full py-2 px-3" placeholder="Sin limite">
                                <p class="mt-1 text-xs text-gray-500">Usados hoy: <span class="text-blue-400 font-medium">{{ number_format($customer->used_daily_minutes) }}</span> minutos</p>
                            </div>
                            <div>
                                <label for="max_monthly_minutes" class="block text-sm font-medium text-gray-300">Minutos Mensuales</label>
                                <input type="number" name="max_monthly_minutes" id="max_monthly_minutes" min="0" value="{{ old('max_monthly_minutes', $customer->max_monthly_minutes) }}"
                                    class="dark-input mt-1 w-full py-2 px-3" placeholder="Sin limite">
                                <p class="mt-1 text-xs text-gray-500">Usados este mes: <span class="text-blue-400 font-medium">{{ number_format($customer->used_monthly_minutes) }}</span> minutos</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notificaciones -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Notificaciones</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="alert_email" class="block text-sm font-medium text-gray-300">Email de Alertas</label>
                                <input type="email" name="alert_email" id="alert_email" value="{{ old('alert_email', $customer->alert_email) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                            <div>
                                <label for="alert_telegram_chat_id" class="block text-sm font-medium text-gray-300">Telegram Chat ID</label>
                                <input type="text" name="alert_telegram_chat_id" id="alert_telegram_chat_id" value="{{ old('alert_telegram_chat_id', $customer->alert_telegram_chat_id) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="mb-8">
                        <label for="notes" class="block text-sm font-medium text-gray-300">Notas Internas</label>
                        <textarea name="notes" id="notes" rows="3"
                            class="dark-input mt-1 w-full py-2 px-3">{{ old('notes', $customer->notes) }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-700/50">
                        <a href="{{ route('customers.show', $customer) }}" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
