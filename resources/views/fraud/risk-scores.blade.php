<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Puntuaciones de Riesgo
            </h2>
            <a href="{{ route('fraud.index') }}" class="btn-secondary text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800">
                    <strong>Nota:</strong> Las puntuaciones de riesgo se calculan en base a la actividad reciente de cada cliente,
                    incluyendo incidentes de fraude, patrones de llamadas inusuales y consumo anormal.
                </p>
            </div>

            <div class="dark-card overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Score</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Nivel</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Incidentes (30d)</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Factores</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($scores as $score)
                            @php
                                $riskLevel = $score['score'] >= 80 ? 'critical' : ($score['score'] >= 60 ? 'high' : ($score['score'] >= 40 ? 'medium' : 'low'));
                                $levelColors = ['critical' => 'red', 'high' => 'orange', 'medium' => 'yellow', 'low' => 'green'];
                                $levelLabels = ['critical' => 'Critico', 'high' => 'Alto', 'medium' => 'Medio', 'low' => 'Bajo'];
                            @endphp
                            <tr class="hover:bg-slate-50 {{ $score['score'] >= 80 ? 'bg-red-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full bg-{{ $levelColors[$riskLevel] }}-100 flex items-center justify-center mr-3">
                                            <span class="text-{{ $levelColors[$riskLevel] }}-600 font-bold">{{ substr($score['customer_name'], 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-800">{{ $score['customer_name'] }}</p>
                                            <p class="text-xs text-slate-500">ID: {{ $score['customer_id'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="text-2xl font-bold text-{{ $levelColors[$riskLevel] }}-600">{{ $score['score'] }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="badge badge-{{ $levelColors[$riskLevel] }}">{{ $levelLabels[$riskLevel] }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="font-medium">{{ $score['incident_count'] ?? 0 }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if(isset($score['factors']) && count($score['factors']) > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach(array_slice($score['factors'], 0, 3) as $factor)
                                                <span class="text-xs bg-slate-100 px-2 py-1 rounded">{{ $factor }}</span>
                                            @endforeach
                                            @if(count($score['factors']) > 3)
                                                <span class="text-xs text-slate-500">+{{ count($score['factors']) - 3 }} mas</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-sm">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('customers.show', $score['customer_id']) }}" class="text-blue-600 hover:text-blue-800 text-sm mr-2">Ver cliente</a>
                                    <a href="{{ route('fraud.incidents', ['customer_id' => $score['customer_id']]) }}" class="text-blue-600 hover:text-blue-800 text-sm">Incidentes</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    No hay datos de riesgo disponibles
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Leyenda -->
            <div class="mt-6 dark-card p-6">
                <h3 class="font-semibold text-slate-800 mb-4">Interpretacion del Score</h3>
                <div class="grid grid-cols-4 gap-4">
                    <div class="p-3 bg-green-50 rounded-lg border border-green-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-green-800">Bajo</span>
                            <span class="badge badge-green">0-39</span>
                        </div>
                        <p class="text-xs text-green-700">Cliente sin actividad sospechosa</p>
                    </div>
                    <div class="p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-yellow-800">Medio</span>
                            <span class="badge badge-yellow">40-59</span>
                        </div>
                        <p class="text-xs text-yellow-700">Actividad a monitorear</p>
                    </div>
                    <div class="p-3 bg-orange-50 rounded-lg border border-orange-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-orange-800">Alto</span>
                            <span class="badge badge-orange">60-79</span>
                        </div>
                        <p class="text-xs text-orange-700">Requiere revision</p>
                    </div>
                    <div class="p-3 bg-red-50 rounded-lg border border-red-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-red-800">Critico</span>
                            <span class="badge badge-red">80-100</span>
                        </div>
                        <p class="text-xs text-red-700">Accion inmediata requerida</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
