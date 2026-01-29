<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('customers.index') }}" class="text-gray-400 hover:text-white mr-3 p-1 hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <div class="flex items-center">
                        <h2 class="text-2xl font-bold text-white">{{ $customer->name }}</h2>
                        @if($customer->active)
                            <span class="ml-3 badge badge-green">Activo</span>
                        @else
                            <span class="ml-3 badge badge-red">Inactivo</span>
                        @endif
                        @if($customer->traces_enabled)
                            <span class="ml-2 badge badge-blue" title="Trazas SIP habilitadas">
                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                Trazas
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-400 mt-0.5">{{ $customer->company ?? 'Cliente individual' }}</p>
                </div>
            </div>
            <a href="{{ route('customers.edit', $customer) }}" class="btn-primary inline-flex items-center">
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
                <!-- Informacion del Cliente -->
                <div class="dark-card p-5">
                    <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Informacion de Contacto</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Email</dt>
                            <dd class="text-gray-300 mt-1">{{ $customer->email ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Telefono</dt>
                            <dd class="text-gray-300 mt-1">{{ $customer->phone ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">UUID</dt>
                            <dd class="font-mono text-xs text-gray-400 bg-gray-800 p-2 rounded mt-1 break-all">{{ $customer->uuid }}</dd>
                        </div>
                        @if($customer->notes)
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Notas</dt>
                            <dd class="text-sm text-gray-300 bg-yellow-500/10 border border-yellow-500/20 p-2 rounded mt-1">{{ $customer->notes }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Limites y Uso -->
                <div class="dark-card p-5">
                    <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Limites y Uso en Tiempo Real</h3>
                    <dl class="space-y-5">
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider mb-2">Canales Simultaneos</dt>
                            <dd>
                                @php $channelPercent = $customer->max_channels > 0 ? ($customer->activeCalls->count() / $customer->max_channels) * 100 : 0; @endphp
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-300">{{ $customer->activeCalls->count() }} / {{ $customer->max_channels }}</span>
                                    <span class="text-gray-500">{{ number_format($channelPercent, 0) }}%</span>
                                </div>
                                <div class="w-full h-2 bg-gray-700 rounded-full">
                                    <div class="h-full rounded-full transition-all {{ $channelPercent >= 80 ? 'bg-red-500' : ($channelPercent >= 50 ? 'bg-yellow-500' : 'bg-green-500') }}" style="width: {{ min(100, $channelPercent) }}%"></div>
                                </div>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Limite CPS</dt>
                            <dd class="text-gray-300 font-medium">{{ $customer->max_cps }} llamadas/segundo</dd>
                        </div>
                        @if($customer->max_daily_minutes)
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider mb-2">Minutos Diarios</dt>
                            <dd>
                                @php $dailyPercent = ($customer->used_daily_minutes / $customer->max_daily_minutes) * 100; @endphp
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-300">{{ number_format($customer->used_daily_minutes) }} / {{ number_format($customer->max_daily_minutes) }}</span>
                                    <span class="text-gray-500">{{ number_format($dailyPercent, 0) }}%</span>
                                </div>
                                <div class="w-full h-2 bg-gray-700 rounded-full">
                                    <div class="h-full rounded-full {{ $dailyPercent >= 90 ? 'bg-red-500' : ($dailyPercent >= 70 ? 'bg-yellow-500' : 'bg-green-500') }}" style="width: {{ min(100, $dailyPercent) }}%"></div>
                                </div>
                            </dd>
                        </div>
                        @endif
                        @if($customer->max_monthly_minutes)
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider mb-2">Minutos Mensuales</dt>
                            <dd>
                                @php $monthlyPercent = ($customer->used_monthly_minutes / $customer->max_monthly_minutes) * 100; @endphp
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-300">{{ number_format($customer->used_monthly_minutes) }} / {{ number_format($customer->max_monthly_minutes) }}</span>
                                    <span class="text-gray-500">{{ number_format($monthlyPercent, 0) }}%</span>
                                </div>
                                <div class="w-full h-2 bg-gray-700 rounded-full">
                                    <div class="h-full rounded-full {{ $monthlyPercent >= 90 ? 'bg-red-500' : ($monthlyPercent >= 70 ? 'bg-yellow-500' : 'bg-green-500') }}" style="width: {{ min(100, $monthlyPercent) }}%"></div>
                                </div>
                            </dd>
                        </div>
                        @endif
                    </dl>
                    <div class="mt-5 pt-4 border-t border-gray-700/50">
                        <form action="{{ route('customers.reset-minutes', $customer) }}" method="POST" class="flex gap-2">
                            @csrf
                            <select name="type" class="dark-select text-sm flex-1 py-1.5 px-2">
                                <option value="daily">Minutos diarios</option>
                                <option value="monthly">Minutos mensuales</option>
                                <option value="both">Ambos contadores</option>
                            </select>
                            <button type="submit" class="text-sm bg-yellow-500/20 hover:bg-yellow-500/30 text-yellow-400 px-3 py-1.5 rounded-lg transition-colors" onclick="return confirm('¿Resetear contadores de minutos?')">
                                Resetear
                            </button>
                        </form>
                    </div>
                </div>

                <!-- IPs Autorizadas -->
                <div class="dark-card p-5">
                    <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">IPs Autorizadas</h3>
                    <div class="space-y-2 mb-4 max-h-48 overflow-y-auto scrollbar-dark">
                        @forelse($customer->ips as $ip)
                            <div class="flex justify-between items-center p-2 bg-gray-800/50 rounded-lg hover:bg-gray-700/50 transition-colors">
                                <div>
                                    <span class="font-mono text-sm text-gray-200">{{ $ip->ip_address }}</span>
                                    @if($ip->description)
                                        <span class="text-xs text-gray-500 ml-2">{{ $ip->description }}</span>
                                    @endif
                                </div>
                                <form action="{{ route('customers.remove-ip', [$customer, $ip]) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-500 hover:text-red-400 transition-colors" onclick="return confirm('¿Eliminar esta IP?')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm text-center py-4">No hay IPs configuradas</p>
                        @endforelse
                    </div>
                    <form action="{{ route('customers.add-ip', $customer) }}" method="POST" class="space-y-2">
                        @csrf
                        <div class="flex gap-2">
                            <input type="text" name="ip_address" placeholder="IP (ej: 192.168.1.1)" required class="dark-input text-sm flex-1 py-1.5 px-2">
                            <input type="text" name="description" placeholder="Descripcion" class="dark-input text-sm flex-1 py-1.5 px-2">
                        </div>
                        <button type="submit" class="w-full btn-primary text-sm py-2">+ Añadir IP</button>
                    </form>
                </div>
            </div>

            <!-- Llamadas Activas -->
            @if($customer->activeCalls->count() > 0)
            <div class="mt-6 dark-card overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-700/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-white">Llamadas Activas</h3>
                        <p class="text-xs text-gray-500">Conversaciones en curso</p>
                    </div>
                    <span class="badge badge-green animate-pulse">{{ $customer->activeCalls->count() }} en curso</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full dark-table">
                        <thead>
                            <tr>
                                <th class="text-left">Inicio</th>
                                <th class="text-left">Origen</th>
                                <th class="text-left">Destino</th>
                                <th class="text-left">Duracion</th>
                                <th class="text-left">Carrier</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customer->activeCalls as $call)
                                <tr class="bg-green-500/5">
                                    <td class="text-gray-400 text-sm">{{ $call->start_time->format('H:i:s') }}</td>
                                    <td class="font-mono text-sm text-gray-300">{{ $call->caller }}</td>
                                    <td class="font-mono text-sm text-gray-300">{{ $call->callee }}</td>
                                    <td class="text-gray-400 text-sm">{{ $call->start_time->diffForHumans(null, true) }}</td>
                                    <td class="text-gray-400 text-sm">{{ $call->carrier->name ?? '-' }}</td>
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
                        <p class="text-xs text-gray-500">Ultimas llamadas de este cliente</p>
                    </div>
                    <a href="{{ route('cdrs.index', ['customer_id' => $customer->id]) }}" class="text-xs text-blue-400 hover:text-blue-300">Ver todas →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full dark-table">
                        <thead>
                            <tr>
                                <th class="text-left">Fecha/Hora</th>
                                <th class="text-left">Origen</th>
                                <th class="text-left">Destino</th>
                                <th class="text-right">Duracion</th>
                                <th class="text-left">Carrier</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCdrs as $cdr)
                                <tr>
                                    <td class="text-gray-400 text-sm">{{ $cdr->start_time->format('d/m H:i:s') }}</td>
                                    <td class="font-mono text-sm text-gray-300">{{ $cdr->caller }}</td>
                                    <td class="font-mono text-sm text-gray-300">{{ $cdr->callee }}</td>
                                    <td class="text-right text-gray-400 text-sm">{{ gmdate('i:s', $cdr->duration) }}</td>
                                    <td class="text-gray-400 text-sm">{{ $cdr->carrier->name ?? '-' }}</td>
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
                                    <td colspan="6" class="text-center py-8 text-gray-500">No hay llamadas registradas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
