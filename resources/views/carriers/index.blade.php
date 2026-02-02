<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Carriers</h2>
                <p class="text-sm text-slate-500 mt-0.5">Proveedores de terminacion VoIP</p>
            </div>
            <a href="{{ route('carriers.create') }}" class="btn-primary inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nuevo Carrier
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Info de Enrutamiento -->
            <div class="mb-4 p-4 bg-slate-50 border border-slate-200 rounded-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-slate-700">
                            <strong class="text-slate-900">El enrutamiento se hace por PRIORIDAD</strong> -
                            Mayor numero = se usa primero. Columna "Prior." define el orden.
                        </span>
                    </div>
                    <a href="{{ route('help') }}#failover" class="text-sm text-blue-600 hover:text-blue-800">Ver documentacion</a>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="dark-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full dark-table">
                        <thead>
                            <tr>
                                <th class="text-left">Carrier</th>
                                <th class="text-left">Host</th>
                                <th class="text-center w-20">Prior.</th>
                                <th class="text-left w-36">Canales</th>
                                <th class="text-right w-28">Hoy</th>
                                <th class="text-center w-28">Estado</th>
                                <th class="text-right w-28">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($carriers as $carrier)
                                @php
                                    $channelPercent = $carrier->max_channels > 0 ? ($carrier->active_calls_count / $carrier->max_channels) * 100 : 0;
                                    $stateConfig = [
                                        'active' => ['class' => 'badge-green', 'label' => 'Activo'],
                                        'probing' => ['class' => 'badge-yellow', 'label' => 'Probando'],
                                        'inactive' => ['class' => 'badge-gray', 'label' => 'Inactivo'],
                                        'disabled' => ['class' => 'badge-red', 'label' => 'Deshabilitado'],
                                    ];
                                    $state = $stateConfig[$carrier->state] ?? ['class' => 'badge-gray', 'label' => ucfirst($carrier->state)];
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('carriers.show', $carrier) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                            {{ $carrier->name }}
                                        </a>
                                    </td>
                                    <td class="font-mono text-sm text-slate-500">{{ $carrier->host }}:{{ $carrier->port }}</td>
                                    <td class="text-center">
                                        <span class="text-slate-700 font-medium">{{ $carrier->priority }}</span>
                                    </td>
                                    <td>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-medium {{ $channelPercent >= 80 ? 'text-red-600' : ($channelPercent >= 50 ? 'text-yellow-600' : 'text-slate-600') }}">
                                                {{ $carrier->active_calls_count }}/{{ $carrier->max_channels }}
                                            </span>
                                            <div class="flex-1 h-1.5 bg-slate-200 rounded-full max-w-16">
                                                <div class="h-full rounded-full {{ $channelPercent >= 80 ? 'bg-red-500' : ($channelPercent >= 50 ? 'bg-yellow-500' : 'bg-green-500') }}" style="width: {{ min($channelPercent, 100) }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right text-slate-600 font-medium">{{ number_format($carrier->daily_calls) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $state['class'] }}">{{ $state['label'] }}</span>
                                        @if(!$carrier->probing_enabled)
                                            <span class="ml-1 text-yellow-500" title="Probing deshabilitado - Gestion manual">
                                                <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="flex items-center justify-end space-x-1">
                                            <a href="{{ route('carriers.show', $carrier) }}" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors" title="Ver detalles">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                            <a href="{{ route('carriers.edit', $carrier) }}" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition-colors" title="Editar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                            <form action="{{ route('carriers.destroy', $carrier) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este carrier? Esta accion no se puede deshacer.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-slate-100 rounded-lg transition-colors" title="Eliminar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-12">
                                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"></path></svg>
                                        <p class="text-slate-400">No hay carriers registrados</p>
                                        <a href="{{ route('carriers.create') }}" class="text-blue-600 text-sm mt-2 inline-block hover:text-blue-800 font-medium">Agregar primer carrier</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($carriers->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">
                    {{ $carriers->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
