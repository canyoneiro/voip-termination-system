<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Editar Regla: {{ $rule->name }}
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
                <form method="POST" action="{{ route('fraud.rules.update', $rule) }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nombre de la regla *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $rule->name) }}" required
                                   class="input-field w-full">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de regla</label>
                            <div class="input-field w-full bg-slate-100">{{ $types[$rule->type] ?? $rule->type }}</div>
                            <p class="mt-1 text-xs text-slate-500">El tipo no se puede modificar</p>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Descripcion</label>
                            <textarea name="description" id="description" rows="2"
                                      class="input-field w-full">{{ old('description', $rule->description) }}</textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="threshold" class="block text-sm font-medium text-slate-700 mb-1">Umbral</label>
                                <input type="number" name="threshold" id="threshold"
                                       value="{{ old('threshold', $rule->threshold) }}"
                                       step="0.01" class="input-field w-full">
                            </div>

                            <div>
                                <label for="cooldown_minutes" class="block text-sm font-medium text-slate-700 mb-1">Cooldown (minutos) *</label>
                                <input type="number" name="cooldown_minutes" id="cooldown_minutes"
                                       value="{{ old('cooldown_minutes', $rule->cooldown_minutes) }}" required min="1"
                                       class="input-field w-full">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="action" class="block text-sm font-medium text-slate-700 mb-1">Accion *</label>
                                <select name="action" id="action" required class="input-field w-full">
                                    <option value="log" {{ old('action', $rule->action) == 'log' ? 'selected' : '' }}>Solo registrar</option>
                                    <option value="alert" {{ old('action', $rule->action) == 'alert' ? 'selected' : '' }}>Generar alerta</option>
                                    <option value="throttle" {{ old('action', $rule->action) == 'throttle' ? 'selected' : '' }}>Limitar trafico</option>
                                    <option value="block" {{ old('action', $rule->action) == 'block' ? 'selected' : '' }}>Bloquear</option>
                                </select>
                            </div>

                            <div>
                                <label for="severity" class="block text-sm font-medium text-slate-700 mb-1">Severidad *</label>
                                <select name="severity" id="severity" required class="input-field w-full">
                                    <option value="low" {{ old('severity', $rule->severity) == 'low' ? 'selected' : '' }}>Baja</option>
                                    <option value="medium" {{ old('severity', $rule->severity) == 'medium' ? 'selected' : '' }}>Media</option>
                                    <option value="high" {{ old('severity', $rule->severity) == 'high' ? 'selected' : '' }}>Alta</option>
                                    <option value="critical" {{ old('severity', $rule->severity) == 'critical' ? 'selected' : '' }}>Critica</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="customer_id" class="block text-sm font-medium text-slate-700 mb-1">Aplicar a cliente (opcional)</label>
                            <select name="customer_id" id="customer_id" class="input-field w-full">
                                <option value="">Todos los clientes (Global)</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id', $rule->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="active" value="1" {{ old('active', $rule->active) ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-slate-700">Regla activa</span>
                            </label>
                        </div>

                        <!-- Stats de la regla -->
                        <div class="border-t border-slate-200 pt-4">
                            <h4 class="font-medium text-slate-700 mb-3">Estadisticas de la regla</h4>
                            <div class="grid grid-cols-3 gap-4 text-sm">
                                <div class="bg-slate-50 rounded-lg p-3 text-center">
                                    <p class="text-2xl font-bold text-slate-800">{{ number_format($rule->trigger_count) }}</p>
                                    <p class="text-slate-500">Disparos totales</p>
                                </div>
                                <div class="bg-slate-50 rounded-lg p-3 text-center">
                                    <p class="text-sm text-slate-800">{{ $rule->last_triggered_at?->format('d/m/Y H:i') ?? 'Nunca' }}</p>
                                    <p class="text-slate-500">Ultimo disparo</p>
                                </div>
                                <div class="bg-slate-50 rounded-lg p-3 text-center">
                                    <p class="text-sm text-slate-800">{{ $rule->created_at->format('d/m/Y') }}</p>
                                    <p class="text-slate-500">Creada</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <a href="{{ route('fraud.rules') }}" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
