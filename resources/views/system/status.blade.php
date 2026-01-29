<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-800">Estado del Sistema</h2>
            <a href="{{ route('system.status.json') }}" target="_blank" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm rounded-lg font-medium">Ver JSON</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Services Grid -->
            <div class="dark-card mb-6">
                <div class="px-5 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-800">Servicios del Sistema</h3>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                        @foreach($status['services'] as $service => $info)
                            <div class="flex flex-col items-center p-4 rounded-lg {{ $info['running'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                                <div class="w-4 h-4 rounded-full mb-2 {{ $info['running'] ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}"></div>
                                <span class="text-sm font-medium {{ $info['running'] ? 'text-green-700' : 'text-red-700' }}">{{ $service }}</span>
                                <span class="text-xs text-slate-500 mt-1">{{ $info['status'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- System Info -->
                <div class="dark-card">
                    <div class="px-5 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-800">Informacion del Sistema</h3>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-slate-500">Hostname</span>
                            <span class="text-slate-800 font-mono">{{ $status['system']['hostname'] }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-slate-500">Uptime</span>
                            <span class="text-slate-800">{{ $status['system']['uptime'] }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-slate-500">CPU Cores</span>
                            <span class="text-slate-800">{{ $status['system']['cpu_cores'] }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-slate-500">Load Average</span>
                            <span class="text-slate-800 font-mono">
                                {{ number_format($status['system']['load'][0], 2) }} /
                                {{ number_format($status['system']['load'][1], 2) }} /
                                {{ number_format($status['system']['load'][2], 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Database Status -->
                <div class="dark-card">
                    <div class="px-5 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-800">Base de Datos (MySQL)</h3>
                    </div>
                    <div class="p-5 space-y-4">
                        @if($status['database']['connected'] ?? false)
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                <span class="text-green-700 font-medium">Conectado</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                <span class="text-slate-500">Conexiones Totales</span>
                                <span class="text-slate-800">{{ number_format($status['database']['Connections'] ?? 0) }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                <span class="text-slate-500">Hilos Conectados</span>
                                <span class="text-slate-800">{{ $status['database']['Threads_connected'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                <span class="text-slate-500">Queries Totales</span>
                                <span class="text-slate-800">{{ number_format($status['database']['Queries'] ?? 0) }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                <span class="text-slate-500">Uptime</span>
                                <span class="text-slate-800">{{ gmdate('H:i:s', $status['database']['Uptime'] ?? 0) }}</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                <span class="text-red-700">Error: {{ $status['database']['error'] ?? 'No conectado' }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Redis Status -->
                <div class="dark-card">
                    <div class="px-5 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-800">Redis</h3>
                    </div>
                    <div class="p-5 space-y-4">
                        @if($status['redis']['connected'] ?? false)
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                <span class="text-green-700 font-medium">Conectado</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                <span class="text-slate-500">Version</span>
                                <span class="text-slate-800 font-mono">{{ $status['redis']['version'] }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                <span class="text-slate-500">Memoria Usada</span>
                                <span class="text-slate-800">{{ $status['redis']['used_memory'] }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                <span class="text-slate-500">Clientes Conectados</span>
                                <span class="text-slate-800">{{ $status['redis']['connected_clients'] }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                <span class="text-slate-500">Conexiones Totales</span>
                                <span class="text-slate-800">{{ number_format($status['redis']['total_connections']) }}</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                <span class="text-red-700">Error: {{ $status['redis']['error'] ?? 'No conectado' }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Memory & Disk -->
                <div class="dark-card">
                    <div class="px-5 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-800">Recursos del Sistema</h3>
                    </div>
                    <div class="p-5 space-y-6">
                        <!-- Memory -->
                        <div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-slate-500">Memoria RAM</span>
                                <span class="text-slate-800">{{ $status['system']['memory']['used'] }} / {{ $status['system']['memory']['total'] }}</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-4">
                                <div class="h-4 rounded-full flex items-center justify-center text-xs text-white font-medium {{ $status['system']['memory']['percent_used'] > 90 ? 'bg-red-500' : ($status['system']['memory']['percent_used'] > 70 ? 'bg-yellow-500' : 'bg-green-500') }}" style="width: {{ $status['system']['memory']['percent_used'] }}%">
                                    {{ $status['system']['memory']['percent_used'] }}%
                                </div>
                            </div>
                        </div>
                        <!-- Disk -->
                        <div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-slate-500">Disco</span>
                                <span class="text-slate-800">{{ $status['system']['disk']['used'] }} / {{ $status['system']['disk']['total'] }}</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-4">
                                <div class="h-4 rounded-full flex items-center justify-center text-xs text-white font-medium {{ $status['system']['disk']['percent_used'] > 90 ? 'bg-red-500' : ($status['system']['disk']['percent_used'] > 70 ? 'bg-yellow-500' : 'bg-green-500') }}" style="width: {{ $status['system']['disk']['percent_used'] }}%">
                                    {{ $status['system']['disk']['percent_used'] }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kamailio Status -->
            <div class="dark-card mb-6">
                <div class="px-5 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-800">Kamailio</h3>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Stats -->
                        <div>
                            <h4 class="text-sm font-medium text-slate-600 mb-3">Estadisticas</h4>
                            <div class="bg-slate-900 rounded-lg p-4 font-mono text-xs overflow-x-auto max-h-64 overflow-y-auto">
                                <pre class="text-green-400 whitespace-pre-wrap">{{ $status['kamailio']['stats'] ?: 'No disponible' }}</pre>
                            </div>
                        </div>
                        <!-- Dispatcher -->
                        <div>
                            <h4 class="text-sm font-medium text-slate-600 mb-3">Dispatcher (Carriers)</h4>
                            <div class="bg-slate-900 rounded-lg p-4 font-mono text-xs overflow-x-auto max-h-64 overflow-y-auto">
                                <pre class="text-green-400 whitespace-pre-wrap">{{ $status['kamailio']['dispatcher'] ?: 'No disponible' }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VoIP Stats -->
            <div class="dark-card">
                <div class="px-5 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-800">Estadisticas VoIP</h3>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="text-center p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="text-3xl font-bold text-green-600">{{ $status['voip']['active_calls'] }}</div>
                            <div class="text-sm text-slate-600 mt-1">Llamadas Activas</div>
                        </div>
                        <div class="text-center p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="text-3xl font-bold text-blue-600">{{ number_format($status['voip']['cdrs_today']) }}</div>
                            <div class="text-sm text-slate-600 mt-1">CDRs Hoy</div>
                        </div>
                        <div class="text-center p-4 bg-purple-50 border border-purple-200 rounded-lg">
                            <div class="text-3xl font-bold text-purple-600">{{ $status['voip']['customers_active'] }}</div>
                            <div class="text-sm text-slate-600 mt-1">Clientes Activos</div>
                        </div>
                        <div class="text-center p-4 bg-teal-50 border border-teal-200 rounded-lg">
                            <div class="text-3xl font-bold text-teal-600">{{ $status['voip']['carriers_active'] }}</div>
                            <div class="text-sm text-slate-600 mt-1">Carriers Activos</div>
                        </div>
                        <div class="text-center p-4 {{ $status['voip']['alerts_unacked'] > 0 ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200' }} border rounded-lg">
                            <div class="text-3xl font-bold {{ $status['voip']['alerts_unacked'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $status['voip']['alerts_unacked'] }}</div>
                            <div class="text-sm text-slate-600 mt-1">Alertas Pendientes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh every 30 seconds
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</x-app-layout>
