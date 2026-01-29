<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Reglas de Deteccion de Fraude
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('fraud.index') }}" class="btn-secondary text-sm">Volver</a>
                <a href="{{ route('fraud.rules.create') }}" class="btn-primary text-sm">Nueva Regla</a>
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

            <div class="dark-card">
                <div class="overflow-x-auto">
                    <table class="dark-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Umbral</th>
                                <th>Accion</th>
                                <th>Severidad</th>
                                <th>Alcance</th>
                                <th>Estado</th>
                                <th>Disparos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rules as $rule)
                                <tr>
                                    <td class="font-medium">{{ $rule->name }}</td>
                                    <td>
                                        <span class="text-xs bg-slate-100 px-2 py-1 rounded">
                                            {{ ucfirst(str_replace('_', ' ', $rule->type)) }}
                                        </span>
                                    </td>
                                    <td>{{ $rule->threshold ?? '-' }}</td>
                                    <td>
                                        @php
                                            $actionColors = ['log' => 'gray', 'alert' => 'blue', 'throttle' => 'yellow', 'block' => 'red'];
                                        @endphp
                                        <span class="badge badge-{{ $actionColors[$rule->action] ?? 'gray' }}">
                                            {{ ucfirst($rule->action) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $sevColors = ['critical' => 'red', 'high' => 'orange', 'medium' => 'yellow', 'low' => 'blue'];
                                        @endphp
                                        <span class="badge badge-{{ $sevColors[$rule->severity] ?? 'gray' }}">
                                            {{ ucfirst($rule->severity) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($rule->customer)
                                            <a href="{{ route('customers.show', $rule->customer) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                                {{ $rule->customer->name }}
                                            </a>
                                        @else
                                            <span class="text-slate-500">Global</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($rule->active)
                                            <span class="badge badge-green">Activa</span>
                                        @else
                                            <span class="badge badge-gray">Inactiva</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($rule->trigger_count) }}</td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="{{ route('fraud.rules.edit', $rule) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                                Editar
                                            </a>
                                            <form method="POST" action="{{ route('fraud.rules.destroy', $rule) }}" class="inline"
                                                  onsubmit="return confirm('Eliminar esta regla?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-slate-500 py-8">
                                        No hay reglas configuradas
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
