<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white">Lista Negra de IPs</h2>
            <p class="text-sm text-gray-400 mt-0.5">IPs bloqueadas para proteger el sistema</p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400">{{ session('error') }}</div>
            @endif

            <!-- Formulario Agregar IP -->
            <div class="dark-card mb-6">
                <div class="px-4 py-3 border-b border-gray-700/50 bg-red-500/10">
                    <h3 class="text-sm font-semibold text-red-400">Bloquear Nueva IP</h3>
                </div>
                <form action="{{ route('blacklist.store') }}" method="POST" class="p-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Direccion IP *</label>
                            <input type="text" name="ip_address" required placeholder="192.168.1.1"
                                class="dark-input w-full text-sm py-1.5 px-2 font-mono">
                            @error('ip_address')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Motivo *</label>
                            <input type="text" name="reason" required placeholder="Motivo del bloqueo"
                                class="dark-input w-full text-sm py-1.5 px-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Expira (opcional)</label>
                            <input type="datetime-local" name="expires_at"
                                class="dark-input w-full text-sm py-1.5 px-2">
                        </div>
                        <div class="flex items-end gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="permanent" value="1"
                                    class="rounded border-gray-600 text-red-500 bg-gray-700 focus:ring-red-500/20">
                                <span class="ml-2 text-sm text-gray-400">Permanente</span>
                            </label>
                            <button type="submit" class="btn-danger text-sm">
                                Bloquear IP
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Resumen -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="stat-card blue">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Total Bloqueadas</div>
                    <div class="text-2xl font-bold text-white mt-1">{{ $blacklist->total() }}</div>
                </div>
                <div class="stat-card red">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Permanentes</div>
                    <div class="text-2xl font-bold text-red-400 mt-1">{{ $blacklist->where('permanent', true)->count() }}</div>
                </div>
                <div class="stat-card yellow">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Temporales</div>
                    <div class="text-2xl font-bold text-yellow-400 mt-1">{{ $blacklist->where('permanent', false)->count() }}</div>
                </div>
                <div class="stat-card purple">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Por Fail2Ban</div>
                    <div class="text-2xl font-bold text-purple-400 mt-1">{{ $blacklist->where('source', 'fail2ban')->count() }}</div>
                </div>
            </div>

            <!-- Tabla de Blacklist -->
            <div class="dark-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full dark-table">
                        <thead>
                            <tr>
                                <th class="text-left">Direccion IP</th>
                                <th class="text-left">Motivo</th>
                                <th class="text-center w-24">Origen</th>
                                <th class="text-center w-20">Intentos</th>
                                <th class="text-left w-32">Expira</th>
                                <th class="text-center w-28">Estado</th>
                                <th class="text-right w-44">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($blacklist as $entry)
                                @php
                                    $sourceLabels = [
                                        'manual' => 'Manual',
                                        'fail2ban' => 'Fail2Ban',
                                        'flood_detection' => 'Anti-Flood',
                                        'scanner' => 'Escaner',
                                    ];
                                @endphp
                                <tr>
                                    <td>
                                        <span class="font-mono text-sm bg-gray-800 px-2 py-1 rounded text-gray-200">{{ $entry->ip_address }}</span>
                                    </td>
                                    <td class="text-sm text-gray-400">{{ Str::limit($entry->reason, 40) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $entry->source === 'manual' ? 'badge-blue' : ($entry->source === 'fail2ban' ? 'badge-purple' : ($entry->source === 'flood_detection' ? 'badge-red' : 'badge-gray')) }}">
                                            {{ $sourceLabels[$entry->source] ?? ucfirst($entry->source) }}
                                        </span>
                                    </td>
                                    <td class="text-center text-gray-400 text-sm">{{ number_format($entry->attempts) }}</td>
                                    <td class="text-sm text-gray-400">
                                        @if($entry->permanent)
                                            <span class="text-red-400 font-medium">Permanente</span>
                                        @elseif($entry->expires_at)
                                            {{ \Carbon\Carbon::parse($entry->expires_at)->diffForHumans() }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($entry->permanent)
                                            <span class="badge badge-red">Permanente</span>
                                        @elseif($entry->expires_at && \Carbon\Carbon::parse($entry->expires_at)->isPast())
                                            <span class="badge badge-gray">Expirado</span>
                                        @else
                                            <span class="badge badge-yellow">Activo</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <form action="{{ route('blacklist.toggle-permanent', $entry) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-blue-400 hover:text-blue-300 text-xs">
                                                    {{ $entry->permanent ? 'Temporal' : 'Permanente' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('blacklist.destroy', $entry) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta IP de la lista negra?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-300 text-xs">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-12">
                                        <svg class="mx-auto h-12 w-12 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        <p class="text-gray-500">No hay IPs bloqueadas</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($blacklist->hasPages())
                <div class="px-4 py-3 border-t border-gray-700/50">
                    {{ $blacklist->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
