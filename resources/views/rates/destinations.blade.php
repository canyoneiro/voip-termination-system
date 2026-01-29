<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Destinos
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('rates.index') }}" class="btn-secondary text-sm">Volver</a>
                <a href="{{ route('rates.destinations.create') }}" class="btn-primary text-sm">Nuevo Destino</a>
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

            <!-- Search -->
            <div class="dark-card p-4 mb-6">
                <form method="GET" action="{{ route('rates.destinations') }}" class="flex gap-4">
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Buscar por prefijo, pais o descripcion..."
                               class="input-field w-full">
                    </div>
                    <button type="submit" class="btn-primary">Buscar</button>
                    @if(request('search'))
                        <a href="{{ route('rates.destinations') }}" class="btn-secondary">Limpiar</a>
                    @endif
                </form>
            </div>

            <!-- Table -->
            <div class="dark-card overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Prefijo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Pais</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Region</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Descripcion</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($destinations as $destination)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono font-semibold text-slate-800">{{ $destination->prefix }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    {{ $destination->country ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    {{ $destination->region ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $destination->description ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($destination->is_premium)
                                        <span class="badge badge-red">Premium</span>
                                    @elseif($destination->is_mobile)
                                        <span class="badge badge-blue">Movil</span>
                                    @else
                                        <span class="badge badge-gray">Fijo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('rates.destinations.edit', $destination) }}" class="text-blue-600 hover:text-blue-800 mr-3">Editar</a>
                                    <form method="POST" action="{{ route('rates.destinations.destroy', $destination) }}" class="inline" onsubmit="return confirm('Eliminar este destino?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    No hay destinos registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $destinations->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
