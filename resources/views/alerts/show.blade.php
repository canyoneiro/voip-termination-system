<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('alerts.index') }}" class="text-gray-400 hover:text-white mr-3 p-1 hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-white">Detalle de Alerta</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Informacion completa del evento</p>
                </div>
            </div>
            @if(!$alert->acknowledged)
                <form action="{{ route('alerts.acknowledge', $alert) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-success inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Reconocer Alerta
                    </button>
                </form>
            @else
                <span class="badge badge-green text-sm px-4 py-1.5">Reconocida</span>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @php
                $severityLabels = ['critical' => 'Critico', 'warning' => 'Aviso', 'info' => 'Info'];
                $typeLabels = [
                    'carrier_down' => 'Carrier Caido',
                    'carrier_recovered' => 'Carrier Recuperado',
                    'high_failure_rate' => 'Alta Tasa de Fallos',
                    'cps_exceeded' => 'CPS Excedido',
                    'channels_exceeded' => 'Canales Excedidos',
                    'minutes_warning' => 'Aviso de Minutos',
                    'minutes_exhausted' => 'Minutos Agotados',
                    'security_ip_blocked' => 'IP Bloqueada',
                    'security_flood_detected' => 'Flood Detectado',
                    'system_error' => 'Error del Sistema',
                ];
            @endphp

            <div class="dark-card overflow-hidden">
                <!-- Header -->
                <div class="p-6 border-b border-gray-700/50 {{ $alert->severity === 'critical' ? 'bg-red-500/10' : ($alert->severity === 'warning' ? 'bg-yellow-500/10' : 'bg-blue-500/10') }}">
                    <div class="flex items-center gap-3">
                        @if($alert->severity === 'critical')
                            <div class="p-2 bg-red-500/20 rounded-full">
                                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                        @elseif($alert->severity === 'warning')
                            <div class="p-2 bg-yellow-500/20 rounded-full">
                                <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        @else
                            <div class="p-2 bg-blue-500/20 rounded-full">
                                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        @endif
                        <div>
                            <span class="badge {{ $alert->severity === 'critical' ? 'badge-red' : ($alert->severity === 'warning' ? 'badge-yellow' : 'badge-blue') }}">
                                {{ $severityLabels[$alert->severity] ?? ucfirst($alert->severity) }}
                            </span>
                            <span class="ml-2 text-sm text-gray-400">{{ $typeLabels[$alert->type] ?? str_replace('_', ' ', $alert->type) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-white mb-4">{{ $alert->title }}</h3>

                    <div class="bg-gray-800/50 p-4 rounded-lg mb-6">
                        <p class="text-gray-300">{{ $alert->message }}</p>
                    </div>

                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="p-4 bg-gray-800/50 rounded-lg">
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Fecha de Creacion</dt>
                            <dd class="mt-1 font-medium text-gray-200">{{ $alert->created_at->format('d/m/Y H:i:s') }}</dd>
                            <dd class="text-xs text-gray-500">{{ $alert->created_at->diffForHumans() }}</dd>
                        </div>
                        <div class="p-4 bg-gray-800/50 rounded-lg">
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Origen</dt>
                            <dd class="mt-1 font-medium text-gray-200">
                                {{ ucfirst($alert->source_type) }}: {{ $alert->source_name ?? $alert->source_id ?? '-' }}
                            </dd>
                        </div>
                        @if($alert->acknowledged)
                        <div class="p-4 bg-green-500/10 rounded-lg">
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Reconocida</dt>
                            <dd class="mt-1 font-medium text-green-400">{{ $alert->acknowledged_at?->format('d/m/Y H:i:s') ?? '-' }}</dd>
                        </div>
                        <div class="p-4 bg-green-500/10 rounded-lg">
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Reconocida Por</dt>
                            <dd class="mt-1 font-medium text-green-400">{{ $alert->acknowledgedBy?->name ?? 'Sistema' }}</dd>
                        </div>
                        @endif
                        <div class="p-4 bg-gray-800/50 rounded-lg col-span-2">
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">UUID</dt>
                            <dd class="mt-1 font-mono text-xs text-gray-400">{{ $alert->uuid }}</dd>
                        </div>
                    </dl>

                    @if($alert->metadata)
                    <div class="border-t border-gray-700/50 pt-6">
                        <h4 class="text-sm font-medium text-gray-300 mb-3">Metadatos Adicionales</h4>
                        <pre class="bg-gray-900 text-green-400 p-4 rounded-lg text-xs overflow-x-auto font-mono scrollbar-dark">{{ json_encode($alert->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="px-6 py-4 bg-gray-800/50 border-t border-gray-700/50 flex justify-between items-center">
                    <a href="{{ route('alerts.index') }}" class="text-blue-400 hover:text-blue-300 text-sm">
                        ← Volver a Alertas
                    </a>
                    <form action="{{ route('alerts.destroy', $alert) }}" method="POST" onsubmit="return confirm('¿Estas seguro de eliminar esta alerta?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm">
                            Eliminar Alerta
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
