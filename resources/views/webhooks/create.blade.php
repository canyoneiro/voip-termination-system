<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('webhooks.index') }}" class="text-slate-400 hover:text-slate-600 mr-3 p-1 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Nuevo Webhook</h2>
                <p class="text-sm text-slate-500 mt-0.5">Configura notificaciones HTTP automaticas para eventos</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dark-card">
                <form action="{{ route('webhooks.store') }}" method="POST" class="p-6">
                    @csrf

                    <!-- URL -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">Configuracion del Endpoint</h3>
                        <div>
                            <label for="url" class="block text-sm font-medium text-slate-700">URL del Webhook *</label>
                            <input type="url" name="url" id="url" required value="{{ old('url') }}" placeholder="https://tu-servidor.com/webhook"
                                class="dark-input mt-1 w-full font-mono text-sm">
                            <p class="mt-1.5 text-xs text-slate-500">El servidor debe responder con codigo 2xx para confirmar recepcion</p>
                            @error('url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <!-- Cliente -->
                    <div class="mb-6">
                        <label for="customer_id" class="block text-sm font-medium text-slate-700">Cliente Asociado</label>
                        <select name="customer_id" id="customer_id" class="dark-select mt-1 w-full">
                            <option value="">Global (todos los eventos del sistema)</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-xs text-slate-500">Dejar vacio para recibir eventos de todos los clientes</p>
                    </div>

                    <!-- Eventos -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">Eventos a Suscribir *</h3>
                        @php
                            $eventLabels = [
                                'call.started' => ['label' => 'Llamada Iniciada', 'desc' => 'Cuando se recibe un nuevo INVITE'],
                                'call.answered' => ['label' => 'Llamada Contestada', 'desc' => 'Cuando el destino contesta (200 OK)'],
                                'call.ended' => ['label' => 'Llamada Finalizada', 'desc' => 'Al terminar, incluye CDR completo'],
                                'customer.minutes_warning' => ['label' => 'Aviso de Minutos', 'desc' => 'Al alcanzar 80% del limite'],
                                'customer.minutes_exhausted' => ['label' => 'Minutos Agotados', 'desc' => 'Al consumir todos los minutos'],
                                'customer.channels_warning' => ['label' => 'Aviso de Canales', 'desc' => 'Al usar 80% de canales'],
                                'carrier.down' => ['label' => 'Carrier Caido', 'desc' => 'Cuando un carrier deja de responder'],
                                'carrier.recovered' => ['label' => 'Carrier Recuperado', 'desc' => 'Cuando un carrier vuelve a responder'],
                                'alert.created' => ['label' => 'Alerta Creada', 'desc' => 'Para cualquier alerta del sistema'],
                            ];
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($availableEvents as $event)
                                <label class="flex items-start p-3 bg-slate-50 border border-slate-200 rounded-lg hover:bg-slate-100 cursor-pointer transition-colors">
                                    <input type="checkbox" name="events[]" value="{{ $event }}"
                                        {{ in_array($event, old('events', [])) ? 'checked' : '' }}
                                        class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <div class="ml-3">
                                        <span class="block text-sm font-medium text-slate-700">{{ $eventLabels[$event]['label'] ?? $event }}</span>
                                        <span class="block text-xs text-slate-500">{{ $eventLabels[$event]['desc'] ?? '' }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('events')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Info de Seguridad -->
                    <div class="mb-6 info-box">
                        <h4 class="text-sm font-semibold mb-2">Seguridad del Webhook</h4>
                        <p class="text-sm text-slate-600">
                            Se generara automaticamente una clave secreta para firmar los webhooks.
                            Usala para verificar la autenticidad de las peticiones recibidas.
                        </p>
                        <div class="mt-3 text-xs text-slate-500">
                            <strong class="text-slate-700">Headers enviados:</strong>
                            <code class="ml-1 bg-white px-1.5 py-0.5 rounded border border-slate-200 text-blue-600">X-Webhook-Signature</code>,
                            <code class="bg-white px-1.5 py-0.5 rounded border border-slate-200 text-blue-600">X-Webhook-Event</code>,
                            <code class="bg-white px-1.5 py-0.5 rounded border border-slate-200 text-blue-600">X-Webhook-Timestamp</code>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                        <a href="{{ route('webhooks.index') }}" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary">Crear Webhook</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
