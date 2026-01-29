<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Reportes Programados
            </h2>
            <a href="{{ route('reports.create') }}" class="btn-primary text-sm">Nuevo Reporte</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="stat-card blue">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Total Reportes</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="stat-card green">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Activos</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['active'] }}</p>
                </div>
                <div class="stat-card yellow">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Ejecuciones Hoy</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['executions_today'] }}</p>
                </div>
                <div class="stat-card purple">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Pendientes</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['pending'] }}</p>
                </div>
            </div>

            <!-- Reports Table -->
            <div class="dark-card">
                <div class="overflow-x-auto">
                    <table class="dark-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Frecuencia</th>
                                <th>Destinatarios</th>
                                <th>Formatos</th>
                                <th>Ultima Ejecucion</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr>
                                    <td class="font-medium">{{ $report->name }}</td>
                                    <td>
                                        @php
                                            $typeLabels = [
                                                'cdr_summary' => 'Resumen CDRs',
                                                'customer_usage' => 'Uso Cliente',
                                                'carrier_performance' => 'Carriers',
                                                'billing' => 'Facturacion',
                                                'qos_report' => 'QoS',
                                                'fraud_report' => 'Fraude',
                                            ];
                                        @endphp
                                        <span class="text-sm">{{ $typeLabels[$report->type] ?? $report->type }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $freqLabels = ['daily' => 'Diario', 'weekly' => 'Semanal', 'monthly' => 'Mensual'];
                                        @endphp
                                        <span class="badge badge-blue">{{ $freqLabels[$report->frequency] ?? $report->frequency }}</span>
                                    </td>
                                    <td>
                                        <span class="text-sm text-slate-600">{{ count($report->recipients) }} email(s)</span>
                                    </td>
                                    <td>
                                        @foreach($report->formats as $format)
                                            <span class="badge badge-gray mr-1">{{ strtoupper($format) }}</span>
                                        @endforeach
                                    </td>
                                    <td class="text-sm text-slate-600">
                                        {{ $report->last_run_at?->format('d/m/Y H:i') ?? 'Nunca' }}
                                    </td>
                                    <td>
                                        @if($report->active)
                                            <span class="badge badge-green">Activo</span>
                                        @else
                                            <span class="badge badge-gray">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="{{ route('reports.show', $report) }}" class="text-blue-600 hover:text-blue-800 text-sm">Ver</a>
                                            <a href="{{ route('reports.edit', $report) }}" class="text-slate-600 hover:text-slate-800 text-sm">Editar</a>
                                            <form method="POST" action="{{ route('reports.trigger', $report) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-800 text-sm">Ejecutar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-slate-500 py-8">
                                        No hay reportes programados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($reports->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $reports->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
