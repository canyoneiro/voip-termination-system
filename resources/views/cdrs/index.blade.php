<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Registro de Llamadas (CDRs)</h2>
                <p class="text-sm text-gray-400 mt-0.5">Historial completo de todas las llamadas procesadas</p>
            </div>
            <a href="{{ route('cdrs.export', request()->query()) }}" class="btn-success inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Exportar CSV
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="dark-card mb-6">
                <div class="px-4 py-3 border-b border-gray-700/50">
                    <h3 class="text-sm font-semibold text-white">Filtros de Busqueda</h3>
                </div>
                <form action="{{ route('cdrs.index') }}" method="GET" class="p-4">
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Desde</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="dark-input w-full text-sm py-1.5 px-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Hasta</label>
                            <input type="date" name="to" value="{{ request('to') }}" class="dark-input w-full text-sm py-1.5 px-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Cliente</label>
                            <select name="customer_id" class="dark-select w-full text-sm py-1.5 px-2">
                                <option value="">Todos</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Carrier</label>
                            <select name="carrier_id" class="dark-select w-full text-sm py-1.5 px-2">
                                <option value="">Todos</option>
                                @foreach($carriers as $carrier)
                                    <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>{{ $carrier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
                            <select name="status" class="dark-select w-full text-sm py-1.5 px-2">
                                <option value="">Todas</option>
                                <option value="answered" {{ request('status') === 'answered' ? 'selected' : '' }}>Contestadas</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Fallidas</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Duracion min.</label>
                            <input type="number" name="min_duration" value="{{ request('min_duration') }}" min="0" placeholder="Seg" class="dark-input w-full text-sm py-1.5 px-2">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 btn-primary text-sm py-1.5">Filtrar</button>
                            <a href="{{ route('cdrs.index') }}" class="btn-secondary text-sm py-1.5 px-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Resumen de Estadisticas -->
            @if($stats)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="stat-card blue">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Total Llamadas</div>
                    <div class="text-2xl font-bold text-white mt-1">{{ number_format($stats->total ?? 0) }}</div>
                </div>
                <div class="stat-card green">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Contestadas</div>
                    <div class="text-2xl font-bold text-green-400 mt-1">{{ number_format($stats->answered ?? 0) }}</div>
                    @if(($stats->total ?? 0) > 0)
                        <div class="text-xs text-gray-500 mt-1">ASR: {{ number_format(($stats->answered / $stats->total) * 100, 1) }}%</div>
                    @endif
                </div>
                <div class="stat-card red">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Fallidas</div>
                    <div class="text-2xl font-bold text-red-400 mt-1">{{ number_format(($stats->total ?? 0) - ($stats->answered ?? 0)) }}</div>
                </div>
                <div class="stat-card purple">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Duracion Total</div>
                    <div class="text-2xl font-bold text-purple-400 mt-1">{{ gmdate('H:i:s', $stats->total_duration ?? 0) }}</div>
                </div>
            </div>
            @endif

            <!-- Tabla de CDRs -->
            <div class="dark-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full dark-table">
                        <thead>
                            <tr>
                                <th class="text-left">Fecha/Hora</th>
                                <th class="text-left">Cliente</th>
                                <th class="text-left">Origen</th>
                                <th class="text-left">Destino</th>
                                <th class="text-right w-20">Duracion</th>
                                <th class="text-right w-20">PDD</th>
                                <th class="text-left">Carrier</th>
                                <th class="text-center w-24">Estado</th>
                                <th class="text-right w-16"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cdrs as $cdr)
                                <tr class="{{ !$cdr->answer_time ? 'bg-red-500/5' : '' }}">
                                    <td class="text-gray-400 text-sm whitespace-nowrap">{{ $cdr->start_time->format('d/m H:i:s') }}</td>
                                    <td class="text-gray-300 text-sm">{{ $cdr->customer->name ?? '-' }}</td>
                                    <td class="font-mono text-xs text-gray-300">{{ $cdr->caller }}</td>
                                    <td class="font-mono text-xs text-gray-300">{{ $cdr->callee }}</td>
                                    <td class="text-right text-gray-400 text-sm">{{ gmdate('i:s', $cdr->duration) }}</td>
                                    <td class="text-right text-gray-500 text-xs">{{ $cdr->pdd ? $cdr->pdd . 'ms' : '-' }}</td>
                                    <td class="text-gray-400 text-sm">{{ $cdr->carrier->name ?? '-' }}</td>
                                    <td class="text-center">
                                        @if($cdr->answer_time)
                                            <span class="badge badge-green">{{ $cdr->sip_code }}</span>
                                        @else
                                            <span class="badge badge-red" title="{{ $cdr->sip_reason }}">{{ $cdr->sip_code }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('cdrs.show', $cdr) }}" class="text-blue-400 hover:text-blue-300 text-xs">Ver</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-12">
                                        <svg class="w-12 h-12 mx-auto text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="text-gray-500">No se encontraron llamadas</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($cdrs->hasPages())
                <div class="px-4 py-3 border-t border-gray-700/50">
                    {{ $cdrs->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
