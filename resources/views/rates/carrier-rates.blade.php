<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Tarifas de Carriers
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('rates.index') }}" class="btn-secondary text-sm">Volver</a>
                <a href="{{ route('rates.carrier-rates.create') }}" class="btn-primary text-sm">Nueva Tarifa</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Aviso importante -->
            <div class="mb-4 p-3 bg-slate-100 border-l-4 border-slate-400 rounded-r-lg flex items-center">
                <svg class="w-5 h-5 text-slate-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span class="text-slate-600 text-sm">
                    Estas tarifas son para <strong>calcular costos</strong> en los CDRs, no controlan el enrutamiento.
                    El enrutamiento se configura en <a href="{{ route('carriers.index') }}" class="text-blue-600 underline">Carriers</a> (por prioridad).
                </span>
            </div>

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

            <!-- Filters -->
            <div class="dark-card p-4 mb-6">
                <form method="GET" action="{{ route('rates.carrier-rates') }}" class="flex gap-4 flex-wrap">
                    <div class="w-48">
                        <select name="carrier_id" class="input-field w-full">
                            <option value="">Todos los carriers</option>
                            @foreach($carriers as $carrier)
                                <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                    {{ $carrier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <input type="text" name="prefix" value="{{ request('prefix') }}"
                               placeholder="Filtrar por prefijo..."
                               class="input-field w-full font-mono">
                    </div>
                    <button type="submit" class="btn-primary">Filtrar</button>
                    @if(request('carrier_id') || request('prefix'))
                        <a href="{{ route('rates.carrier-rates') }}" class="btn-secondary">Limpiar</a>
                    @endif
                </form>
            </div>

            <!-- Table -->
            <div class="dark-card overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Carrier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Prefijo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Destino</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Tarifa/min</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Conexion</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Billing</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($rates as $rate)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-medium text-slate-800">{{ $rate->carrier->name ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono font-semibold text-slate-800">{{ $rate->destinationPrefix->prefix ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    {{ $rate->destinationPrefix->description ?? $rate->destinationPrefix->country ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="font-mono text-slate-800">${{ number_format($rate->cost_per_minute, 4) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-slate-600">
                                    @if($rate->connection_fee > 0)
                                        ${{ number_format($rate->connection_fee, 4) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-600">
                                    {{ $rate->billing_increment }}s
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($rate->active)
                                        <span class="badge badge-green">Activa</span>
                                    @else
                                        <span class="badge badge-gray">Inactiva</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('rates.carrier-rates.edit', $rate) }}" class="text-blue-600 hover:text-blue-800 mr-3">Editar</a>
                                    <form method="POST" action="{{ route('rates.carrier-rates.destroy', $rate) }}" class="inline" onsubmit="return confirm('Eliminar esta tarifa?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                    No hay tarifas registradas
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $rates->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
