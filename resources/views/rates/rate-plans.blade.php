<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Planes de Tarifas
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('rates.index') }}" class="btn-secondary text-sm">Volver</a>
                <a href="{{ route('rates.rate-plans.create') }}" class="btn-primary text-sm">Nuevo Plan</a>
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
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Grid de planes -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($plans as $plan)
                    <div class="dark-card p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-lg text-slate-800">{{ $plan->name }}</h3>
                                @if($plan->active)
                                    <span class="badge badge-green">Activo</span>
                                @else
                                    <span class="badge badge-gray">Inactivo</span>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-blue-600">{{ $plan->default_markup_percent }}%</p>
                                <p class="text-xs text-slate-500">Markup</p>
                            </div>
                        </div>

                        @if($plan->description)
                            <p class="text-sm text-slate-600 mb-4">{{ $plan->description }}</p>
                        @endif

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="bg-slate-50 rounded-lg p-3 text-center">
                                <p class="text-xl font-bold text-slate-800">{{ $plan->customers_count }}</p>
                                <p class="text-xs text-slate-500">Clientes</p>
                            </div>
                            <div class="bg-slate-50 rounded-lg p-3 text-center">
                                <p class="text-xl font-bold text-slate-800">{{ $plan->rates_count }}</p>
                                <p class="text-xs text-slate-500">Tarifas</p>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('rates.rate-plans.show', $plan) }}" class="btn-secondary text-xs flex-1 text-center">Ver</a>
                            <a href="{{ route('rates.rate-plans.edit', $plan) }}" class="btn-secondary text-xs flex-1 text-center">Editar</a>
                            @if($plan->customers_count == 0)
                                <form method="POST" action="{{ route('rates.rate-plans.destroy', $plan) }}" class="flex-1" onsubmit="return confirm('Eliminar este plan?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger text-xs w-full">Eliminar</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full dark-card p-12 text-center">
                        <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p class="text-slate-500 mb-4">No hay planes de tarifas</p>
                        <a href="{{ route('rates.rate-plans.create') }}" class="btn-primary">Crear primer plan</a>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $plans->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
