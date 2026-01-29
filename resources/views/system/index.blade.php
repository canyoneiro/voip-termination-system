<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-800">Sistema</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('output'))
                <div class="mb-4 p-4 bg-slate-100 border border-slate-200 rounded-lg">
                    <pre class="text-xs text-slate-700 overflow-x-auto whitespace-pre-wrap">{{ session('output') }}</pre>
                </div>
            @endif

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="stat-card green">
                    <div class="text-2xl font-bold text-slate-800">{{ $status['voip']['active_calls'] }}</div>
                    <div class="text-sm text-slate-500">Llamadas Activas</div>
                </div>
                <div class="stat-card blue">
                    <div class="text-2xl font-bold text-slate-800">{{ $status['voip']['cdrs_today'] }}</div>
                    <div class="text-sm text-slate-500">CDRs Hoy</div>
                </div>
                <div class="stat-card purple">
                    <div class="text-2xl font-bold text-slate-800">{{ $status['voip']['customers_active'] }}</div>
                    <div class="text-sm text-slate-500">Clientes Activos</div>
                </div>
                <div class="stat-card blue">
                    <div class="text-2xl font-bold text-slate-800">{{ $status['voip']['carriers_active'] }}</div>
                    <div class="text-sm text-slate-500">Carriers Activos</div>
                </div>
                <div class="stat-card {{ $status['voip']['alerts_unacked'] > 0 ? 'red' : 'green' }}">
                    <div class="text-2xl font-bold text-slate-800">{{ $status['voip']['alerts_unacked'] }}</div>
                    <div class="text-sm text-slate-500">Alertas Pendientes</div>
                </div>
            </div>

            <!-- Services Status -->
            <div class="dark-card mb-6">
                <div class="px-5 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-800">Estado de Servicios</h3>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                        @foreach($status['services'] as $service => $info)
                            <div class="flex flex-col items-center p-4 rounded-lg {{ $info['running'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                                <div class="w-3 h-3 rounded-full mb-2 {{ $info['running'] ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                <span class="text-sm font-medium {{ $info['running'] ? 'text-green-700' : 'text-red-700' }}">{{ $service }}</span>
                                <span class="text-xs text-slate-500">{{ $info['status'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Service Actions -->
                <div class="dark-card">
                    <div class="px-5 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-800">Acciones de Servicios</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <span class="text-slate-700 font-medium">Kamailio</span>
                            <div class="flex gap-2">
                                <form action="{{ route('system.action') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="action" value="restart">
                                    <input type="hidden" name="target" value="kamailio">
                                    <button type="submit" class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-sm rounded-lg font-medium" onclick="return confirm('Reiniciar Kamailio?')">Reiniciar</button>
                                </form>
                                <form action="{{ route('system.kamailio') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="action" value="reload_dispatcher">
                                    <button type="submit" class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-sm rounded-lg font-medium">Reload Dispatcher</button>
                                </form>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <span class="text-slate-700 font-medium">MySQL</span>
                            <form action="{{ route('system.action') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="action" value="restart">
                                <input type="hidden" name="target" value="mysql">
                                <button type="submit" class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-sm rounded-lg font-medium" onclick="return confirm('Reiniciar MySQL?')">Reiniciar</button>
                            </form>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <span class="text-slate-700 font-medium">Nginx</span>
                            <form action="{{ route('system.action') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="action" value="restart">
                                <input type="hidden" name="target" value="nginx">
                                <button type="submit" class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-sm rounded-lg font-medium" onclick="return confirm('Reiniciar Nginx?')">Reiniciar</button>
                            </form>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <span class="text-slate-700 font-medium">Redis</span>
                            <form action="{{ route('system.action') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="action" value="restart">
                                <input type="hidden" name="target" value="redis">
                                <button type="submit" class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-sm rounded-lg font-medium" onclick="return confirm('Reiniciar Redis?')">Reiniciar</button>
                            </form>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <span class="text-slate-700 font-medium">PHP-FPM</span>
                            <form action="{{ route('system.action') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="action" value="restart">
                                <input type="hidden" name="target" value="php8.3-fpm">
                                <button type="submit" class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-sm rounded-lg font-medium" onclick="return confirm('Reiniciar PHP-FPM?')">Reiniciar</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Kamailio Actions -->
                <div class="dark-card">
                    <div class="px-5 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-800">Acciones Kamailio</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <span class="text-slate-700 font-medium">Dispatcher Probing</span>
                            <div class="flex gap-2">
                                <form action="{{ route('system.kamailio') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="action" value="ping_active_on">
                                    <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-sm rounded-lg font-medium">Activar</button>
                                </form>
                                <form action="{{ route('system.kamailio') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="action" value="ping_active_off">
                                    <button type="submit" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-sm rounded-lg font-medium">Desactivar</button>
                                </form>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <span class="text-slate-700 font-medium">Recargar Permisos</span>
                            <form action="{{ route('system.kamailio') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="action" value="reload_permissions">
                                <button type="submit" class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-sm rounded-lg font-medium">Recargar</button>
                            </form>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <span class="text-slate-700 font-medium">Limpiar Cache</span>
                            <div class="flex gap-2">
                                <form action="{{ route('system.clear-cache') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="type" value="laravel">
                                    <button type="submit" class="px-3 py-1.5 bg-purple-500 hover:bg-purple-600 text-white text-sm rounded-lg font-medium">Laravel</button>
                                </form>
                                <form action="{{ route('system.clear-cache') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="type" value="redis">
                                    <button type="submit" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-sm rounded-lg font-medium" onclick="return confirm('Esto borrara todos los datos de Redis!')">Redis</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Resources -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Memory -->
                <div class="dark-card">
                    <div class="px-5 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-800">Memoria</h3>
                    </div>
                    <div class="p-5">
                        <div class="mb-2 flex justify-between text-sm">
                            <span class="text-slate-500">{{ $status['system']['memory']['used'] }} / {{ $status['system']['memory']['total'] }}</span>
                            <span class="text-slate-700 font-medium">{{ $status['system']['memory']['percent_used'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-3">
                            <div class="h-3 rounded-full {{ $status['system']['memory']['percent_used'] > 90 ? 'bg-red-500' : ($status['system']['memory']['percent_used'] > 70 ? 'bg-yellow-500' : 'bg-green-500') }}" style="width: {{ $status['system']['memory']['percent_used'] }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Disk -->
                <div class="dark-card">
                    <div class="px-5 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-800">Disco</h3>
                    </div>
                    <div class="p-5">
                        <div class="mb-2 flex justify-between text-sm">
                            <span class="text-slate-500">{{ $status['system']['disk']['used'] }} / {{ $status['system']['disk']['total'] }}</span>
                            <span class="text-slate-700 font-medium">{{ $status['system']['disk']['percent_used'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-3">
                            <div class="h-3 rounded-full {{ $status['system']['disk']['percent_used'] > 90 ? 'bg-red-500' : ($status['system']['disk']['percent_used'] > 70 ? 'bg-yellow-500' : 'bg-green-500') }}" style="width: {{ $status['system']['disk']['percent_used'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
