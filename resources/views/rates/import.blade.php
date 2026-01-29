<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Importar Tarifas
            </h2>
            <a href="{{ route('rates.index') }}" class="btn-secondary text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
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

            <div class="dark-card p-6">
                <form method="POST" action="{{ route('rates.import.process') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <label for="type" class="block text-sm font-medium text-slate-700 mb-1">Tipo de importacion *</label>
                            <select name="type" id="type" required class="input-field w-full" onchange="toggleCarrierSelect()">
                                <option value="">Seleccionar...</option>
                                <option value="destinations" {{ old('type') == 'destinations' ? 'selected' : '' }}>Destinos (prefijos)</option>
                                <option value="carrier_rates" {{ old('type') == 'carrier_rates' ? 'selected' : '' }}>Tarifas de carrier</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="carrier_select" class="hidden">
                            <label for="carrier_id" class="block text-sm font-medium text-slate-700 mb-1">Carrier *</label>
                            <select name="carrier_id" id="carrier_id" class="input-field w-full">
                                <option value="">Seleccionar carrier...</option>
                                @foreach($carriers as $carrier)
                                    <option value="{{ $carrier->id }}" {{ old('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                        {{ $carrier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('carrier_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="file" class="block text-sm font-medium text-slate-700 mb-1">Archivo CSV *</label>
                            <input type="file" name="file" id="file" required accept=".csv,.txt"
                                   class="input-field w-full">
                            @error('file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-slate-500">Maximo 10MB. Formato CSV con separador coma o punto y coma.</p>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <a href="{{ route('rates.index') }}" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary">Importar</button>
                    </div>
                </form>
            </div>

            <!-- Formato esperado -->
            <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="dark-card p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Formato: Destinos</h3>
                    <p class="text-sm text-slate-600 mb-3">El archivo debe tener las siguientes columnas:</p>
                    <div class="bg-slate-800 text-slate-100 rounded-lg p-4 font-mono text-xs overflow-x-auto">
                        <p class="text-slate-400"># Cabecera (opcional)</p>
                        <p>prefix,country,region,description,is_mobile,is_premium</p>
                        <p class="text-slate-400 mt-2"># Ejemplo</p>
                        <p>34,Spain,,,0,0</p>
                        <p>346,Spain,,Mobile,1,0</p>
                        <p>3490,Spain,,Premium,0,1</p>
                    </div>
                </div>

                <div class="dark-card p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Formato: Tarifas Carrier</h3>
                    <p class="text-sm text-slate-600 mb-3">El archivo debe tener las siguientes columnas:</p>
                    <div class="bg-slate-800 text-slate-100 rounded-lg p-4 font-mono text-xs overflow-x-auto">
                        <p class="text-slate-400"># Cabecera (opcional)</p>
                        <p>prefix,rate_per_minute,connection_fee,billing_increment</p>
                        <p class="text-slate-400 mt-2"># Ejemplo</p>
                        <p>34,0.0100,0,1</p>
                        <p>346,0.0150,0,1</p>
                        <p>3490,0.5000,0.1,60</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleCarrierSelect() {
            const type = document.getElementById('type').value;
            const carrierSelect = document.getElementById('carrier_select');
            if (type === 'carrier_rates') {
                carrierSelect.classList.remove('hidden');
            } else {
                carrierSelect.classList.add('hidden');
            }
        }
        // Ejecutar al cargar por si hay valor preseleccionado
        toggleCarrierSelect();
    </script>
</x-app-layout>
