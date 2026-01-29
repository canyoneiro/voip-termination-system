<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Reporte: {{ $report->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.index') }}" class="btn-secondary text-sm">Volver</a>
                <a href="{{ route('reports.edit', $report) }}" class="btn-secondary text-sm">Editar</a>
                <form method="POST" action="{{ route('reports.trigger', $report) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn-primary text-sm">Ejecutar Ahora</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Info del reporte -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="dark-card p-4">
                    <p class="text-slate-500 text-xs uppercase tracking-wide">Estado</p>
                    @if($report->active)
                        <span class="badge badge-green mt-1">Activo</span>
                    @else
                        <span class="badge badge-gray mt-1">Inactivo</span>
                    @endif
                </div>
                <div class="dark-card p-4">
                    <p class="text-slate-500 text-xs uppercase tracking-wide">Tipo</p>
                    <p class="font-semibold text-slate-800 mt-1">
                        @switch($report->type)
                            @case('cdr_summary') Resumen CDRs @break
                            @case('customer_usage') Uso Cliente @break
                            @case('carrier_performance') Rendimiento Carrier @break
                            @case('billing') Facturacion @break
                            @case('qos_report') QoS @break
                            @case('fraud_report') Fraude @break
                            @default {{ $report->type }}
                        @endswitch
                    </p>
                </div>
                <div class="dark-card p-4">
                    <p class="text-slate-500 text-xs uppercase tracking-wide">Frecuencia</p>
                    <p class="font-semibold text-slate-800 mt-1">
                        @switch($report->frequency)
                            @case('daily') Diario @break
                            @case('weekly') Semanal @break
                            @case('monthly') Mensual @break
                            @default {{ $report->frequency }}
                        @endswitch
                    </p>
                </div>
                <div class="dark-card p-4">
                    <p class="text-slate-500 text-xs uppercase tracking-wide">Cliente</p>
                    <p class="font-semibold text-slate-800 mt-1">{{ $report->customer->name ?? 'Todos' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Destinatarios -->
                <div class="dark-card p-4">
                    <h3 class="font-semibold text-slate-800 mb-3">Destinatarios</h3>
                    <div class="space-y-2">
                        @foreach($report->recipients ?? [] as $email)
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 text-slate-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-slate-700">{{ $email }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Formatos -->
                <div class="dark-card p-4">
                    <h3 class="font-semibold text-slate-800 mb-3">Formatos</h3>
                    <div class="flex gap-2">
                        @foreach($report->formats ?? [] as $format)
                            <span class="badge badge-blue">{{ strtoupper($format) }}</span>
                        @endforeach
                    </div>
                </div>

                <!-- Fechas -->
                <div class="dark-card p-4">
                    <h3 class="font-semibold text-slate-800 mb-3">Informacion</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Creado:</span>
                            <span class="text-slate-700">{{ $report->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Ultima ejecucion:</span>
                            <span class="text-slate-700">{{ $report->last_run_at?->format('d/m/Y H:i') ?? 'Nunca' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Proxima ejecucion:</span>
                            <span class="text-slate-700">{{ $report->next_run_at?->format('d/m/Y H:i') ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial de ejecuciones -->
            <div class="dark-card">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-800">Historial de ejecuciones</h3>
                </div>
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Periodo</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Registros</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($executions as $execution)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    {{ $execution->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    {{ $execution->period_start?->format('d/m/Y') }} - {{ $execution->period_end?->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @switch($execution->status)
                                        @case('completed')
                                            <span class="badge badge-green">Completado</span>
                                            @break
                                        @case('pending')
                                            <span class="badge badge-yellow">Pendiente</span>
                                            @break
                                        @case('processing')
                                            <span class="badge badge-blue">Procesando</span>
                                            @break
                                        @case('failed')
                                            <span class="badge badge-red">Error</span>
                                            @break
                                        @default
                                            <span class="badge badge-gray">{{ $execution->status }}</span>
                                    @endswitch
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-600">
                                    {{ number_format($execution->records_count ?? 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    @if($execution->status === 'completed' && $execution->file_path)
                                        @foreach($report->formats ?? ['pdf'] as $format)
                                            <a href="{{ route('reports.executions.download', [$execution, $format]) }}"
                                               class="text-blue-600 hover:text-blue-800 mr-2">
                                                {{ strtoupper($format) }}
                                            </a>
                                        @endforeach
                                    @elseif($execution->status === 'failed')
                                        <span class="text-red-600 text-xs" title="{{ $execution->error_message }}">
                                            Ver error
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    No hay ejecuciones registradas
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($executions->hasPages())
                    <div class="px-6 py-4 border-t border-slate-200">
                        {{ $executions->links() }}
                    </div>
                @endif
            </div>

            <!-- Zona peligrosa -->
            <div class="mt-6 dark-card p-6 border-red-200">
                <h3 class="font-semibold text-red-600 mb-4">Zona peligrosa</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-700">Eliminar este reporte programado</p>
                        <p class="text-xs text-slate-500">Esta accion no se puede deshacer. Se eliminaran todas las ejecuciones y archivos.</p>
                    </div>
                    <form method="POST" action="{{ route('reports.destroy', $report) }}" onsubmit="return confirm('Estas seguro de eliminar este reporte?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger text-sm">Eliminar Reporte</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
