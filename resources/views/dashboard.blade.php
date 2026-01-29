<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Panel de Control</h2>
                <p class="text-sm text-slate-500 mt-0.5">Monitoreo en tiempo real de tu plataforma VoIP</p>
            </div>
            <div class="text-right">
                <div class="text-xs text-slate-400">Ultima actualizacion</div>
                <div class="text-sm font-medium text-slate-600">{{ now()->format('H:i:s') }}</div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Metricas Principales -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Llamadas Activas -->
                <div class="stat-card blue">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Llamadas Activas</p>
                            <p class="text-3xl font-bold text-slate-800 mt-1">{{ $activeCallsCount }}</p>
                            <p class="text-xs text-slate-400 mt-1">En curso ahora mismo</p>
                        </div>
                        <div class="p-2.5 bg-blue-100 rounded-xl">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- CPS -->
                <div class="stat-card green">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">CPS Actual</p>
                            <p class="text-3xl font-bold text-slate-800 mt-1">{{ $cps }}</p>
                            <p class="text-xs text-slate-400 mt-1">Llamadas por segundo</p>
                        </div>
                        <div class="p-2.5 bg-green-100 rounded-xl">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- ASR -->
                <div class="stat-card {{ $asr >= 50 ? 'green' : ($asr >= 30 ? 'yellow' : 'red') }}">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">ASR Hoy</p>
                            <p class="text-3xl font-bold text-slate-800 mt-1">{{ $asr }}%</p>
                            <p class="text-xs text-slate-400 mt-1">Tasa de respuesta</p>
                        </div>
                        <div class="p-2.5 {{ $asr >= 50 ? 'bg-green-100' : ($asr >= 30 ? 'bg-yellow-100' : 'bg-red-100') }} rounded-xl">
                            <svg class="w-6 h-6 {{ $asr >= 50 ? 'text-green-600' : ($asr >= 30 ? 'text-yellow-600' : 'text-red-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- ACD -->
                <div class="stat-card purple">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">ACD Hoy</p>
                            <p class="text-3xl font-bold text-slate-800 mt-1">{{ gmdate('i:s', $acd) }}</p>
                            <p class="text-xs text-slate-400 mt-1">Duracion promedio</p>
                        </div>
                        <div class="p-2.5 bg-purple-100 rounded-xl">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid Principal -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Estado de Carriers -->
                <div class="dark-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-800">Estado de Carriers</h3>
                            <p class="text-xs text-slate-500">Proveedores de terminacion</p>
                        </div>
                        <a href="{{ route('carriers.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">Ver todos →</a>
                    </div>
                    <div class="p-4 space-y-3 max-h-80 overflow-y-auto scrollbar-dark">
                        @forelse($carriers as $carrier)
                            @php
                                $pct = $carrier->max_channels > 0 ? ($carrier->active_calls_count / $carrier->max_channels) * 100 : 0;
                                $stateColors = [
                                    'active' => 'bg-green-500',
                                    'probing' => 'bg-yellow-500',
                                    'inactive' => 'bg-slate-400',
                                    'disabled' => 'bg-red-500'
                                ];
                            @endphp
                            <a href="{{ route('carriers.show', $carrier) }}" class="block p-4 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 transition-all hover:shadow-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center space-x-2">
                                        <span class="w-2.5 h-2.5 rounded-full {{ $stateColors[$carrier->state] ?? 'bg-slate-400' }}"></span>
                                        <span class="text-sm font-semibold text-slate-700">{{ $carrier->name }}</span>
                                    </div>
                                    <span class="text-xs font-medium text-slate-600 bg-white px-2 py-0.5 rounded-full border">{{ $carrier->active_calls_count }}/{{ $carrier->max_channels }}</span>
                                </div>
                                <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all {{ $pct >= 80 ? 'bg-red-500' : ($pct >= 50 ? 'bg-yellow-500' : 'bg-green-500') }}" style="width: {{ min(100, $pct) }}%"></div>
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-8">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"></path></svg>
                                <p class="text-slate-400 text-sm">No hay carriers configurados</p>
                                <a href="{{ route('carriers.create') }}" class="text-blue-600 text-sm mt-1 inline-block font-medium">Agregar carrier</a>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Llamadas Activas -->
                <div class="dark-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-800">Llamadas Activas</h3>
                            <p class="text-xs text-slate-500">Conversaciones en curso</p>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $activeCallsCount > 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500' }}">{{ $activeCallsCount }}</span>
                    </div>
                    <div class="overflow-x-auto max-h-80 scrollbar-dark">
                        <table class="w-full">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Cliente</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Destino</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Tiempo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($activeCalls->take(8) as $call)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 text-sm text-slate-700">{{ Str::limit($call->customer->name ?? '-', 12) }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600 font-mono text-xs">{{ $call->callee }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="text-xs font-medium {{ $call->answered ? 'text-green-600' : 'text-yellow-600' }}">
                                                {{ $call->start_time->diffForHumans(null, true) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-slate-400 text-sm">
                                            No hay llamadas activas
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Alertas Recientes -->
                <div class="dark-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-800">Alertas Recientes</h3>
                            <p class="text-xs text-slate-500">Eventos que requieren atencion</p>
                        </div>
                        @php $unackCount = $alerts->where('acknowledged', false)->count(); @endphp
                        @if($unackCount > 0)
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-red-100 text-red-700 animate-pulse">{{ $unackCount }}</span>
                        @endif
                    </div>
                    <div class="p-4 space-y-3 max-h-80 overflow-y-auto scrollbar-dark">
                        @forelse($alerts as $alert)
                            @php
                                $severityStyles = [
                                    'critical' => 'border-l-red-500 bg-red-50',
                                    'warning' => 'border-l-yellow-500 bg-yellow-50',
                                    'info' => 'border-l-blue-500 bg-blue-50'
                                ];
                                $severityLabels = ['critical' => 'Critico', 'warning' => 'Aviso', 'info' => 'Info'];
                            @endphp
                            <a href="{{ route('alerts.show', $alert) }}" class="block p-4 rounded-xl border-l-4 {{ $severityStyles[$alert->severity] ?? 'border-l-slate-400 bg-slate-50' }} hover:shadow-sm transition-all">
                                <div class="flex items-start justify-between">
                                    <span class="text-xs font-bold uppercase {{ $alert->severity === 'critical' ? 'text-red-600' : ($alert->severity === 'warning' ? 'text-yellow-700' : 'text-blue-600') }}">
                                        {{ $severityLabels[$alert->severity] ?? ucfirst($alert->severity) }}
                                    </span>
                                    <span class="text-xs text-slate-400">{{ $alert->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-slate-700 mt-1 font-medium">{{ Str::limit($alert->title, 45) }}</p>
                            </a>
                        @empty
                            <div class="text-center py-8">
                                <svg class="w-10 h-10 mx-auto text-green-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-slate-400 text-sm">Sin alertas pendientes</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="px-5 py-3 border-t border-slate-100 bg-slate-50">
                        <a href="{{ route('alerts.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">Ver todas las alertas →</a>
                    </div>
                </div>
            </div>

            <!-- Top Clientes -->
            @if($topCustomers->count() > 0)
            <div class="mt-6 dark-card overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-base font-semibold text-slate-800">Top Clientes por Llamadas Activas</h3>
                    <p class="text-xs text-slate-500">Clientes con mayor actividad en este momento</p>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        @foreach($topCustomers as $customer)
                            <a href="{{ route('customers.show', $customer) }}" class="text-center p-5 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 hover:border-blue-300 transition-all hover:shadow-md">
                                <div class="text-2xl font-bold text-blue-600">{{ $customer->active_calls_count }}</div>
                                <div class="text-xs text-slate-600 mt-1 font-medium">{{ Str::limit($customer->name, 18) }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
