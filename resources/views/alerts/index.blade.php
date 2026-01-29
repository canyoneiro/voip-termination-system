<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center">
                    <h2 class="text-2xl font-bold text-white">Alertas del Sistema</h2>
                    @if($unacknowledgedCount > 0)
                        <span class="ml-3 px-2.5 py-1 text-sm font-bold text-white bg-red-500 rounded-full animate-pulse">{{ $unacknowledgedCount }}</span>
                    @endif
                </div>
                <p class="text-sm text-gray-400 mt-0.5">Eventos y notificaciones que requieren atencion</p>
            </div>
            @if($unacknowledgedCount > 0)
                <form action="{{ route('alerts.acknowledge-multiple') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-success inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Reconocer Todas
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400">{{ session('success') }}</div>
            @endif

            <!-- Filtros -->
            <div class="dark-card mb-6">
                <div class="px-4 py-3 border-b border-gray-700/50">
                    <h3 class="text-sm font-semibold text-white">Filtros de Busqueda</h3>
                </div>
                <form action="{{ route('alerts.index') }}" method="GET" class="p-4">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Severidad</label>
                            <select name="severity" class="dark-select w-full text-sm py-1.5 px-2">
                                <option value="">Todas</option>
                                <option value="info" {{ request('severity') === 'info' ? 'selected' : '' }}>Informacion</option>
                                <option value="warning" {{ request('severity') === 'warning' ? 'selected' : '' }}>Aviso</option>
                                <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critico</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Tipo</label>
                            <select name="type" class="dark-select w-full text-sm py-1.5 px-2">
                                <option value="">Todos</option>
                                <option value="carrier_down" {{ request('type') === 'carrier_down' ? 'selected' : '' }}>Carrier Caido</option>
                                <option value="carrier_recovered" {{ request('type') === 'carrier_recovered' ? 'selected' : '' }}>Carrier Recuperado</option>
                                <option value="high_failure_rate" {{ request('type') === 'high_failure_rate' ? 'selected' : '' }}>Alta Tasa de Fallos</option>
                                <option value="channels_exceeded" {{ request('type') === 'channels_exceeded' ? 'selected' : '' }}>Canales Excedidos</option>
                                <option value="minutes_warning" {{ request('type') === 'minutes_warning' ? 'selected' : '' }}>Aviso de Minutos</option>
                                <option value="security_ip_blocked" {{ request('type') === 'security_ip_blocked' ? 'selected' : '' }}>IP Bloqueada</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
                            <select name="acknowledged" class="dark-select w-full text-sm py-1.5 px-2">
                                <option value="">Todas</option>
                                <option value="0" {{ request('acknowledged') === '0' ? 'selected' : '' }}>Sin Reconocer</option>
                                <option value="1" {{ request('acknowledged') === '1' ? 'selected' : '' }}>Reconocidas</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Desde</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="dark-input w-full text-sm py-1.5 px-2">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 btn-primary text-sm py-1.5">Filtrar</button>
                            <a href="{{ route('alerts.index') }}" class="btn-secondary text-sm py-1.5 px-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabla de Alertas -->
            <div class="dark-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full dark-table">
                        <thead>
                            <tr>
                                <th class="text-left">Fecha/Hora</th>
                                <th class="text-center w-24">Severidad</th>
                                <th class="text-left">Tipo</th>
                                <th class="text-left">Titulo</th>
                                <th class="text-left">Origen</th>
                                <th class="text-center w-28">Estado</th>
                                <th class="text-right w-40">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($alerts as $alert)
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
                                <tr class="{{ !$alert->acknowledged ? 'bg-yellow-500/5' : '' }}">
                                    <td class="text-gray-400 text-sm">
                                        {{ $alert->created_at->format('d/m H:i') }}
                                        <span class="block text-xs text-gray-500">{{ $alert->created_at->diffForHumans() }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $alert->severity === 'critical' ? 'badge-red' : ($alert->severity === 'warning' ? 'badge-yellow' : 'badge-blue') }}">
                                            {{ $severityLabels[$alert->severity] ?? ucfirst($alert->severity) }}
                                        </span>
                                    </td>
                                    <td class="text-sm text-gray-400">{{ $typeLabels[$alert->type] ?? str_replace('_', ' ', $alert->type) }}</td>
                                    <td class="text-sm text-gray-200 font-medium">{{ Str::limit($alert->title, 50) }}</td>
                                    <td class="text-sm text-gray-500">{{ $alert->source_name ?? '-' }}</td>
                                    <td class="text-center">
                                        @if($alert->acknowledged)
                                            <span class="badge badge-green">Reconocida</span>
                                        @else
                                            <span class="badge badge-yellow animate-pulse">Nueva</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('alerts.show', $alert) }}" class="text-blue-400 hover:text-blue-300 text-xs">Ver</a>
                                            @if(!$alert->acknowledged)
                                                <form action="{{ route('alerts.acknowledge', $alert) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-400 hover:text-green-300 text-xs">Reconocer</button>
                                                </form>
                                            @endif
                                            <form action="{{ route('alerts.destroy', $alert) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta alerta?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-300 text-xs">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-12">
                                        <svg class="mx-auto h-12 w-12 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-gray-500">No hay alertas que mostrar</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($alerts->hasPages())
                <div class="px-4 py-3 border-t border-gray-700/50">
                    {{ $alerts->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
