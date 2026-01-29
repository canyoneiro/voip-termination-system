<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('carriers.show', $carrier) }}" class="text-gray-400 hover:text-white mr-3 p-1 hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-white">Editar: {{ $carrier->name }}</h2>
                <p class="text-sm text-gray-400 mt-0.5">Modifica la configuracion del carrier</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dark-card">
                <form action="{{ route('carriers.update', $carrier) }}" method="POST" class="p-6">
                    @csrf @method('PUT')

                    <!-- Estado -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Estado del Carrier</h3>
                        <div>
                            <label for="state" class="block text-sm font-medium text-gray-300">Estado *</label>
                            <select name="state" id="state" required class="dark-select mt-1 w-full md:w-1/2 py-2 px-3">
                                <option value="active" {{ old('state', $carrier->state) === 'active' ? 'selected' : '' }}>Activo - Recibiendo trafico</option>
                                <option value="inactive" {{ old('state', $carrier->state) === 'inactive' ? 'selected' : '' }}>Inactivo - Sin trafico</option>
                                <option value="probing" {{ old('state', $carrier->state) === 'probing' ? 'selected' : '' }}>En pruebas - Verificando</option>
                                <option value="disabled" {{ old('state', $carrier->state) === 'disabled' ? 'selected' : '' }}>Deshabilitado - Fuera de servicio</option>
                            </select>
                        </div>
                    </div>

                    <!-- Conexion -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Configuracion de Conexion</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-300">Nombre *</label>
                                <input type="text" name="name" id="name" required value="{{ old('name', $carrier->name) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                            <div>
                                <label for="host" class="block text-sm font-medium text-gray-300">Host *</label>
                                <input type="text" name="host" id="host" required value="{{ old('host', $carrier->host) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                            <div>
                                <label for="port" class="block text-sm font-medium text-gray-300">Puerto *</label>
                                <input type="number" name="port" id="port" required min="1" max="65535" value="{{ old('port', $carrier->port) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                            <div>
                                <label for="transport" class="block text-sm font-medium text-gray-300">Transporte *</label>
                                <select name="transport" id="transport" required class="dark-select mt-1 w-full py-2 px-3">
                                    <option value="udp" {{ old('transport', $carrier->transport) === 'udp' ? 'selected' : '' }}>UDP</option>
                                    <option value="tcp" {{ old('transport', $carrier->transport) === 'tcp' ? 'selected' : '' }}>TCP</option>
                                    <option value="tls" {{ old('transport', $carrier->transport) === 'tls' ? 'selected' : '' }}>TLS</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Prioridad y Peso -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Prioridad y Balanceo</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-300">Prioridad *</label>
                                <input type="number" name="priority" id="priority" required min="1" max="100" value="{{ old('priority', $carrier->priority) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                                <p class="mt-1 text-xs text-gray-500">Menor numero = mayor prioridad</p>
                            </div>
                            <div>
                                <label for="weight" class="block text-sm font-medium text-gray-300">Peso *</label>
                                <input type="number" name="weight" id="weight" required min="1" max="100" value="{{ old('weight', $carrier->weight) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                        </div>
                    </div>

                    <!-- Limites -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Limites de Capacidad</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="max_channels" class="block text-sm font-medium text-gray-300">Canales Maximos *</label>
                                <input type="number" name="max_channels" id="max_channels" required min="1" value="{{ old('max_channels', $carrier->max_channels) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                            <div>
                                <label for="max_cps" class="block text-sm font-medium text-gray-300">CPS Maximo *</label>
                                <input type="number" name="max_cps" id="max_cps" required min="1" value="{{ old('max_cps', $carrier->max_cps) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                        </div>
                    </div>

                    <!-- Manipulacion -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Manipulacion de Llamadas</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="codecs" class="block text-sm font-medium text-gray-300">Codecs</label>
                                <input type="text" name="codecs" id="codecs" value="{{ old('codecs', $carrier->codecs) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                            <div>
                                <label for="tech_prefix" class="block text-sm font-medium text-gray-300">Prefijo Tecnico</label>
                                <input type="text" name="tech_prefix" id="tech_prefix" value="{{ old('tech_prefix', $carrier->tech_prefix) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                            <div>
                                <label for="strip_digits" class="block text-sm font-medium text-gray-300">Digitos a Eliminar *</label>
                                <input type="number" name="strip_digits" id="strip_digits" required min="0" max="20" value="{{ old('strip_digits', $carrier->strip_digits) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                        </div>
                    </div>

                    <!-- Filtros de Prefijos -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Filtros de Prefijos</h3>
                        <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4 mb-4">
                            <h4 class="text-sm font-medium text-blue-400 mb-2">Como usar los filtros de prefijos</h4>
                            <ul class="text-xs text-gray-400 space-y-1">
                                <li><strong class="text-gray-300">Formato:</strong> Un prefijo por linea (usar Enter para separar)</li>
                                <li><strong class="text-gray-300">Wildcards:</strong> Usar <code class="bg-gray-700 px-1 rounded text-blue-300">*</code> al final para coincidir con cualquier digito. Ej: <code class="bg-gray-700 px-1 rounded text-blue-300">34*</code> coincide con 34, 341, 34123, etc.</li>
                                <li><strong class="text-gray-300">Exacto:</strong> Sin <code class="bg-gray-700 px-1 rounded text-blue-300">*</code> coincide exactamente. Ej: <code class="bg-gray-700 px-1 rounded text-blue-300">34</code> solo coincide con el numero 34</li>
                                <li><strong class="text-gray-300">Logica:</strong> Si hay prefijos permitidos, SOLO esos destinos se enrutan. Si hay denegados, esos se bloquean.</li>
                            </ul>
                            <div class="mt-2 text-xs text-blue-400">
                                <strong>Ejemplos:</strong> 34* (España), 1* (USA/Canada), 52* (Mexico), 57* (Colombia)
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="prefix_filter" class="block text-sm font-medium text-gray-300">Prefijos Permitidos</label>
                                <textarea name="prefix_filter" id="prefix_filter" rows="5" placeholder="34*&#10;1*&#10;52*"
                                    class="dark-input mt-1 w-full py-2 px-3 font-mono text-sm">{{ old('prefix_filter', $carrier->prefix_filter) }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">Dejar vacio para permitir todos los destinos</p>
                            </div>
                            <div>
                                <label for="prefix_deny" class="block text-sm font-medium text-gray-300">Prefijos Denegados</label>
                                <textarea name="prefix_deny" id="prefix_deny" rows="5" placeholder="900*&#10;803*&#10;807*"
                                    class="dark-input mt-1 w-full py-2 px-3 font-mono text-sm">{{ old('prefix_deny', $carrier->prefix_deny) }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">Bloquear destinos caros o no deseados</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="mb-8">
                        <label for="notes" class="block text-sm font-medium text-gray-300">Notas Internas</label>
                        <textarea name="notes" id="notes" rows="2"
                            class="dark-input mt-1 w-full py-2 px-3">{{ old('notes', $carrier->notes) }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-700/50">
                        <a href="{{ route('carriers.show', $carrier) }}" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
