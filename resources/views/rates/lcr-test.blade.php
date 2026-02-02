<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Calculadora de Costos
            </h2>
            <a href="{{ route('rates.index') }}" class="btn-secondary text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- AVISO IMPORTANTE -->
            <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-400 rounded-r-lg">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-blue-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h3 class="font-bold text-blue-800">Esta herramienta es solo informativa</h3>
                        <p class="text-blue-700 text-sm mt-1">
                            Muestra el <strong>costo y precio</strong> que se aplicaria a una llamada, pero <strong>NO determina el enrutamiento</strong>.
                        </p>
                        <p class="text-blue-600 text-sm mt-1">
                            Las llamadas se enrutan por <strong>prioridad del carrier</strong>, no por costo.
                            Para cambiar por donde salen las llamadas, ajusta las prioridades en <a href="{{ route('carriers.index') }}" class="underline font-medium">Carriers</a>.
                        </p>
                    </div>
                </div>
            </div>

            <div class="dark-card p-6 mb-6">
                <h3 class="font-semibold text-slate-800 mb-4">Consultar Costo por Destino</h3>
                <form method="POST" action="{{ route('rates.lcr-lookup') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Numero de Destino</label>
                        <input type="text" name="number" value="{{ $number ?? '' }}" required
                               class="dark-input w-full" placeholder="ej: 34612345678">
                        <p class="text-xs text-slate-500 mt-1">Introduzca el numero completo con prefijo de pais</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Cliente (opcional)</label>
                        <select name="customer_id" class="dark-select w-full">
                            <option value="">-- Sin cliente (usar tarifas base) --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ isset($customer) && $customer?->id == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">Buscar Ruta</button>
                </form>
            </div>

            @if(isset($result))
                <div class="dark-card p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Resultado LCR</h3>

                    @if($result['success'])
                        <div class="space-y-4">
                            <!-- Destination Info -->
                            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <h4 class="font-medium text-blue-800 mb-2">Destino Detectado</h4>
                                <dl class="grid grid-cols-2 gap-2 text-sm">
                                    <dt class="text-blue-600">Prefijo:</dt>
                                    <dd class="font-mono text-blue-800">{{ $result['destination']['prefix'] ?? '-' }}</dd>
                                    <dt class="text-blue-600">Pais:</dt>
                                    <dd class="text-blue-800">{{ $result['destination']['country'] ?? '-' }}</dd>
                                    <dt class="text-blue-600">Descripcion:</dt>
                                    <dd class="text-blue-800">{{ $result['destination']['description'] ?? '-' }}</dd>
                                </dl>
                            </div>

                            <!-- Selected Carrier -->
                            @if(isset($result['carrier']))
                                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <h4 class="font-medium text-green-800 mb-2">Carrier Seleccionado</h4>
                                    <dl class="grid grid-cols-2 gap-2 text-sm">
                                        <dt class="text-green-600">Carrier:</dt>
                                        <dd class="font-medium text-green-800">{{ $result['carrier']['name'] }}</dd>
                                        <dt class="text-green-600">Host:</dt>
                                        <dd class="font-mono text-green-800">{{ $result['carrier']['host'] }}</dd>
                                        <dt class="text-green-600">Prioridad:</dt>
                                        <dd class="text-green-800">{{ $result['carrier']['priority'] }}</dd>
                                        <dt class="text-green-600">Costo/min:</dt>
                                        <dd class="font-medium text-green-800">${{ number_format($result['rate']['cost'] ?? 0, 4) }}</dd>
                                    </dl>
                                </div>
                            @endif

                            <!-- Pricing -->
                            @if(isset($result['pricing']))
                                <div class="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                                    <h4 class="font-medium text-purple-800 mb-2">Precios</h4>
                                    <dl class="grid grid-cols-2 gap-2 text-sm">
                                        <dt class="text-purple-600">Costo Carrier:</dt>
                                        <dd class="text-purple-800">${{ number_format($result['pricing']['cost'], 4) }}/min</dd>
                                        <dt class="text-purple-600">Precio Cliente:</dt>
                                        <dd class="font-medium text-purple-800">${{ number_format($result['pricing']['price'], 4) }}/min</dd>
                                        <dt class="text-purple-600">Margen:</dt>
                                        <dd class="text-purple-800">{{ number_format($result['pricing']['margin_percent'], 1) }}%</dd>
                                    </dl>
                                </div>
                            @endif

                            <!-- Available Routes -->
                            @if(isset($result['available_routes']) && count($result['available_routes']) > 1)
                                <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg">
                                    <h4 class="font-medium text-slate-800 mb-2">Rutas Alternativas</h4>
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="text-slate-500">
                                                <th class="text-left py-1">Carrier</th>
                                                <th class="text-left py-1">Prioridad</th>
                                                <th class="text-right py-1">Costo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($result['available_routes'] as $route)
                                                <tr class="border-t border-slate-200">
                                                    <td class="py-1">{{ $route['carrier_name'] }}</td>
                                                    <td class="py-1">{{ $route['priority'] }}</td>
                                                    <td class="py-1 text-right font-mono">${{ number_format($route['rate'], 4) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                            <h4 class="font-medium text-red-800 mb-2">Sin Ruta Disponible</h4>
                            <p class="text-sm text-red-600">{{ $result['message'] ?? 'No se encontro ninguna ruta para este destino.' }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
