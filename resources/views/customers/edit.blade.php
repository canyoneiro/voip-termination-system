<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('customers.show', $customer) }}" class="text-gray-400 hover:text-white mr-3 p-1 hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-white">Editar: {{ $customer->name }}</h2>
                <p class="text-sm text-gray-400 mt-0.5">Modifica la configuracion del cliente</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dark-card">
                <form action="{{ route('customers.update', $customer) }}" method="POST" class="p-6">
                    @csrf @method('PUT')

                    <!-- Estado -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between p-4 bg-gray-800/50 rounded-lg">
                            <div>
                                <h3 class="text-sm font-semibold text-white">Estado del Cliente</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Activa o desactiva este cliente para recibir trafico</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="active" value="0">
                                <input type="checkbox" name="active" value="1" class="sr-only peer" {{ $customer->active ? 'checked' : '' }}>
                                <div class="w-14 h-7 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-500"></div>
                                <span class="ml-3 text-sm font-medium {{ $customer->active ? 'text-green-400' : 'text-gray-400' }}">{{ $customer->active ? 'Activo' : 'Inactivo' }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Informacion basica -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Informacion Basica</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-300">Nombre *</label>
                                <input type="text" name="name" id="name" required value="{{ old('name', $customer->name) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                                @error('name')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="company" class="block text-sm font-medium text-gray-300">Empresa</label>
                                <input type="text" name="company" id="company" value="{{ old('company', $customer->company) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $customer->email) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-300">Telefono</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $customer->phone) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                        </div>
                    </div>

                    <!-- Planes -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Planes Asignados</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="rate_plan_id" class="block text-sm font-medium text-gray-300">Plan de Tarifas</label>
                                <select name="rate_plan_id" id="rate_plan_id" class="dark-input mt-1 w-full py-2 px-3">
                                    <option value="">Sin plan de tarifas</option>
                                    @foreach(\App\Models\RatePlan::where('active', true)->orderBy('name')->get() as $ratePlan)
                                        <option value="{{ $ratePlan->id }}" {{ old('rate_plan_id', $customer->rate_plan_id) == $ratePlan->id ? 'selected' : '' }}>
                                            {{ $ratePlan->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Define los precios de venta para este cliente</p>
                            </div>
                            <div>
                                <label for="dialing_plan_id" class="block text-sm font-medium text-gray-300">Plan de Marcacion</label>
                                <select name="dialing_plan_id" id="dialing_plan_id" class="dark-input mt-1 w-full py-2 px-3">
                                    <option value="">Sin restricciones</option>
                                    @foreach(\App\Models\DialingPlan::where('active', true)->orderBy('name')->get() as $dialingPlan)
                                        <option value="{{ $dialingPlan->id }}" {{ old('dialing_plan_id', $customer->dialing_plan_id) == $dialingPlan->id ? 'selected' : '' }}>
                                            {{ $dialingPlan->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Restringe los destinos que puede marcar</p>
                            </div>
                        </div>
                    </div>

                    <!-- Formato de Numeracion -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">
                            Formato de Numeracion
                            <span class="ml-2 text-xs font-normal text-blue-400 cursor-help" title="Click para mas informacion" onclick="document.getElementById('number-format-help').classList.toggle('hidden')">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Ayuda
                            </span>
                        </h3>

                        <!-- Panel de Ayuda -->
                        <div id="number-format-help" class="hidden mb-6 p-4 bg-blue-900/30 border border-blue-700/50 rounded-lg">
                            <h4 class="text-sm font-semibold text-blue-300 mb-3">Como funciona la normalizacion de numeros</h4>
                            <div class="space-y-4 text-sm text-gray-300">
                                <div>
                                    <p class="font-medium text-white mb-1">Deteccion Automatica (Recomendado)</p>
                                    <p class="text-gray-400">El sistema detecta automaticamente el formato del numero recibido:</p>
                                    <ul class="mt-2 ml-4 space-y-1 text-xs">
                                        <li><code class="bg-gray-800 px-1 rounded">666123456</code> → <code class="bg-green-900/50 px-1 rounded text-green-300">34666123456</code> <span class="text-gray-500">(detectado como nacional)</span></li>
                                        <li><code class="bg-gray-800 px-1 rounded">34666123456</code> → <code class="bg-green-900/50 px-1 rounded text-green-300">34666123456</code> <span class="text-gray-500">(ya es internacional)</span></li>
                                        <li><code class="bg-gray-800 px-1 rounded">+34666123456</code> → <code class="bg-green-900/50 px-1 rounded text-green-300">34666123456</code> <span class="text-gray-500">(internacional con +)</span></li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="font-medium text-white mb-1">Internacional (E.164)</p>
                                    <p class="text-gray-400">El cliente siempre envia numeros con codigo de pais. Acepta con o sin el signo +.</p>
                                    <ul class="mt-2 ml-4 space-y-1 text-xs">
                                        <li><code class="bg-gray-800 px-1 rounded">34666123456</code> → <code class="bg-green-900/50 px-1 rounded text-green-300">34666123456</code></li>
                                        <li><code class="bg-gray-800 px-1 rounded">+34666123456</code> → <code class="bg-green-900/50 px-1 rounded text-green-300">34666123456</code></li>
                                        <li><code class="bg-gray-800 px-1 rounded">1234567890</code> → <code class="bg-green-900/50 px-1 rounded text-green-300">1234567890</code> <span class="text-gray-500">(se asume internacional)</span></li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="font-medium text-white mb-1">Nacional Espana</p>
                                    <p class="text-gray-400">El cliente envia numeros en formato nacional espanol (9 digitos). Se anade automaticamente el prefijo 34.</p>
                                    <ul class="mt-2 ml-4 space-y-1 text-xs">
                                        <li><code class="bg-gray-800 px-1 rounded">666123456</code> → <code class="bg-green-900/50 px-1 rounded text-green-300">34666123456</code></li>
                                        <li><code class="bg-gray-800 px-1 rounded">911234567</code> → <code class="bg-green-900/50 px-1 rounded text-green-300">34911234567</code></li>
                                        <li><code class="bg-gray-800 px-1 rounded">34666123456</code> → <code class="bg-green-900/50 px-1 rounded text-green-300">34666123456</code> <span class="text-gray-500">(ya tiene prefijo)</span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-blue-700/50">
                                <p class="text-xs text-blue-400">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                                    La normalizacion se aplica antes del enrutamiento LCR y la verificacion del plan de marcacion, asegurando que los numeros siempre se procesen en formato E.164.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="number_format" class="block text-sm font-medium text-gray-300">Formato de Entrada</label>
                                <select name="number_format" id="number_format" class="dark-input mt-1 w-full py-2 px-3">
                                    <option value="auto" {{ old('number_format', $customer->number_format) == 'auto' ? 'selected' : '' }}>
                                        Deteccion Automatica (Recomendado)
                                    </option>
                                    <option value="international" {{ old('number_format', $customer->number_format) == 'international' ? 'selected' : '' }}>
                                        Internacional (E.164)
                                    </option>
                                    <option value="national_es" {{ old('number_format', $customer->number_format) == 'national_es' ? 'selected' : '' }}>
                                        Nacional Espana (9 digitos)
                                    </option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Como envia el cliente los numeros destino</p>
                            </div>
                            <div>
                                <label for="default_country_code" class="block text-sm font-medium text-gray-300">Codigo de Pais por Defecto</label>
                                <select name="default_country_code" id="default_country_code" class="dark-input mt-1 w-full py-2 px-3">
                                    <option value="34" {{ old('default_country_code', $customer->default_country_code) == '34' ? 'selected' : '' }}>34 - Espana</option>
                                    <option value="351" {{ old('default_country_code', $customer->default_country_code) == '351' ? 'selected' : '' }}>351 - Portugal</option>
                                    <option value="33" {{ old('default_country_code', $customer->default_country_code) == '33' ? 'selected' : '' }}>33 - Francia</option>
                                    <option value="44" {{ old('default_country_code', $customer->default_country_code) == '44' ? 'selected' : '' }}>44 - Reino Unido</option>
                                    <option value="49" {{ old('default_country_code', $customer->default_country_code) == '49' ? 'selected' : '' }}>49 - Alemania</option>
                                    <option value="39" {{ old('default_country_code', $customer->default_country_code) == '39' ? 'selected' : '' }}>39 - Italia</option>
                                    <option value="1" {{ old('default_country_code', $customer->default_country_code) == '1' ? 'selected' : '' }}>1 - USA/Canada</option>
                                    <option value="52" {{ old('default_country_code', $customer->default_country_code) == '52' ? 'selected' : '' }}>52 - Mexico</option>
                                    <option value="54" {{ old('default_country_code', $customer->default_country_code) == '54' ? 'selected' : '' }}>54 - Argentina</option>
                                    <option value="57" {{ old('default_country_code', $customer->default_country_code) == '57' ? 'selected' : '' }}>57 - Colombia</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Usado para formato nacional o deteccion automatica</p>
                            </div>
                        </div>

                        <!-- Opciones avanzadas -->
                        <div class="mt-4 p-4 bg-gray-800/30 rounded-lg">
                            <p class="text-xs font-semibold text-gray-400 mb-3">Opciones de formato de salida</p>
                            <div class="flex flex-wrap gap-6">
                                <label class="flex items-center">
                                    <input type="hidden" name="strip_plus_sign" value="0">
                                    <input type="checkbox" name="strip_plus_sign" value="1" class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500 focus:ring-offset-gray-800"
                                        {{ old('strip_plus_sign', $customer->strip_plus_sign) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-300">Eliminar signo + del numero</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="hidden" name="add_plus_sign" value="0">
                                    <input type="checkbox" name="add_plus_sign" value="1" class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500 focus:ring-offset-gray-800"
                                        {{ old('add_plus_sign', $customer->add_plus_sign) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-300">Anadir signo + al numero normalizado</span>
                                </label>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Estas opciones controlan como se formatea el numero despues de la normalizacion, antes de enviarlo al carrier.</p>
                        </div>

                        <!-- Force CLI -->
                        <div class="mt-4 p-4 bg-yellow-900/20 border border-yellow-700/30 rounded-lg">
                            <div class="flex items-start gap-4">
                                <div class="flex-1">
                                    <label for="force_cli" class="block text-sm font-medium text-yellow-300">Forzar CLI (Caller ID)</label>
                                    <input type="text" name="force_cli" id="force_cli" value="{{ old('force_cli', $customer->force_cli) }}"
                                        class="dark-input mt-1 w-full py-2 px-3" placeholder="Ej: 34680680680">
                                    <p class="mt-1 text-xs text-gray-500">Si se especifica, todas las llamadas de este cliente saldran con este numero como CLI. Dejar vacio para usar el CLI original.</p>
                                </div>
                                <div class="pt-6">
                                    <svg class="w-8 h-8 text-yellow-500/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Test de Normalizacion -->
                        <div class="mt-4 p-4 bg-gray-800/30 rounded-lg">
                            <p class="text-xs font-semibold text-gray-400 mb-3">Probar normalizacion</p>
                            <div class="flex gap-3">
                                <input type="text" id="test-number" placeholder="Introduce un numero para probar..."
                                    class="dark-input flex-1 py-2 px-3 text-sm">
                                <button type="button" onclick="testNormalization()" class="btn-secondary text-sm px-4">
                                    Probar
                                </button>
                            </div>
                            <div id="test-result" class="mt-3 hidden">
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="text-gray-400">Entrada:</span>
                                    <code id="test-input" class="bg-gray-800 px-2 py-1 rounded text-gray-300"></code>
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    <span class="text-gray-400">Salida:</span>
                                    <code id="test-output" class="bg-green-900/50 px-2 py-1 rounded text-green-300"></code>
                                </div>
                                <p id="test-message" class="mt-2 text-xs text-gray-400"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Limites -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Limites de Servicio</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="max_channels" class="block text-sm font-medium text-gray-300">Canales Simultaneos *</label>
                                <input type="number" name="max_channels" id="max_channels" required min="1" value="{{ old('max_channels', $customer->max_channels) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                            <div>
                                <label for="max_cps" class="block text-sm font-medium text-gray-300">CPS Maximo *</label>
                                <input type="number" name="max_cps" id="max_cps" required min="1" value="{{ old('max_cps', $customer->max_cps) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                            <div>
                                <label for="max_daily_minutes" class="block text-sm font-medium text-gray-300">Minutos Diarios</label>
                                <input type="number" name="max_daily_minutes" id="max_daily_minutes" min="0" value="{{ old('max_daily_minutes', $customer->max_daily_minutes) }}"
                                    class="dark-input mt-1 w-full py-2 px-3" placeholder="Sin limite">
                                <p class="mt-1 text-xs text-gray-500">Usados hoy: <span class="text-blue-400 font-medium">{{ number_format($customer->used_daily_minutes) }}</span> minutos</p>
                            </div>
                            <div>
                                <label for="max_monthly_minutes" class="block text-sm font-medium text-gray-300">Minutos Mensuales</label>
                                <input type="number" name="max_monthly_minutes" id="max_monthly_minutes" min="0" value="{{ old('max_monthly_minutes', $customer->max_monthly_minutes) }}"
                                    class="dark-input mt-1 w-full py-2 px-3" placeholder="Sin limite">
                                <p class="mt-1 text-xs text-gray-500">Usados este mes: <span class="text-blue-400 font-medium">{{ number_format($customer->used_monthly_minutes) }}</span> minutos</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notificaciones -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Notificaciones</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="alert_email" class="block text-sm font-medium text-gray-300">Email de Alertas</label>
                                <input type="email" name="alert_email" id="alert_email" value="{{ old('alert_email', $customer->alert_email) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                            <div>
                                <label for="alert_telegram_chat_id" class="block text-sm font-medium text-gray-300">Telegram Chat ID</label>
                                <input type="text" name="alert_telegram_chat_id" id="alert_telegram_chat_id" value="{{ old('alert_telegram_chat_id', $customer->alert_telegram_chat_id) }}"
                                    class="dark-input mt-1 w-full py-2 px-3">
                            </div>
                        </div>
                    </div>

                    <!-- Debug / Trazas SIP -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Debug y Diagnostico</h3>
                        <div class="flex items-center justify-between p-4 bg-gray-800/50 rounded-lg">
                            <div>
                                <h4 class="text-sm font-medium text-white">Capturar Trazas SIP</h4>
                                <p class="text-xs text-gray-500 mt-0.5">Guarda todos los mensajes SIP de las llamadas para diagnostico</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="traces_enabled" value="0">
                                <input type="checkbox" name="traces_enabled" value="1" class="sr-only peer" {{ old('traces_enabled', $customer->traces_enabled) ? 'checked' : '' }}>
                                <div class="w-14 h-7 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-500"></div>
                                <span class="ml-3 text-sm font-medium {{ $customer->traces_enabled ? 'text-blue-400' : 'text-gray-400' }}">{{ $customer->traces_enabled ? 'Activo' : 'Inactivo' }}</span>
                            </label>
                        </div>
                        @if($customer->traces_enabled)
                        <p class="mt-2 text-xs text-blue-400">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Las trazas se pueden ver en el detalle de cada CDR
                        </p>
                        @endif
                    </div>

                    <!-- Notas -->
                    <div class="mb-8">
                        <label for="notes" class="block text-sm font-medium text-gray-300">Notas Internas</label>
                        <textarea name="notes" id="notes" rows="3"
                            class="dark-input mt-1 w-full py-2 px-3">{{ old('notes', $customer->notes) }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-700/50">
                        <a href="{{ route('customers.show', $customer) }}" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function testNormalization() {
            const number = document.getElementById('test-number').value.trim();
            if (!number) {
                alert('Introduce un numero para probar');
                return;
            }

            const format = document.getElementById('number_format').value;
            const countryCode = document.getElementById('default_country_code').value;
            const stripPlus = document.querySelector('input[name="strip_plus_sign"]:checked') !== null;
            const addPlus = document.querySelector('input[name="add_plus_sign"]:checked') !== null;

            fetch(`{{ route('customers.test-normalization') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    number: number,
                    format: format,
                    country_code: countryCode,
                    strip_plus: stripPlus,
                    add_plus: addPlus
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('test-result').classList.remove('hidden');
                document.getElementById('test-input').textContent = data.original;
                document.getElementById('test-output').textContent = data.normalized;
                document.getElementById('test-message').textContent = data.message || `Formato detectado: ${data.format_detected}`;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al probar la normalizacion');
            });
        }

        // Enter key to test
        document.getElementById('test-number').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                testNormalization();
            }
        });
    </script>
</x-app-layout>
