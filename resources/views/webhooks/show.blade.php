<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <a href="{{ route('webhooks.index') }}" class="text-slate-400 hover:text-slate-600 mr-3 p-1 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Detalle del Webhook</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Configuracion y estado de entregas</p>
                </div>
            </div>
            <div class="flex gap-2">
                <form action="{{ route('webhooks.test', $webhook) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn-success inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Enviar Test
                    </button>
                </form>
                <a href="{{ route('webhooks.edit', $webhook) }}" class="btn-primary">Editar</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700">{{ session('success') }}</div>
            @endif

            @if(session('new_secret'))
                <div class="mb-4 warning-box">
                    <h4 class="font-bold">Guarda tu clave secreta</h4>
                    <p class="text-sm text-slate-600 mt-1">Esta clave solo se mostrara una vez. Usala para verificar las firmas:</p>
                    <code class="block mt-2 p-3 bg-slate-800 rounded-lg font-mono text-sm text-green-400 break-all">{{ session('new_secret') }}</code>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Info del Webhook -->
                <div class="dark-card p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">Configuracion</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs text-slate-500 uppercase tracking-wider font-medium">URL del Endpoint</dt>
                            <dd class="mt-1 font-mono text-sm text-slate-700 break-all bg-slate-50 p-3 rounded-lg border border-slate-200">{{ $webhook->url }}</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-xs text-slate-500 uppercase tracking-wider font-medium">Cliente</dt>
                                <dd class="mt-1 font-medium text-slate-700">{{ $webhook->customer->name ?? 'Global (todos)' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-500 uppercase tracking-wider font-medium">Estado</dt>
                                <dd class="mt-1">
                                    @if($webhook->active)
                                        <span class="badge badge-green">Activo</span>
                                    @else
                                        <span class="badge badge-gray">Inactivo</span>
                                    @endif
                                </dd>
                            </div>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 uppercase tracking-wider font-medium">UUID</dt>
                            <dd class="mt-1 font-mono text-xs text-slate-500">{{ $webhook->uuid }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 uppercase tracking-wider font-medium">Creado</dt>
                            <dd class="mt-1 text-sm text-slate-600">{{ $webhook->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6 pt-4 border-t border-slate-200">
                        <form action="{{ route('webhooks.regenerate-secret', $webhook) }}" method="POST" onsubmit="return confirm('Esto invalidara la clave actual. ¿Continuar?')">
                            @csrf
                            <button type="submit" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Regenerar Clave Secreta</button>
                        </form>
                    </div>
                </div>

                <!-- Eventos y Stats -->
                <div class="dark-card p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">Eventos Suscritos</h3>
                    <div class="flex flex-wrap gap-2 mb-6">
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
                            ];
                        @endphp
                        @foreach($webhook->events ?? [] as $event)
                            <span class="px-3 py-1 text-sm bg-blue-50 text-blue-700 rounded-full border border-blue-200 font-medium">{{ $eventLabels[$event] ?? $event }}</span>
                        @endforeach
                    </div>

                    <h4 class="text-sm font-medium text-slate-600 mb-3">Estadisticas de Entrega</h4>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <dt class="text-xs text-slate-500 font-medium">Ultimo Disparo</dt>
                            <dd class="mt-1 font-medium text-slate-700 text-sm">{{ $webhook->last_triggered_at?->diffForHumans() ?? 'Nunca' }}</dd>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <dt class="text-xs text-slate-500 font-medium">Ultimo Codigo</dt>
                            <dd class="mt-1">
                                @if($webhook->last_status_code)
                                    <span class="badge {{ $webhook->last_status_code < 400 ? 'badge-green' : 'badge-red' }}">
                                        {{ $webhook->last_status_code }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </dd>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <dt class="text-xs text-slate-500 font-medium">Fallos</dt>
                            <dd class="mt-1 font-medium {{ $webhook->failure_count > 0 ? 'text-red-600' : 'text-slate-700' }}">{{ $webhook->failure_count }}</dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Entregas Recientes -->
            <div class="mt-6 dark-card overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-slate-800">Entregas Recientes</h3>
                    <a href="{{ route('webhooks.deliveries', $webhook) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Ver todas →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full dark-table">
                        <thead>
                            <tr>
                                <th class="text-left w-40">Fecha/Hora</th>
                                <th class="text-left">Evento</th>
                                <th class="text-center w-28">Estado</th>
                                <th class="text-center w-24">Intentos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDeliveries as $delivery)
                                <tr>
                                    <td class="text-sm text-slate-500">{{ $delivery->created_at->format('d/m H:i:s') }}</td>
                                    <td class="text-sm text-slate-700">{{ $eventLabels[$delivery->event] ?? $delivery->event }}</td>
                                    <td class="text-center">
                                        @if($delivery->success)
                                            <span class="badge badge-green">{{ $delivery->response_code }}</span>
                                        @else
                                            <span class="badge badge-red">{{ $delivery->response_code ?? 'Error' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center text-sm text-slate-500">{{ $delivery->attempts }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-slate-400">No hay entregas registradas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Ejemplo de Verificacion -->
            <div class="mt-6 dark-card p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Verificacion de Firma</h3>
                <p class="text-sm text-slate-600 mb-4">
                    Verifica la autenticidad de los webhooks comprobando el header <code class="bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 text-blue-600">X-Webhook-Signature</code>.
                </p>
                <pre class="bg-slate-800 text-green-400 p-4 rounded-lg text-sm overflow-x-auto font-mono">
// Ejemplo en PHP
$timestamp = $_SERVER['HTTP_X_WEBHOOK_TIMESTAMP'];
$body = file_get_contents('php://input');
$payload = "{$timestamp}.{$body}";
$expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (hash_equals($expected, $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'])) {
    // Firma valida - procesar webhook
    $data = json_decode($body, true);
} else {
    // Firma invalida - rechazar
    http_response_code(401);
}</pre>
            </div>

            <div class="mt-6">
                <a href="{{ route('webhooks.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">← Volver a Webhooks</a>
            </div>
        </div>
    </div>
</x-app-layout>
