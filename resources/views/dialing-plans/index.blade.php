<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Dialing Plans</h2>
                <p class="text-sm text-slate-500 mt-0.5">Restriccion de destinos por cliente</p>
            </div>
            <a href="{{ route('dialing-plans.create') }}" class="btn-primary inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nuevo Plan
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

            @if($dialingPlans->isEmpty())
                <div class="dark-card p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-slate-700 mb-2">Sin Dialing Plans</h3>
                    <p class="text-slate-500 mb-4">Crea tu primer plan de marcacion para restringir destinos por cliente.</p>
                    <a href="{{ route('dialing-plans.create') }}" class="btn-primary">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Crear Dialing Plan
                    </a>
                </div>
            @else
                <div class="dark-card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full dark-table">
                            <thead>
                                <tr>
                                    <th class="text-left">Nombre</th>
                                    <th class="text-center w-32">Default</th>
                                    <th class="text-center w-32">Premium</th>
                                    <th class="text-center w-20">Reglas</th>
                                    <th class="text-center w-24">Clientes</th>
                                    <th class="text-center w-24">Estado</th>
                                    <th class="text-center w-32">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dialingPlans as $plan)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td>
                                        <a href="{{ route('dialing-plans.show', $plan) }}" class="font-semibold text-blue-600 hover:text-blue-800">
                                            {{ $plan->name }}
                                        </a>
                                        @if($plan->description)
                                            <p class="text-xs text-slate-500 mt-0.5">{{ Str::limit($plan->description, 50) }}</p>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $plan->default_action === 'allow' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ strtoupper($plan->default_action) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($plan->block_premium)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">Bloqueado</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-600">Permitido</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-mono">{{ $plan->rules_count }}</td>
                                    <td class="text-center font-mono">{{ $plan->customers_count }}</td>
                                    <td class="text-center">
                                        @if($plan->active)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Activo</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-600">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <a href="{{ route('dialing-plans.show', $plan) }}" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors" title="Ver">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                            <a href="{{ route('dialing-plans.edit', $plan) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded transition-colors" title="Editar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                            <form action="{{ route('dialing-plans.clone', $plan) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-purple-600 hover:bg-purple-50 rounded transition-colors" title="Clonar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    {{ $dialingPlans->links() }}
                </div>
            @endif

            <!-- Info Card -->
            <div class="mt-6 dark-card p-6">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Como funcionan los Dialing Plans</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <div>
                            <p class="font-medium text-slate-700">Reglas ALLOW</p>
                            <p class="text-slate-500 text-xs">Permiten marcar a destinos especificos</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </div>
                        <div>
                            <p class="font-medium text-slate-700">Reglas DENY</p>
                            <p class="text-slate-500 text-xs">Bloquean destinos especificos</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                        </div>
                        <div>
                            <p class="font-medium text-slate-700">Prioridad</p>
                            <p class="text-slate-500 text-xs">Menor numero = se evalua primero</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
