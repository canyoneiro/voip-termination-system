<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Webhooks</h2>
                <p class="text-sm text-slate-500 mt-0.5">Notificaciones HTTP automaticas para eventos del sistema</p>
            </div>
            <a href="{{ route('webhooks.create') }}" class="btn-primary inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nuevo Webhook
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700">{{ session('success') }}</div>
            @endif

            <!-- Resumen -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="stat-card blue">
                    <div class="text-xs text-slate-500 uppercase tracking-wider font-medium">Total Webhooks</div>
                    <div class="text-2xl font-bold text-slate-800 mt-1">{{ $webhooks->total() }}</div>
                </div>
                <div class="stat-card green">
                    <div class="text-xs text-slate-500 uppercase tracking-wider font-medium">Activos</div>
                    <div class="text-2xl font-bold text-green-600 mt-1">{{ $webhooks->where('active', true)->count() }}</div>
                </div>
                <div class="stat-card yellow">
                    <div class="text-xs text-slate-500 uppercase tracking-wider font-medium">Con Fallos</div>
                    <div class="text-2xl font-bold text-yellow-600 mt-1">{{ $webhooks->where('failure_count', '>', 0)->count() }}</div>
                </div>
                <div class="stat-card purple">
                    <div class="text-xs text-slate-500 uppercase tracking-wider font-medium">Globales</div>
                    <div class="text-2xl font-bold text-purple-600 mt-1">{{ $webhooks->whereNull('customer_id')->count() }}</div>
                </div>
            </div>

            <!-- Tabla de Webhooks -->
            <div class="dark-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full dark-table">
                        <thead>
                            <tr>
                                <th class="text-left">URL del Endpoint</th>
                                <th class="text-left w-32">Cliente</th>
                                <th class="text-center w-28">Eventos</th>
                                <th class="text-left w-36">Ultimo Disparo</th>
                                <th class="text-center w-28">Estado</th>
                                <th class="text-right w-40">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($webhooks as $webhook)
                                <tr>
                                    <td>
                                        <a href="{{ route('webhooks.show', $webhook) }}" class="text-blue-600 hover:text-blue-800 font-mono text-sm font-medium">
                                            {{ Str::limit($webhook->url, 55) }}
                                        </a>
                                    </td>
                                    <td class="text-sm text-slate-500">
                                        {{ $webhook->customer->name ?? 'Global' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-blue">
                                            {{ count($webhook->events ?? []) }} eventos
                                        </span>
                                    </td>
                                    <td class="text-sm text-slate-500">
                                        @if($webhook->last_triggered_at)
                                            {{ $webhook->last_triggered_at->diffForHumans() }}
                                            @if($webhook->last_status_code)
                                                <span class="ml-1 badge {{ $webhook->last_status_code < 400 ? 'badge-green' : 'badge-red' }}">
                                                    {{ $webhook->last_status_code }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-slate-400">Nunca</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($webhook->active)
                                            <span class="badge badge-green">Activo</span>
                                        @else
                                            <span class="badge badge-gray">Inactivo</span>
                                        @endif
                                        @if($webhook->failure_count > 0)
                                            <span class="ml-1 badge badge-red">{{ $webhook->failure_count }} fallos</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <form action="{{ route('webhooks.test', $webhook) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-medium">Test</button>
                                            </form>
                                            <a href="{{ route('webhooks.show', $webhook) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Ver</a>
                                            <a href="{{ route('webhooks.edit', $webhook) }}" class="text-yellow-600 hover:text-yellow-800 text-xs font-medium">Editar</a>
                                            <form action="{{ route('webhooks.destroy', $webhook) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este webhook?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-12">
                                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                        </svg>
                                        <p class="text-slate-400">No hay webhooks configurados</p>
                                        <a href="{{ route('webhooks.create') }}" class="mt-2 inline-block text-blue-600 hover:text-blue-800 font-medium">Crear el primero</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($webhooks->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">
                    {{ $webhooks->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
