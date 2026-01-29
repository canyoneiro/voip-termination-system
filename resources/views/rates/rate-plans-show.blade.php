<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Plan: {{ $ratePlan->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('rates.rate-plans') }}" class="btn-secondary text-sm">Volver</a>
                <a href="{{ route('rates.rate-plans.edit', $ratePlan) }}" class="btn-primary text-sm">Editar</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Info del plan -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="dark-card p-6">
                    <p class="text-slate-500 text-sm mb-1">Estado</p>
                    @if($ratePlan->active)
                        <span class="badge badge-green text-lg">Activo</span>
                    @else
                        <span class="badge badge-gray text-lg">Inactivo</span>
                    @endif
                </div>
                <div class="dark-card p-6">
                    <p class="text-slate-500 text-sm mb-1">Markup por defecto</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $ratePlan->default_markup_percent }}%</p>
                </div>
                <div class="dark-card p-6">
                    <p class="text-slate-500 text-sm mb-1">Descripcion</p>
                    <p class="text-slate-800">{{ $ratePlan->description ?? 'Sin descripcion' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Clientes asignados -->
                <div class="dark-card p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Clientes asignados ({{ $ratePlan->customers->count() }})</h3>
                    @if($ratePlan->customers->count() > 0)
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            @foreach($ratePlan->customers as $customer)
                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-slate-800">{{ $customer->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $customer->company ?? 'Sin empresa' }}</p>
                                    </div>
                                    <a href="{{ route('customers.show', $customer) }}" class="text-blue-600 hover:text-blue-800 text-sm">Ver</a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-500 text-center py-4">No hay clientes asignados a este plan</p>
                    @endif
                </div>

                <!-- Tarifas especiales del plan -->
                <div class="dark-card p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Tarifas especiales ({{ $ratePlan->rates->count() }})</h3>
                    @if($ratePlan->rates->count() > 0)
                        <div class="overflow-x-auto max-h-64 overflow-y-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-100">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Prefijo</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Destino</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Tarifa</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @foreach($ratePlan->rates as $rate)
                                        <tr>
                                            <td class="px-3 py-2 font-mono">{{ $rate->prefix }}</td>
                                            <td class="px-3 py-2 text-slate-600">{{ $rate->destination->description ?? '-' }}</td>
                                            <td class="px-3 py-2 text-right font-mono">${{ number_format($rate->rate_per_minute, 4) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-slate-500 text-center py-4">Este plan usa las tarifas base de carrier + markup</p>
                    @endif
                </div>
            </div>

            <!-- Simulador de precios -->
            <div class="dark-card p-6 mt-6">
                <h3 class="font-semibold text-slate-800 mb-4">Simulador de precio al cliente</h3>
                <p class="text-sm text-slate-500 mb-4">Con un markup del {{ $ratePlan->default_markup_percent }}%, asi quedarian las tarifas:</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-slate-50 rounded-lg p-4 text-center">
                        <p class="text-xs text-slate-500 mb-1">Coste carrier</p>
                        <p class="text-xl font-mono">$0.0100</p>
                        <p class="text-xs text-slate-500 mt-2">Precio cliente</p>
                        <p class="text-xl font-mono text-green-600">${{ number_format(0.01 * (1 + $ratePlan->default_markup_percent/100), 4) }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-4 text-center">
                        <p class="text-xs text-slate-500 mb-1">Coste carrier</p>
                        <p class="text-xl font-mono">$0.0200</p>
                        <p class="text-xs text-slate-500 mt-2">Precio cliente</p>
                        <p class="text-xl font-mono text-green-600">${{ number_format(0.02 * (1 + $ratePlan->default_markup_percent/100), 4) }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-4 text-center">
                        <p class="text-xs text-slate-500 mb-1">Coste carrier</p>
                        <p class="text-xl font-mono">$0.0500</p>
                        <p class="text-xs text-slate-500 mt-2">Precio cliente</p>
                        <p class="text-xl font-mono text-green-600">${{ number_format(0.05 * (1 + $ratePlan->default_markup_percent/100), 4) }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-4 text-center">
                        <p class="text-xs text-slate-500 mb-1">Coste carrier</p>
                        <p class="text-xl font-mono">$0.1000</p>
                        <p class="text-xs text-slate-500 mt-2">Precio cliente</p>
                        <p class="text-xl font-mono text-green-600">${{ number_format(0.1 * (1 + $ratePlan->default_markup_percent/100), 4) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
