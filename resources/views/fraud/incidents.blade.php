<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Incidentes de Fraude
            </h2>
            <a href="{{ route('fraud.index') }}" class="btn-secondary text-sm">Volver al Dashboard</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="dark-card p-4 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Estado</label>
                        <select name="status" class="dark-select w-full">
                            <option value="">Todos</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="investigating" {{ request('status') === 'investigating' ? 'selected' : '' }}>Investigando</option>
                            <option value="false_positive" {{ request('status') === 'false_positive' ? 'selected' : '' }}>Falso Positivo</option>
                            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                            <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resuelto</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Severidad</label>
                        <select name="severity" class="dark-select w-full">
                            <option value="">Todas</option>
                            <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critico</option>
                            <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>Alto</option>
                            <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Medio</option>
                            <option value="low" {{ request('severity') === 'low' ? 'selected' : '' }}>Bajo</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Cliente</label>
                        <select name="customer_id" class="dark-select w-full">
                            <option value="">Todos</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Tipo</label>
                        <select name="type" class="dark-select w-full">
                            <option value="">Todos</option>
                            <option value="high_cost_destination" {{ request('type') === 'high_cost_destination' ? 'selected' : '' }}>Alto Costo</option>
                            <option value="traffic_spike" {{ request('type') === 'traffic_spike' ? 'selected' : '' }}>Pico Trafico</option>
                            <option value="wangiri" {{ request('type') === 'wangiri' ? 'selected' : '' }}>Wangiri</option>
                            <option value="unusual_destination" {{ request('type') === 'unusual_destination' ? 'selected' : '' }}>Destino Inusual</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn-primary">Filtrar</button>
                        <a href="{{ route('fraud.incidents') }}" class="btn-secondary">Limpiar</a>
                    </div>
                </form>
            </div>

            <!-- Incidents Table -->
            <div class="dark-card">
                <div class="overflow-x-auto">
                    <table class="dark-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Cliente</th>
                                <th>Descripcion</th>
                                <th>Severidad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incidents as $incident)
                                <tr>
                                    <td class="mono text-xs">{{ $incident->created_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $incident->fraudRule?->name ?? ucfirst(str_replace('_', ' ', $incident->type)) }}</td>
                                    <td>
                                        @if($incident->customer)
                                            <a href="{{ route('customers.show', $incident->customer) }}" class="text-blue-600 hover:text-blue-800">
                                                {{ $incident->customer->name }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="max-w-xs truncate">{{ Str::limit($incident->description, 50) }}</td>
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
                                        @endphp
                                        <span class="badge badge-{{ $statusColors[$incident->status] ?? 'gray' }}">
                                            {{ ucfirst(str_replace('_', ' ', $incident->status)) }}
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
                                    <td colspan="7" class="text-center text-slate-500 py-8">
                                        No hay incidentes con los filtros seleccionados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($incidents->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $incidents->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
