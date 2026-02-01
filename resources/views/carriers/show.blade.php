<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('carriers.index') }}" class="text-gray-400 hover:text-white mr-3 p-1 hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <div class="flex items-center">
                        <h2 class="text-2xl font-bold text-white">{{ $carrier->name }}</h2>
                        @php
                            $stateConfig = [
                                'active' => ['class' => 'badge-green', 'label' => 'Activo'],
                                'probing' => ['class' => 'badge-yellow', 'label' => 'Probando'],
                                'inactive' => ['class' => 'badge-gray', 'label' => 'Inactivo'],
                                'disabled' => ['class' => 'badge-red', 'label' => 'Deshabilitado'],
                            ];
                            $state = $stateConfig[$carrier->state] ?? ['class' => 'badge-gray', 'label' => ucfirst($carrier->state)];
                        @endphp
                        <span class="ml-3 badge {{ $state['class'] }}">{{ $state['label'] }}</span>
                    </div>
                    <p class="text-sm text-gray-400 mt-0.5 font-mono">{{ $carrier->host }}:{{ $carrier->port }} ({{ strtoupper($carrier->transport) }})</p>
                </div>
            </div>
            <a href="{{ route('carriers.edit', $carrier) }}" class="btn-primary inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Editar
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400">{{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Informacion de Conexion -->
                <div class="dark-card p-5">
                    <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Conexion y Routing</h3>
                    <dl class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wider">Prioridad</dt>
                                <dd class="text-2xl font-bold text-blue-400 mt-1">{{ $carrier->priority }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wider">Peso</dt>
                                <dd class="text-2xl font-bold text-purple-400 mt-1">{{ $carrier->weight }}</dd>
                            </div>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Monitoreo OPTIONS</dt>
                            <dd class="mt-1">
                                @if($carrier->probing_enabled)
                                    <span class="inline-flex items-center text-sm text-green-400">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                        Habilitado
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-sm text-yellow-400">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                        Deshabilitado (Manual)
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Ultimo OPTIONS</dt>
                            <dd class="text-sm text-gray-300 mt-1">{{ $carrier->last_options_time ? $carrier->last_options_time->diffForHumans() : 'Nunca' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">UUID</dt>
                            <dd class="font-mono text-xs text-gray-400 bg-gray-800 p-2 rounded mt-1 break-all">{{ $carrier->uuid }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Limites y Uso -->
                <div class="dark-card p-5">
                    <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Capacidad en Tiempo Real</h3>
                    <dl class="space-y-5">
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider mb-2">Canales en Uso</dt>
                            <dd>
                                @php $channelPercent = $carrier->max_channels > 0 ? ($carrier->activeCalls->count() / $carrier->max_channels) * 100 : 0; @endphp
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-300">{{ $carrier->activeCalls->count() }} / {{ $carrier->max_channels }}</span>
                                    <span class="text-gray-500">{{ number_format($channelPercent, 0) }}%</span>
                                </div>
                                <div class="w-full h-2 bg-gray-700 rounded-full">
                                    <div class="h-full rounded-full transition-all {{ $channelPercent >= 80 ? 'bg-red-500' : ($channelPercent >= 50 ? 'bg-yellow-500' : 'bg-green-500') }}" style="width: {{ min(100, $channelPercent) }}%"></div>
                                </div>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">CPS Maximo</dt>
                            <dd class="text-gray-300 font-medium">{{ $carrier->max_cps }} llamadas/segundo</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4 pt-3 border-t border-gray-700/50">
                            <div class="text-center p-3 bg-gray-800/50 rounded-lg">
                                <div class="text-xl font-bold text-green-400">{{ number_format($carrier->daily_calls) }}</div>
                                <div class="text-xs text-gray-500">Llamadas hoy</div>
                            </div>
                            <div class="text-center p-3 bg-gray-800/50 rounded-lg">
                                <div class="text-xl font-bold text-blue-400">{{ number_format($carrier->daily_minutes) }}</div>
                                <div class="text-xs text-gray-500">Minutos hoy</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-red-500/10 rounded-lg">
                                <div class="text-xl font-bold text-red-400">{{ number_format($carrier->daily_failed) }}</div>
                                <div class="text-xs text-gray-500">Fallidas hoy</div>
                            </div>
                            <div class="text-center p-3 bg-yellow-500/10 rounded-lg">
                                <div class="text-xl font-bold text-yellow-400">{{ number_format($carrier->failover_count) }}</div>
                                <div class="text-xs text-gray-500">Failovers</div>
                            </div>
                        </div>
                    </dl>
                </div>

                <!-- Manipulacion -->
                <div class="dark-card p-5">
                    <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Manipulacion de Llamadas</h3>
                    <dl class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wider">Prefijo Tecnico</dt>
                                <dd class="font-mono text-gray-300 mt-1">{{ $carrier->tech_prefix ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wider">Strip Digits</dt>
                                <dd class="font-mono text-gray-300 mt-1">{{ $carrier->strip_digits }}</dd>
                            </div>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Codecs</dt>
                            <dd class="font-mono text-sm text-gray-300 mt-1">{{ $carrier->codecs ?: 'Por defecto' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Prefijos Permitidos</dt>
                            <dd class="font-mono text-xs text-gray-400 bg-gray-800 p-2 rounded mt-1 max-h-16 overflow-y-auto scrollbar-dark whitespace-pre-line">{{ $carrier->prefix_filter ?: 'Todos' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Prefijos Denegados</dt>
                            <dd class="font-mono text-xs text-gray-400 bg-gray-800 p-2 rounded mt-1 max-h-16 overflow-y-auto scrollbar-dark whitespace-pre-line">{{ $carrier->prefix_deny ?: 'Ninguno' }}</dd>
                        </div>
                    </dl>
                    @if($carrier->notes)
                    <div class="mt-4 pt-4 border-t border-gray-700/50">
                        <dt class="text-xs text-gray-500 uppercase tracking-wider">Notas</dt>
                        <dd class="text-sm text-gray-300 bg-yellow-500/10 border border-yellow-500/20 p-2 rounded mt-1">{{ $carrier->notes }}</dd>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Llamadas Activas -->
            @if($carrier->activeCalls->count() > 0)
            <div class="mt-6 dark-card overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-700/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-white">Llamadas Activas en este Carrier</h3>
                        <p class="text-xs text-gray-500">Conversaciones enrutadas por este proveedor</p>
                    </div>
                    <span class="badge badge-green animate-pulse">{{ $carrier->activeCalls->count() }} en curso</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full dark-table">
                        <thead>
                            <tr>
                                <th class="text-left">Inicio</th>
                                <th class="text-left">Cliente</th>
                                <th class="text-left">Origen</th>
                                <th class="text-left">Destino</th>
                                <th class="text-left">Duracion</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($carrier->activeCalls as $call)
                                <tr class="bg-green-500/5">
                                    <td class="text-gray-400 text-sm">{{ $call->start_time->format('H:i:s') }}</td>
                                    <td class="text-gray-300 text-sm">{{ $call->customer->name ?? '-' }}</td>
                                    <td class="font-mono text-sm text-gray-300">{{ $call->caller }}</td>
                                    <td class="font-mono text-sm text-gray-300">{{ $call->callee }}</td>
                                    <td class="text-gray-400 text-sm">{{ $call->start_time->diffForHumans(null, true) }}</td>
                                    <td class="text-center">
                                        @if($call->answered)
                                            <span class="badge badge-green">Contestada</span>
                                        @else
                                            <span class="badge badge-yellow">Sonando</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Llamadas Recientes -->
            <div class="mt-6 dark-card overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-700/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-white">Llamadas Recientes</h3>
                        <p class="text-xs text-gray-500">Ultimas llamadas procesadas por este carrier</p>
                    </div>
                    <a href="{{ route('cdrs.index', ['carrier_id' => $carrier->id]) }}" class="text-xs text-blue-400 hover:text-blue-300">Ver todas →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full dark-table">
                        <thead>
                            <tr>
                                <th class="text-left">Fecha/Hora</th>
                                <th class="text-left">Cliente</th>
                                <th class="text-left">Origen</th>
                                <th class="text-left">Destino</th>
                                <th class="text-right" title="Tiempo facturable">Billable</th>
                                <th class="text-right" title="Tiempo de timbrado">Ring</th>
                                <th class="text-right" title="Post Dial Delay">PDD</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCdrs as $cdr)
                                <tr>
                                    <td class="text-gray-400 text-sm">{{ $cdr->start_time->format('d/m H:i:s') }}</td>
                                    <td class="text-gray-300 text-sm">{{ $cdr->customer->name ?? '-' }}</td>
                                    <td class="font-mono text-sm text-gray-300">{{ $cdr->caller }}</td>
                                    <td class="font-mono text-sm text-gray-300">{{ $cdr->callee }}</td>
                                    <td class="text-right text-green-400 text-sm">{{ gmdate('i:s', $cdr->billable_duration) }}</td>
                                    <td class="text-right text-yellow-400 text-xs">{{ $cdr->ring_time ? $cdr->ring_time . 's' : '-' }}</td>
                                    <td class="text-right text-purple-400 text-xs">{{ $cdr->pdd ? $cdr->pdd . 'ms' : '-' }}</td>
                                    <td class="text-center">
                                        @if($cdr->answer_time)
                                            <span class="badge badge-green">{{ $cdr->sip_code }}</span>
                                        @else
                                            <span class="badge badge-red">{{ $cdr->sip_code }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-8 text-gray-500">No hay llamadas registradas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
