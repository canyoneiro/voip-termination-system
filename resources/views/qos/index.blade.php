<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            QoS - Calidad de Servicio
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Real-time Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="stat-card blue">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">MOS Promedio</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($realtime['avg_mos'] ?? 0, 2) }}</p>
                    <p class="text-xs text-slate-500 mt-1">ultima hora</p>
                </div>
                <div class="stat-card green">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">PDD Promedio</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($realtime['avg_pdd'] ?? 0) }} ms</p>
                    <p class="text-xs text-slate-500 mt-1">Post Dial Delay</p>
                </div>
                <div class="stat-card yellow">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Llamadas Analizadas</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($realtime['total_calls'] ?? 0) }}</p>
                    <p class="text-xs text-slate-500 mt-1">ultimas 24h</p>
                </div>
                <div class="stat-card purple">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Calidad Pobre</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($realtime['poor_quality_count'] ?? 0) }}</p>
                    <p class="text-xs text-slate-500 mt-1">MOS < 3.0</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Quality Distribution -->
                <div class="dark-card p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Distribucion de Calidad (24h)</h3>
                    <div class="space-y-3">
                        @php
                            $totalDist = array_sum($distribution);
                            $ratings = [
                                'excellent' => ['label' => 'Excelente (4.0+)', 'color' => 'green'],
                                'good' => ['label' => 'Buena (3.5-4.0)', 'color' => 'blue'],
                                'fair' => ['label' => 'Aceptable (3.0-3.5)', 'color' => 'yellow'],
                                'poor' => ['label' => 'Pobre (2.5-3.0)', 'color' => 'orange'],
                                'bad' => ['label' => 'Mala (<2.5)', 'color' => 'red'],
                            ];
                        @endphp
                        @foreach($ratings as $key => $info)
                            @php
                                $count = $distribution[$key] ?? 0;
                                $percent = $totalDist > 0 ? ($count / $totalDist) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-slate-600">{{ $info['label'] }}</span>
                                    <span class="text-slate-800 font-medium">{{ number_format($count) }} ({{ number_format($percent, 1) }}%)</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2">
                                    <div class="bg-{{ $info['color'] }}-500 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Carrier Comparison -->
                <div class="dark-card p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Comparativa por Carrier (24h)</h3>
                    @if($carrierStats->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="dark-table">
                                <thead>
                                    <tr>
                                        <th>Carrier</th>
                                        <th>MOS</th>
                                        <th>PDD</th>
                                        <th>Llamadas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($carrierStats as $stat)
                                        <tr>
                                            <td class="font-medium">{{ $stat->carrier_name }}</td>
                                            <td>
                                                <span class="badge {{ $stat->avg_mos >= 4 ? 'badge-green' : ($stat->avg_mos >= 3 ? 'badge-yellow' : 'badge-red') }}">
                                                    {{ number_format($stat->avg_mos, 2) }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($stat->avg_pdd) }} ms</td>
                                            <td>{{ number_format($stat->calls) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-slate-500 text-center py-8">No hay datos disponibles</p>
                    @endif
                </div>
            </div>

            <!-- MOS Trend Chart -->
            <div class="dark-card p-6 mb-6">
                <h3 class="font-semibold text-slate-800 mb-4">Tendencia MOS (24h)</h3>
                <div class="h-64">
                    <canvas id="mosTrendChart"></canvas>
                </div>
            </div>

            <!-- Poor Quality Calls -->
            <div class="dark-card">
                <div class="p-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-800">Llamadas con Calidad Pobre (MOS < 3.0)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="dark-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Carrier</th>
                                <th>MOS</th>
                                <th>PDD</th>
                                <th>Codec</th>
                                <th>Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($poorCalls as $metric)
                                <tr>
                                    <td class="mono text-xs">{{ $metric->created_at->format('d/m H:i:s') }}</td>
                                    <td>{{ $metric->cdr?->customer?->name ?? '-' }}</td>
                                    <td>{{ $metric->cdr?->carrier?->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-red">{{ number_format($metric->mos_score, 2) }}</span>
                                    </td>
                                    <td>{{ $metric->pdd }} ms</td>
                                    <td class="mono">{{ $metric->codec_used ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $metric->quality_rating === 'poor' ? 'yellow' : 'red' }}">
                                            {{ ucfirst($metric->quality_rating) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-slate-500 py-8">
                                        No hay llamadas con calidad pobre en las ultimas 24h
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('mosTrendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($hourlyTrend->pluck('hour')->map(fn($h) => \Carbon\Carbon::parse($h)->format('H:i'))) !!},
                datasets: [{
                    label: 'MOS Promedio',
                    data: {!! json_encode($hourlyTrend->pluck('avg_mos')) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        min: 1,
                        max: 5,
                        grid: { color: '#e2e8f0' }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
