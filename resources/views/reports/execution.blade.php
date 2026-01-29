<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Ejecucion de Reporte
            </h2>
            <a href="{{ route('reports.show', $execution->report) }}" class="btn-secondary text-sm">Volver al reporte</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dark-card p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <p class="text-slate-500 text-xs uppercase tracking-wide">Reporte</p>
                        <p class="font-semibold text-slate-800 mt-1">{{ $execution->report->name }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs uppercase tracking-wide">Estado</p>
                        <div class="mt-1">
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
                            @endswitch
                        </div>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs uppercase tracking-wide">Fecha</p>
                        <p class="font-semibold text-slate-800 mt-1">{{ $execution->created_at->format('d/m/Y H:i:s') }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs uppercase tracking-wide">Registros</p>
                        <p class="font-semibold text-slate-800 mt-1">{{ number_format($execution->records_count ?? 0) }}</p>
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-4">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-slate-500 text-xs uppercase tracking-wide">Periodo inicio</p>
                            <p class="text-slate-800 mt-1">{{ $execution->period_start?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 text-xs uppercase tracking-wide">Periodo fin</p>
                            <p class="text-slate-800 mt-1">{{ $execution->period_end?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                    </div>

                    @if($execution->status === 'completed' && $execution->file_path)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="font-medium text-green-800 mb-2">Archivos disponibles</h4>
                            <div class="flex gap-3">
                                @foreach($execution->report->formats ?? ['pdf'] as $format)
                                    <a href="{{ route('reports.executions.download', [$execution, $format]) }}"
                                       class="btn-primary text-sm">
                                        Descargar {{ strtoupper($format) }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($execution->status === 'failed' && $execution->error_message)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="font-medium text-red-800 mb-2">Error</h4>
                            <pre class="text-sm text-red-700 whitespace-pre-wrap">{{ $execution->error_message }}</pre>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
