<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Clientes</h2>
                <p class="text-sm text-slate-500 mt-0.5">Gestiona tus clientes y sus limites de servicio</p>
            </div>
            <a href="{{ route('customers.create') }}" class="btn-primary inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nuevo Cliente
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                                <th class="text-left">Cliente</th>
                                <th class="text-left">Empresa</th>
                                <th class="text-center w-20">IPs</th>
                                <th class="text-left w-36">Canales</th>
                                <th class="text-left w-40">Minutos Hoy</th>
                                <th class="text-center w-24">Estado</th>
                                <th class="text-right w-28">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                                @php
                                    $channelPercent = $customer->max_channels > 0 ? ($customer->active_calls_count / $customer->max_channels) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('customers.show', $customer) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                            {{ $customer->name }}
                                        </a>
                                    </td>
                                    <td class="text-slate-500">{{ $customer->company ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-blue">{{ $customer->ips_count }}</span>
                                    </td>
                                    <td>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-medium {{ $channelPercent >= 80 ? 'text-red-600' : ($channelPercent >= 50 ? 'text-yellow-600' : 'text-slate-600') }}">
                                                {{ $customer->active_calls_count }}/{{ $customer->max_channels }}
                                            </span>
                                            <div class="flex-1 h-1.5 bg-slate-200 rounded-full max-w-16">
                                                <div class="h-full rounded-full {{ $channelPercent >= 80 ? 'bg-red-500' : ($channelPercent >= 50 ? 'bg-yellow-500' : 'bg-green-500') }}" style="width: {{ min($channelPercent, 100) }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($customer->max_daily_minutes)
                                            @php $minutePercent = ($customer->used_daily_minutes / $customer->max_daily_minutes) * 100; @endphp
                                            <span class="text-sm {{ $minutePercent >= 90 ? 'text-red-600 font-bold' : ($minutePercent >= 70 ? 'text-yellow-600' : 'text-slate-600') }}">
                                                {{ number_format($customer->used_daily_minutes) }}/{{ number_format($customer->max_daily_minutes) }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-sm">Sin limite</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($customer->active)
                                            <span class="badge badge-green">Activo</span>
                                        @else
                                            <span class="badge badge-red">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="flex items-center justify-end space-x-1">
                                            <a href="{{ route('customers.show', $customer) }}" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors" title="Ver detalles">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                            <a href="{{ route('customers.edit', $customer) }}" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition-colors" title="Editar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este cliente? Esta accion no se puede deshacer.')">
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
                                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        <p class="text-slate-400">No hay clientes registrados</p>
                                        <a href="{{ route('customers.create') }}" class="text-blue-600 text-sm mt-2 inline-block hover:text-blue-800 font-medium">Crear primer cliente</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($customers->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">
                    {{ $customers->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
