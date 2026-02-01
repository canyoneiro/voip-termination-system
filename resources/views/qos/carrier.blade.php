<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                QoS - {{ $carrier->name }}
            </h2>
            <a href="{{ route('qos.index') }}" class="btn btn-secondary btn-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="stat-card blue">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">MOS Promedio</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['avg_mos'] ?? 0, 2) }}</p>
                    <p class="text-xs text-slate-500 mt-1">ultimos 7 dias</p>
                </div>
                <div class="stat-card green">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">PDD Promedio</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['avg_pdd'] ?? 0) }} ms</p>
                    <p class="text-xs text-slate-500 mt-1">Post Dial Delay</p>
                </div>
                <div class="stat-card yellow">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Llamadas Analizadas</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['total_calls'] ?? 0) }}</p>
                    <p class="text-xs text-slate-500 mt-1">ultimos 7 dias</p>
                </div>
                <div class="stat-card purple">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Calidad Pobre</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['poor_quality_count'] ?? 0) }}</p>
                    <p class="text-xs text-slate-500 mt-1">MOS < 3.0</p>
                </div>
            </div>

            <!-- Carrier Info -->
            <div class="dark-card p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-sm text-slate-500">Host</p>
                        <p class="font-medium text-slate-800">{{ $carrier->host }}:{{ $carrier->port }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Transporte</p>
                        <p class="font-medium text-slate-800">{{ strtoupper($carrier->transport) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Estado</p>
                        <span class="badge {{ $carrier->state === 'active' ? 'badge-green' : ($carrier->state === 'probing' ? 'badge-yellow' : 'badge-red') }}">
                            {{ ucfirst($carrier->state) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Daily Trend Chart -->
            <div class="dark-card p-6 mb-6">
                <h3 class="font-semibold text-slate-800 mb-4">Tendencia MOS (30 dias)</h3>
                <div class="h-64">
                    <canvas id="mosTrendChart"></canvas>
                </div>
            </div>

            <!-- Recent Metrics -->
            <div class="dark-card">
                <div class="p-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-800">Metricas Recientes (7 dias)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="dark-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Duracion</th>
                                <th>MOS</th>
                                <th>PDD</th>
                                <th>Jitter</th>
                                <th>Packet Loss</th>
                                <th>Codec</th>
                                <th>Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($metrics as $metric)
                                <tr>
                                    <td class="mono text-xs">{{ $metric->created_at->format('d/m H:i:s') }}</td>
                                    <td>{{ $metric->cdr?->customer?->name ?? '-' }}</td>
                                    <td class="text-green-400">{{ $metric->cdr ? gmdate('i:s', $metric->cdr->billable_duration) : '-' }}</td>
                                    <td>
                                        <span class="badge {{ $metric->mos_score >= 4 ? 'badge-green' : ($metric->mos_score >= 3 ? 'badge-yellow' : 'badge-red') }}">
                                            {{ number_format($metric->mos_score, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-purple-400">{{ $metric->pdd ?? '-' }} ms</td>
                                    <td>{{ $metric->jitter ?? '-' }} ms</td>
                                    <td>{{ $metric->packet_loss ? number_format($metric->packet_loss, 2) . '%' : '-' }}</td>
                                    <td class="mono">{{ $metric->codec_used ?? '-' }}</td>
                                    <td>
                                        @php
                                            $ratingColors = [
                                                'excellent' => 'badge-green',
                                                'good' => 'badge-blue',
                                                'fair' => 'badge-yellow',
                                                'poor' => 'badge-orange',
                                                'bad' => 'badge-red',
                                            ];
                                        @endphp
                                        <span class="badge {{ $ratingColors[$metric->quality_rating] ?? 'badge-gray' }}">
                                            {{ ucfirst($metric->quality_rating) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-slate-500 py-8">
                                        No hay metricas disponibles para este carrier
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($metrics->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $metrics->links() }}
                    </div>
                @endif
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
                labels: {!! json_encode($dailyStats->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))) !!},
                datasets: [{
                    label: 'MOS Promedio',
                    data: {!! json_encode($dailyStats->pluck('avg_mos')) !!},
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
