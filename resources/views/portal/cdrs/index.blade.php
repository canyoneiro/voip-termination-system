<x-portal.layouts.app>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            Historial de Llamadas (CDRs)
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="dark-card p-4 mb-6">
                <form method="GET" action="{{ route('portal.cdrs.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Desde</label>
                        <input type="date" name="from" value="{{ request('from', now()->subDays(7)->format('Y-m-d')) }}" class="dark-input w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Hasta</label>
                        <input type="date" name="to" value="{{ request('to', now()->format('Y-m-d')) }}" class="dark-input w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Numero</label>
                        <input type="text" name="number" value="{{ request('number') }}" placeholder="Caller o callee" class="dark-input w-full">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn-primary">Filtrar</button>
                        <a href="{{ route('portal.cdrs.index') }}" class="btn-secondary">Limpiar</a>
                    </div>
                </form>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="stat-card blue">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Total Llamadas</p>
                    <p class="text-xl font-bold text-slate-800 mt-1">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="stat-card green">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Contestadas</p>
                    <p class="text-xl font-bold text-slate-800 mt-1">{{ number_format($summary['answered']) }}</p>
                </div>
                <div class="stat-card yellow">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Minutos</p>
                    <p class="text-xl font-bold text-slate-800 mt-1">{{ number_format($summary['minutes']) }}</p>
                </div>
                <div class="stat-card purple">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">ASR</p>
                    <p class="text-xl font-bold text-slate-800 mt-1">{{ $summary['asr'] }}%</p>
                </div>
            </div>

            <!-- Export Button -->
            <div class="flex justify-end mb-4">
                <a href="{{ route('portal.cdrs.export', request()->query()) }}" class="btn-secondary text-sm">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Exportar CSV
                </a>
            </div>

            <!-- CDR Table -->
            <div class="dark-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="dark-table">
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Origen</th>
                                <th>Destino</th>
                                <th title="Tiempo facturable">Billable</th>
                                <th title="Tiempo de timbrado">Ring</th>
                                <th title="Post Dial Delay">PDD</th>
                                <th>Codigo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cdrs as $cdr)
                                <tr>
                                    <td class="mono text-xs">{{ $cdr->start_time->format('Y-m-d H:i:s') }}</td>
                                    <td class="mono">{{ $cdr->caller }}</td>
                                    <td class="mono">{{ $cdr->callee }}</td>
                                    <td class="text-green-400">{{ gmdate('i:s', $cdr->billable_duration) }}</td>
                                    <td class="text-yellow-400">{{ $cdr->ring_time ? $cdr->ring_time . 's' : '-' }}</td>
                                    <td class="text-purple-400">{{ $cdr->pdd ? $cdr->pdd . 'ms' : '-' }}</td>
                                    <td>
                                        @if($cdr->sip_code == 200)
                                            <span class="badge badge-green">200 OK</span>
                                        @elseif($cdr->sip_code >= 400 && $cdr->sip_code < 500)
                                            <span class="badge badge-yellow">{{ $cdr->sip_code }} {{ Str::limit($cdr->sip_reason, 12) }}</span>
                                        @elseif($cdr->sip_code >= 500)
                                            <span class="badge badge-red">{{ $cdr->sip_code }} {{ Str::limit($cdr->sip_reason, 12) }}</span>
                                        @else
                                            <span class="badge badge-gray">{{ $cdr->sip_code }} {{ Str::limit($cdr->sip_reason, 12) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('portal.cdrs.show', $cdr) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-slate-500 py-8">
                                        No hay registros para los filtros seleccionados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($cdrs->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $cdrs->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-portal.layouts.app>
