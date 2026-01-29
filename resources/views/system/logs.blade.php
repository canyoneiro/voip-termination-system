<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-800">Visor de Logs</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="dark-card mb-6">
                <div class="p-4">
                    <form method="GET" action="{{ route('system.logs') }}" class="flex flex-wrap gap-4 items-end">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Tipo de Log</label>
                            <select name="type" class="form-select border-slate-300 rounded-lg text-slate-700 focus:border-blue-500 focus:ring-blue-500">
                                @foreach($logFiles as $logType)
                                    <option value="{{ $logType }}" {{ $logType === request('type', 'kamailio') ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $logType)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Lineas</label>
                            <select name="lines" class="form-select border-slate-300 rounded-lg text-slate-700 focus:border-blue-500 focus:ring-blue-500">
                                @foreach([50, 100, 200, 500, 1000] as $num)
                                    <option value="{{ $num }}" {{ $num == $lines ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm text-slate-600 mb-1">Filtrar</label>
                            <input type="text" name="filter" value="{{ $filter }}" placeholder="Buscar en logs..." class="form-input border-slate-300 rounded-lg text-slate-700 w-full focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <button type="submit" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium">Buscar</button>
                        </div>
                        <div>
                            <a href="{{ route('system.logs') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg font-medium inline-block">Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Log Content -->
            <div class="dark-card">
                <div class="px-5 py-4 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-slate-800">
                        {{ ucfirst(str_replace('_', ' ', $logType)) }}
                        @if($filter)
                            <span class="text-sm text-slate-500 ml-2">- filtrado por "{{ $filter }}"</span>
                        @endif
                    </h3>
                    <button onclick="refreshLogs()" class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-sm rounded-lg font-medium">
                        Refrescar
                    </button>
                </div>
                <div class="p-4">
                    <div id="log-content" class="bg-slate-900 rounded-lg p-4 overflow-x-auto font-mono text-xs leading-relaxed max-h-[70vh] overflow-y-auto">
                        @if(empty(trim($logContent)))
                            <span class="text-slate-500">No hay logs para mostrar</span>
                        @else
                            @foreach(explode("\n", $logContent) as $line)
                                @php
                                    $lineClass = 'text-slate-300';
                                    if (stripos($line, 'error') !== false || stripos($line, 'fail') !== false) {
                                        $lineClass = 'text-red-400';
                                    } elseif (stripos($line, 'warn') !== false) {
                                        $lineClass = 'text-yellow-400';
                                    } elseif (stripos($line, 'info') !== false) {
                                        $lineClass = 'text-blue-400';
                                    } elseif (stripos($line, 'debug') !== false) {
                                        $lineClass = 'text-slate-500';
                                    }
                                @endphp
                                <div class="{{ $lineClass }} hover:bg-slate-800 px-2 -mx-2 rounded">{{ $line }}</div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Filters -->
            <div class="mt-4 flex flex-wrap gap-2 items-center">
                <span class="text-sm text-slate-600">Filtros rapidos:</span>
                <a href="{{ route('system.logs', ['type' => 'kamailio', 'filter' => 'INVITE']) }}" class="px-2 py-1 bg-slate-200 hover:bg-slate-300 text-xs rounded text-slate-700 font-medium">INVITE</a>
                <a href="{{ route('system.logs', ['type' => 'kamailio', 'filter' => 'BYE']) }}" class="px-2 py-1 bg-slate-200 hover:bg-slate-300 text-xs rounded text-slate-700 font-medium">BYE</a>
                <a href="{{ route('system.logs', ['type' => 'kamailio', 'filter' => 'error']) }}" class="px-2 py-1 bg-red-100 hover:bg-red-200 text-xs rounded text-red-700 font-medium">Errores</a>
                <a href="{{ route('system.logs', ['type' => 'kamailio', 'filter' => 'AUTH']) }}" class="px-2 py-1 bg-slate-200 hover:bg-slate-300 text-xs rounded text-slate-700 font-medium">AUTH</a>
                <a href="{{ route('system.logs', ['type' => 'kamailio', 'filter' => 'CALL']) }}" class="px-2 py-1 bg-slate-200 hover:bg-slate-300 text-xs rounded text-slate-700 font-medium">CALL</a>
                <a href="{{ route('system.logs', ['type' => 'kamailio', 'filter' => 'dispatcher']) }}" class="px-2 py-1 bg-slate-200 hover:bg-slate-300 text-xs rounded text-slate-700 font-medium">Dispatcher</a>
                <a href="{{ route('system.logs', ['type' => 'kamailio', 'filter' => '403']) }}" class="px-2 py-1 bg-orange-100 hover:bg-orange-200 text-xs rounded text-orange-700 font-medium">403</a>
                <a href="{{ route('system.logs', ['type' => 'kamailio', 'filter' => '503']) }}" class="px-2 py-1 bg-orange-100 hover:bg-orange-200 text-xs rounded text-orange-700 font-medium">503</a>
            </div>
        </div>
    </div>

    <script>
        function refreshLogs() {
            window.location.reload();
        }

        // Auto-scroll to bottom
        document.addEventListener('DOMContentLoaded', function() {
            const logContent = document.getElementById('log-content');
            logContent.scrollTop = logContent.scrollHeight;
        });
    </script>
</x-app-layout>
