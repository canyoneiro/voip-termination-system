<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Deteccion de Fraude
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('fraud.incidents') }}" class="btn-secondary text-sm">Ver Incidentes</a>
                <a href="{{ route('fraud.rules') }}" class="btn-primary text-sm">Gestionar Reglas</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="stat-card red">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Pendientes</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['pending'] }}</p>
                    <p class="text-xs text-slate-500 mt-1">requieren revision</p>
                </div>
                <div class="stat-card yellow">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Investigando</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['investigating'] }}</p>
                    <p class="text-xs text-slate-500 mt-1">en proceso</p>
                </div>
                <div class="stat-card blue">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Hoy</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['today'] }}</p>
                    <p class="text-xs text-slate-500 mt-1">incidentes detectados</p>
                </div>
                <div class="stat-card purple">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Esta Semana</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['week'] }}</p>
                    <p class="text-xs text-slate-500 mt-1">ultimos 7 dias</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Severity Breakdown -->
                <div class="dark-card p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Incidentes por Severidad (7 dias)</h3>
                    <div class="space-y-3">
                        @php
                            $severities = [
                                'critical' => ['label' => 'Critico', 'color' => 'red'],
                                'high' => ['label' => 'Alto', 'color' => 'orange'],
                                'medium' => ['label' => 'Medio', 'color' => 'yellow'],
                                'low' => ['label' => 'Bajo', 'color' => 'blue'],
                            ];
                            $totalSeverity = array_sum($bySeverity);
                        @endphp
                        @foreach($severities as $key => $info)
                            @php
                                $count = $bySeverity[$key] ?? 0;
                                $percent = $totalSeverity > 0 ? ($count / $totalSeverity) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-slate-600">{{ $info['label'] }}</span>
                                    <span class="text-slate-800 font-medium">{{ $count }}</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2">
                                    <div class="bg-{{ $info['color'] }}-500 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-200">
                        <p class="text-sm text-slate-600">
                            <span class="font-semibold text-slate-800">{{ $activeRules }}</span> reglas activas
                        </p>
                    </div>
                </div>

                <!-- Top Risk Customers -->
                <div class="dark-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-800">Clientes con Mayor Riesgo</h3>
                        <a href="{{ route('fraud.risk-scores') }}" class="text-sm text-blue-600 hover:text-blue-800">Ver todos</a>
                    </div>
                    @if($topRiskCustomers->count() > 0)
                        <div class="space-y-3">
                            @foreach($topRiskCustomers as $item)
                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-slate-800">{{ $item['customer']->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $item['incidents_count'] }} incidentes</p>
                                    </div>
                                    <div class="text-right">
                                        @php
                                            $score = $item['score'];
                                            $color = $score >= 70 ? 'red' : ($score >= 40 ? 'yellow' : 'green');
                                        @endphp
                                        <span class="badge badge-{{ $color }}">
                                            {{ $score }}/100
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-500 text-center py-8">No hay datos de riesgo disponibles</p>
                    @endif
                </div>
            </div>

            <!-- Recent Incidents -->
            <div class="dark-card">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800">Incidentes Recientes</h3>
                    <a href="{{ route('fraud.incidents') }}" class="text-sm text-blue-600 hover:text-blue-800">Ver todos</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="dark-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Cliente</th>
                                <th>Severidad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentIncidents as $incident)
                                <tr>
                                    <td class="mono text-xs">{{ $incident->created_at->format('d/m H:i') }}</td>
                                    <td>
                                        <span class="text-sm">{{ $incident->fraudRule?->name ?? ucfirst(str_replace('_', ' ', $incident->type)) }}</span>
                                    </td>
                                    <td>{{ $incident->customer?->name ?? '-' }}</td>
                                    <td>
                                        @php
                                            $sevColors = ['critical' => 'red', 'high' => 'orange', 'medium' => 'yellow', 'low' => 'blue'];
                                        @endphp
                                        <span class="badge badge-{{ $sevColors[$incident->severity] ?? 'gray' }}">
                                            {{ ucfirst($incident->severity) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'yellow',
                                                'investigating' => 'blue',
                                                'false_positive' => 'gray',
                                                'confirmed' => 'red',
                                                'resolved' => 'green',
                                            ];
                                            $statusLabels = [
                                                'pending' => 'Pendiente',
                                                'investigating' => 'Investigando',
                                                'false_positive' => 'Falso Positivo',
                                                'confirmed' => 'Confirmado',
                                                'resolved' => 'Resuelto',
                                            ];
                                        @endphp
                                        <span class="badge badge-{{ $statusColors[$incident->status] ?? 'gray' }}">
                                            {{ $statusLabels[$incident->status] ?? $incident->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('fraud.incidents.show', $incident) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-slate-500 py-8">
                                        No hay incidentes recientes
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
