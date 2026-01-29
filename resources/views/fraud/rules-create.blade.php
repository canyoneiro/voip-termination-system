<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Nueva Regla de Fraude
            </h2>
            <a href="{{ route('fraud.rules') }}" class="btn-secondary text-sm">Cancelar</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="dark-card p-6">
                <form method="POST" action="{{ route('fraud.rules.store') }}">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nombre de la regla *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="input-field w-full" placeholder="Detectar llamadas premium internacionales">
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-slate-700 mb-1">Tipo de regla *</label>
                            <select name="type" id="type" required class="input-field w-full">
                                <option value="">Seleccionar tipo...</option>
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Descripcion</label>
                            <textarea name="description" id="description" rows="2"
                                      class="input-field w-full" placeholder="Descripcion de lo que detecta esta regla...">{{ old('description') }}</textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="threshold" class="block text-sm font-medium text-slate-700 mb-1">Umbral</label>
                                <input type="number" name="threshold" id="threshold" value="{{ old('threshold') }}"
                                       step="0.01" class="input-field w-full" placeholder="10">
                                <p class="mt-1 text-xs text-slate-500">Valor numerico para activar la regla</p>
                            </div>

                            <div>
                                <label for="cooldown_minutes" class="block text-sm font-medium text-slate-700 mb-1">Cooldown (minutos) *</label>
                                <input type="number" name="cooldown_minutes" id="cooldown_minutes"
                                       value="{{ old('cooldown_minutes', 60) }}" required min="1"
                                       class="input-field w-full">
                                <p class="mt-1 text-xs text-slate-500">Tiempo entre alertas</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="action" class="block text-sm font-medium text-slate-700 mb-1">Accion *</label>
                                <select name="action" id="action" required class="input-field w-full">
                                    <option value="log" {{ old('action') == 'log' ? 'selected' : '' }}>Solo registrar</option>
                                    <option value="alert" {{ old('action', 'alert') == 'alert' ? 'selected' : '' }}>Generar alerta</option>
                                    <option value="throttle" {{ old('action') == 'throttle' ? 'selected' : '' }}>Limitar trafico</option>
                                    <option value="block" {{ old('action') == 'block' ? 'selected' : '' }}>Bloquear</option>
                                </select>
                            </div>

                            <div>
                                <label for="severity" class="block text-sm font-medium text-slate-700 mb-1">Severidad *</label>
                                <select name="severity" id="severity" required class="input-field w-full">
                                    <option value="low" {{ old('severity') == 'low' ? 'selected' : '' }}>Baja</option>
                                    <option value="medium" {{ old('severity', 'medium') == 'medium' ? 'selected' : '' }}>Media</option>
                                    <option value="high" {{ old('severity') == 'high' ? 'selected' : '' }}>Alta</option>
                                    <option value="critical" {{ old('severity') == 'critical' ? 'selected' : '' }}>Critica</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="customer_id" class="block text-sm font-medium text-slate-700 mb-1">Aplicar a cliente (opcional)</label>
                            <select name="customer_id" id="customer_id" class="input-field w-full">
                                <option value="">Todos los clientes (Global)</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-slate-700">Regla activa</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <a href="{{ route('fraud.rules') }}" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary">Crear Regla</button>
                    </div>
                </form>
            </div>

            <!-- Ayuda sobre tipos -->
            <div class="mt-6 dark-card p-6">
                <h3 class="font-semibold text-slate-800 mb-4">Tipos de reglas disponibles</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="p-3 bg-slate-50 rounded-lg">
                        <p class="font-medium text-slate-800">Destino de Alto Costo</p>
                        <p class="text-slate-600">Detecta llamadas a destinos premium o caros</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg">
                        <p class="font-medium text-slate-800">Pico de Trafico</p>
                        <p class="text-slate-600">Alerta cuando el trafico supera lo normal</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg">
                        <p class="font-medium text-slate-800">Wangiri</p>
                        <p class="text-slate-600">Detecta llamadas muy cortas (callback fraud)</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg">
                        <p class="font-medium text-slate-800">Alta Tasa de Fallos</p>
                        <p class="text-slate-600">Alerta cuando muchas llamadas fallan</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg">
                        <p class="font-medium text-slate-800">Trafico Fuera de Horario</p>
                        <p class="text-slate-600">Detecta actividad en horas inusuales</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg">
                        <p class="font-medium text-slate-800">Consumo Acelerado</p>
                        <p class="text-slate-600">Detecta consumo rapido de minutos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
