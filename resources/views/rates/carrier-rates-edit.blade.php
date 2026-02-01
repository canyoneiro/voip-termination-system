<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Editar Tarifa: <span class="font-mono">{{ $rate->carrier->name ?? 'N/A' }} - {{ $rate->destinationPrefix->prefix ?? 'N/A' }}</span>
            </h2>
            <a href="{{ route('rates.carrier-rates') }}" class="btn-secondary text-sm">Cancelar</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
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
                <form method="POST" action="{{ route('rates.carrier-rates.update', $rate) }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Carrier</label>
                                <div class="input-field w-full bg-slate-100">{{ $rate->carrier->name ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Destino</label>
                                <div class="input-field w-full bg-slate-100 font-mono">{{ $rate->destinationPrefix->prefix ?? 'N/A' }} - {{ $rate->destinationPrefix->description ?? $rate->destinationPrefix->country ?? '' }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="cost_per_minute" class="block text-sm font-medium text-slate-700 mb-1">Costo por minuto *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                                    <input type="number" name="cost_per_minute" id="cost_per_minute"
                                           value="{{ old('cost_per_minute', $rate->cost_per_minute) }}" required
                                           step="0.000001" min="0"
                                           class="input-field w-full pl-7 font-mono">
                                </div>
                            </div>

                            <div>
                                <label for="connection_fee" class="block text-sm font-medium text-slate-700 mb-1">Fee conexion</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                                    <input type="number" name="connection_fee" id="connection_fee"
                                           value="{{ old('connection_fee', $rate->connection_fee) }}"
                                           step="0.000001" min="0"
                                           class="input-field w-full pl-7 font-mono">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="billing_increment" class="block text-sm font-medium text-slate-700 mb-1">Incremento facturacion *</label>
                                <select name="billing_increment" id="billing_increment" required class="input-field w-full">
                                    <option value="1" {{ old('billing_increment', $rate->billing_increment) == 1 ? 'selected' : '' }}>1 segundo</option>
                                    <option value="6" {{ old('billing_increment', $rate->billing_increment) == 6 ? 'selected' : '' }}>6 segundos</option>
                                    <option value="30" {{ old('billing_increment', $rate->billing_increment) == 30 ? 'selected' : '' }}>30 segundos</option>
                                    <option value="60" {{ old('billing_increment', $rate->billing_increment) == 60 ? 'selected' : '' }}>60 segundos</option>
                                </select>
                            </div>

                            <div>
                                <label for="min_duration" class="block text-sm font-medium text-slate-700 mb-1">Duracion minima (seg)</label>
                                <input type="number" name="min_duration" id="min_duration"
                                       value="{{ old('min_duration', $rate->min_duration) }}"
                                       min="0"
                                       class="input-field w-full">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="effective_date" class="block text-sm font-medium text-slate-700 mb-1">Fecha efectiva *</label>
                                <input type="date" name="effective_date" id="effective_date"
                                       value="{{ old('effective_date', $rate->effective_date?->format('Y-m-d')) }}" required
                                       class="input-field w-full">
                            </div>

                            <div>
                                <label for="end_date" class="block text-sm font-medium text-slate-700 mb-1">Fecha fin</label>
                                <input type="date" name="end_date" id="end_date"
                                       value="{{ old('end_date', $rate->end_date?->format('Y-m-d')) }}"
                                       class="input-field w-full">
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="active" value="1" {{ old('active', $rate->active) ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-slate-700">Tarifa activa</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <a href="{{ route('rates.carrier-rates') }}" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
