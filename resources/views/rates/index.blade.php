<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Tarifas y LCR
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('rates.lcr-test') }}" class="btn-secondary text-sm">Test LCR</a>
                <a href="{{ route('rates.import') }}" class="btn-primary text-sm">Importar</a>
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

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="stat-card blue">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Destinos</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['destinations']) }}</p>
                    <a href="{{ route('rates.destinations') }}" class="text-xs text-blue-600 hover:text-blue-800">Gestionar</a>
                </div>
                <div class="stat-card green">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Tarifas Carrier</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['carrier_rates']) }}</p>
                    <a href="{{ route('rates.carrier-rates') }}" class="text-xs text-blue-600 hover:text-blue-800">Gestionar</a>
                </div>
                <div class="stat-card yellow">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Planes de Tarifa</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['rate_plans']) }}</p>
                    <a href="{{ route('rates.rate-plans') }}" class="text-xs text-blue-600 hover:text-blue-800">Gestionar</a>
                </div>
                <div class="stat-card purple">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Tarifas Cliente</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['customer_rates']) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Quick Actions -->
                <div class="dark-card p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Acciones Rapidas</h3>
                    <div class="space-y-3">
                        <a href="{{ route('rates.destinations.create') }}" class="flex items-center p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-slate-800">Nuevo Destino</p>
                                <p class="text-xs text-slate-500">Agregar prefijo de destino</p>
                            </div>
                        </a>
                        <a href="{{ route('rates.carrier-rates.create') }}" class="flex items-center p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-slate-800">Nueva Tarifa Carrier</p>
                                <p class="text-xs text-slate-500">Agregar tarifa por prefijo</p>
                            </div>
                        </a>
                        <a href="{{ route('rates.rate-plans.create') }}" class="flex items-center p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-slate-800">Nuevo Plan de Tarifas</p>
                                <p class="text-xs text-slate-500">Crear plan con markup</p>
                            </div>
                        </a>
                        <form method="POST" action="{{ route('rates.sync-redis') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition text-left">
                                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-800">Sincronizar Redis</p>
                                    <p class="text-xs text-slate-500">Actualizar datos LCR en Kamailio</p>
                                </div>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Recent Imports -->
                <div class="dark-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-800">Importaciones Recientes</h3>
                        <a href="{{ route('rates.import') }}" class="text-sm text-blue-600 hover:text-blue-800">Nueva importacion</a>
                    </div>
                    @if($recentImports->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentImports as $import)
                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-slate-800 text-sm">{{ ucfirst($import->type) }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $import->created_at->format('d/m/Y H:i') }} por {{ $import->user?->name ?? 'Sistema' }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        @if($import->status === 'completed')
                                            <span class="badge badge-green">{{ number_format($import->imported_count) }}</span>
                                        @elseif($import->status === 'failed')
                                            <span class="badge badge-red">Error</span>
                                        @else
                                            <span class="badge badge-yellow">{{ ucfirst($import->status) }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-500 text-center py-8">No hay importaciones recientes</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
