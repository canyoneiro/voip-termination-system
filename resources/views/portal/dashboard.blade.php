<x-portal.layouts.app>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Welcome Message -->
            <div class="dark-card p-6 mb-6">
                <h3 class="text-lg font-semibold text-slate-800">Bienvenido, {{ $customer->name }}</h3>
                <p class="text-slate-600 mt-1">{{ $customer->company ?? 'Cliente VoIP' }}</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Active Calls -->
                <div class="stat-card blue">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Llamadas Activas</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['active_calls'] }}</p>
                    <p class="text-xs text-slate-500 mt-1">de {{ $customer->max_channels }} canales</p>
                </div>

                <!-- Today's Calls -->
                <div class="stat-card green">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Llamadas Hoy</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['today_calls']) }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ number_format($stats['today_answered']) }} contestadas</p>
                </div>

                <!-- Today's Minutes -->
                <div class="stat-card yellow">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Minutos Hoy</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['today_minutes']) }}</p>
                    @if($customer->max_daily_minutes)
                        <p class="text-xs text-slate-500 mt-1">de {{ number_format($customer->max_daily_minutes) }} max</p>
                    @else
                        <p class="text-xs text-slate-500 mt-1">sin limite</p>
                    @endif
                </div>

                <!-- ASR -->
                <div class="stat-card purple">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">ASR Hoy</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['asr'] }}%</p>
                    <p class="text-xs text-slate-500 mt-1">tasa de contestacion</p>
                </div>
            </div>

            <!-- Usage Bars -->
            @if($customer->max_daily_minutes || $customer->max_monthly_minutes)
            <div class="dark-card p-6 mb-6">
                <h4 class="font-semibold text-slate-800 mb-4">Uso de Minutos</h4>

                @if($customer->max_daily_minutes)
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-600">Diario</span>
                        <span class="text-slate-800 font-medium">
                            {{ number_format($customer->used_daily_minutes) }} / {{ number_format($customer->max_daily_minutes) }} minutos
                        </span>
                    </div>
                    @php
                        $dailyPercent = $customer->max_daily_minutes > 0
                            ? min(100, ($customer->used_daily_minutes / $customer->max_daily_minutes) * 100)
                            : 0;
                        $dailyColor = $dailyPercent >= 90 ? 'bg-red-500' : ($dailyPercent >= 70 ? 'bg-yellow-500' : 'bg-green-500');
                    @endphp
                    <div class="w-full bg-slate-200 rounded-full h-2.5">
                        <div class="{{ $dailyColor }} h-2.5 rounded-full transition-all" style="width: {{ $dailyPercent }}%"></div>
                    </div>
                </div>
                @endif

                @if($customer->max_monthly_minutes)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-600">Mensual</span>
                        <span class="text-slate-800 font-medium">
                            {{ number_format($customer->used_monthly_minutes) }} / {{ number_format($customer->max_monthly_minutes) }} minutos
                        </span>
                    </div>
                    @php
                        $monthlyPercent = $customer->max_monthly_minutes > 0
                            ? min(100, ($customer->used_monthly_minutes / $customer->max_monthly_minutes) * 100)
                            : 0;
                        $monthlyColor = $monthlyPercent >= 90 ? 'bg-red-500' : ($monthlyPercent >= 70 ? 'bg-yellow-500' : 'bg-green-500');
                    @endphp
                    <div class="w-full bg-slate-200 rounded-full h-2.5">
                        <div class="{{ $monthlyColor }} h-2.5 rounded-full transition-all" style="width: {{ $monthlyPercent }}%"></div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Calls -->
                <div class="dark-card">
                    <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                        <h4 class="font-semibold text-slate-800">Llamadas Recientes</h4>
                        <a href="{{ route('portal.cdrs.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Ver todas</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="dark-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Duracion</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentCalls as $cdr)
                                    <tr>
                                        <td class="mono text-xs">{{ $cdr->start_time->format('d/m H:i') }}</td>
                                        <td class="mono">{{ Str::limit($cdr->caller, 15) }}</td>
                                        <td class="mono">{{ Str::limit($cdr->callee, 15) }}</td>
                                        <td>{{ gmdate('i:s', $cdr->duration) }}</td>
                                        <td>
                                            @if($cdr->sip_code == 200)
                                                <span class="badge badge-green">OK</span>
                                            @else
                                                <span class="badge badge-red">{{ $cdr->sip_code }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-slate-500">No hay llamadas recientes</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Authorized IPs -->
                <div class="dark-card">
                    <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                        <h4 class="font-semibold text-slate-800">IPs Autorizadas</h4>
                        <a href="{{ route('portal.ips.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Gestionar</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="dark-table">
                            <thead>
                                <tr>
                                    <th>IP</th>
                                    <th>Descripcion</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($authorizedIps as $ip)
                                    <tr>
                                        <td class="mono">{{ $ip->ip_address }}</td>
                                        <td>{{ $ip->description ?? '-' }}</td>
                                        <td>
                                            @if($ip->active)
                                                <span class="badge badge-green">Activa</span>
                                            @else
                                                <span class="badge badge-gray">Inactiva</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-slate-500">No hay IPs autorizadas</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-portal.layouts.app>
