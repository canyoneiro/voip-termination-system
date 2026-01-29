<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('webhooks.show', $webhook) }}" class="text-slate-400 hover:text-slate-600 mr-3 p-1 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Historial de Entregas</h2>
                <p class="text-sm text-slate-500 mt-0.5 font-mono">{{ Str::limit($webhook->url, 50) }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Resumen -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="stat-card blue">
                    <div class="text-xs text-slate-500 uppercase tracking-wider font-medium">Total Entregas</div>
                    <div class="text-2xl font-bold text-slate-800 mt-1">{{ $deliveries->total() }}</div>
                </div>
                <div class="stat-card green">
                    <div class="text-xs text-slate-500 uppercase tracking-wider font-medium">Exitosas</div>
                    <div class="text-2xl font-bold text-green-600 mt-1">{{ $deliveries->where('success', true)->count() }}</div>
                </div>
                <div class="stat-card red">
                    <div class="text-xs text-slate-500 uppercase tracking-wider font-medium">Fallidas</div>
                    <div class="text-2xl font-bold text-red-600 mt-1">{{ $deliveries->where('success', false)->count() }}</div>
                </div>
                <div class="stat-card yellow">
                    <div class="text-xs text-slate-500 uppercase tracking-wider font-medium">Ultimo Disparo</div>
                    <div class="text-lg font-bold text-yellow-600 mt-1">{{ $webhook->last_triggered_at?->diffForHumans() ?? 'Nunca' }}</div>
                </div>
            </div>

            <!-- Tabla de Entregas -->
            <div class="dark-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full dark-table">
                        <thead>
                            <tr>
                                <th class="text-left w-44">Fecha/Hora</th>
                                <th class="text-left">Evento</th>
                                <th class="text-center w-28">Estado</th>
                                <th class="text-center w-24">Intentos</th>
                                <th class="text-left">Respuesta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $eventLabels = [
                                    'call.started' => 'Llamada Iniciada',
                                    'call.answered' => 'Llamada Contestada',
                                    'call.ended' => 'Llamada Finalizada',
                                    'customer.minutes_warning' => 'Aviso Minutos',
                                    'customer.minutes_exhausted' => 'Minutos Agotados',
                                    'customer.channels_warning' => 'Aviso Canales',
                                    'carrier.down' => 'Carrier Caido',
                                    'carrier.recovered' => 'Carrier Recuperado',
                                    'alert.created' => 'Alerta Creada',
                                    'test' => 'Test Manual',
                                ];
                            @endphp
                            @forelse($deliveries as $delivery)
                                <tr>
                                    <td class="text-sm text-slate-500 whitespace-nowrap">
                                        {{ $delivery->created_at->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td>
                                        <span class="text-sm text-slate-700">{{ $eventLabels[$delivery->event] ?? $delivery->event }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($delivery->success)
                                            <span class="badge badge-green">Exitoso</span>
                                        @else
                                            <span class="badge badge-red">Fallido</span>
                                        @endif
                                    </td>
                                    <td class="text-center text-sm text-slate-500">{{ $delivery->attempts }}</td>
                                    <td>
                                        @if($delivery->response_code)
                                            <span class="badge {{ $delivery->response_code < 400 ? 'badge-green' : 'badge-red' }}">
                                                {{ $delivery->response_code }}
                                            </span>
                                        @endif
                                        @if($delivery->response_body)
                                            <details class="mt-1 inline-block">
                                                <summary class="text-xs text-blue-600 cursor-pointer hover:text-blue-800 font-medium">Ver respuesta</summary>
                                                <pre class="mt-2 p-3 bg-slate-800 rounded text-xs text-green-400 overflow-x-auto max-w-md font-mono">{{ Str::limit($delivery->response_body, 500) }}</pre>
                                            </details>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-12">
                                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-slate-400">No hay entregas registradas</p>
                                        <form action="{{ route('webhooks.test', $webhook) }}" method="POST" class="inline mt-2">
                                            @csrf
                                            <button type="submit" class="text-blue-600 hover:text-blue-800 font-medium">Enviar test ahora</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($deliveries->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">
                    {{ $deliveries->links() }}
                </div>
                @endif
            </div>

            <div class="mt-6">
                <a href="{{ route('webhooks.show', $webhook) }}" class="text-blue-600 hover:text-blue-800 font-medium">← Volver al Webhook</a>
            </div>
        </div>
    </div>
</x-app-layout>
