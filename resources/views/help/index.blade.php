<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Centro de Ayuda</h2>
                <p class="text-sm text-gray-400 mt-0.5">Guia completa del sistema de terminacion VoIP</p>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <div class="text-gray-400">
                    <span class="text-green-400 font-semibold">{{ $stats['customers_active'] }}</span> clientes activos
                </div>
                <div class="text-gray-400">
                    <span class="text-blue-400 font-semibold">{{ $stats['carriers_active'] }}</span> carriers activos
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Navegacion rapida -->
            <div class="dark-card p-4 mb-6">
                <div class="flex flex-wrap gap-2">
                    <a href="#clientes" class="px-3 py-1.5 bg-blue-500/20 text-blue-400 rounded-lg text-sm hover:bg-blue-500/30 transition-colors">Clientes</a>
                    <a href="#carriers" class="px-3 py-1.5 bg-green-500/20 text-green-400 rounded-lg text-sm hover:bg-green-500/30 transition-colors">Carriers</a>
                    <a href="#failover" class="px-3 py-1.5 bg-indigo-500/20 text-indigo-400 rounded-lg text-sm hover:bg-indigo-500/30 transition-colors">Failover</a>
                    <a href="#tarifas" class="px-3 py-1.5 bg-yellow-500/20 text-yellow-400 rounded-lg text-sm hover:bg-yellow-500/30 transition-colors">Tarifas</a>
                    <a href="#prepago" class="px-3 py-1.5 bg-purple-500/20 text-purple-400 rounded-lg text-sm hover:bg-purple-500/30 transition-colors">Prepago/Postpago</a>
                    <a href="#dialing" class="px-3 py-1.5 bg-red-500/20 text-red-400 rounded-lg text-sm hover:bg-red-500/30 transition-colors">Planes de Marcacion</a>
                    <a href="#numeracion" class="px-3 py-1.5 bg-cyan-500/20 text-cyan-400 rounded-lg text-sm hover:bg-cyan-500/30 transition-colors">Normalizacion</a>
                    <a href="#cdrs" class="px-3 py-1.5 bg-orange-500/20 text-orange-400 rounded-lg text-sm hover:bg-orange-500/30 transition-colors">CDRs</a>
                    <a href="#alertas" class="px-3 py-1.5 bg-pink-500/20 text-pink-400 rounded-lg text-sm hover:bg-pink-500/30 transition-colors">Alertas</a>
                    <a href="#arquitectura" class="px-3 py-1.5 bg-gray-500/20 text-gray-400 rounded-lg text-sm hover:bg-gray-500/30 transition-colors">Arquitectura</a>
                </div>
            </div>

            <!-- Introduccion -->
            <div class="dark-card p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4">Introduccion al Sistema</h2>
                <div class="prose prose-invert max-w-none">
                    <p class="text-gray-300">
                        Este panel gestiona un sistema de <strong class="text-white">terminacion VoIP</strong> basado en Kamailio.
                        Permite recibir trafico SIP de clientes autorizados y enrutarlo hacia carriers de terminacion.
                    </p>
                    <div class="mt-4 p-4 bg-gray-800/50 rounded-lg">
                        <h4 class="text-sm font-semibold text-blue-400 mb-2">Flujo de una llamada:</h4>
                        <ol class="text-sm text-gray-400 space-y-1 ml-4">
                            <li>1. Cliente envia INVITE desde IP autorizada</li>
                            <li>2. Sistema verifica IP, limites (canales, CPS, minutos)</li>
                            <li>3. Numero destino se normaliza segun configuracion del cliente</li>
                            <li>4. Se verifica plan de marcacion (destinos permitidos)</li>
                            <li>5. Se selecciona carrier por LCR (menor coste) o prioridad</li>
                            <li>6. Llamada se enruta al carrier</li>
                            <li>7. Al finalizar, se genera CDR con duracion, coste y precio</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Clientes -->
            <div id="clientes" class="dark-card p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Gestion de Clientes
                </h2>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-3">Crear un Cliente Nuevo</h3>
                        <ol class="space-y-2 text-sm text-gray-300">
                            <li class="flex items-start">
                                <span class="bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5 flex-shrink-0">1</span>
                                <span>Ir a <strong class="text-white">Clientes → Nuevo Cliente</strong></span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5 flex-shrink-0">2</span>
                                <span>Completar nombre y datos de contacto</span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5 flex-shrink-0">3</span>
                                <span>Configurar limites: <strong class="text-white">canales simultaneos</strong> (ej: 10), <strong class="text-white">CPS</strong> (ej: 5)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5 flex-shrink-0">4</span>
                                <span>Opcional: limites de minutos diarios/mensuales</span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5 flex-shrink-0">5</span>
                                <span>Guardar y luego <strong class="text-white">añadir IPs autorizadas</strong></span>
                            </li>
                        </ol>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-white mb-3">Añadir IPs Autorizadas</h3>
                        <div class="p-4 bg-gray-800/50 rounded-lg">
                            <p class="text-sm text-gray-300 mb-3">
                                Las IPs son la forma de <strong class="text-white">autenticacion</strong>. Solo las IPs registradas pueden enviar llamadas.
                            </p>
                            <ol class="space-y-1 text-sm text-gray-400">
                                <li>• Ir al detalle del cliente</li>
                                <li>• En la seccion "IPs Autorizadas"</li>
                                <li>• Introducir la IP publica del cliente</li>
                                <li>• Click en "Añadir IP"</li>
                            </ol>
                            <div class="mt-3 p-2 bg-green-900/30 border border-green-700/50 rounded text-xs text-green-300">
                                <strong>Automatico:</strong> Kamailio se recarga automaticamente al añadir/eliminar IPs.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-yellow-900/20 border border-yellow-700/30 rounded-lg">
                    <h4 class="text-sm font-semibold text-yellow-400 mb-2">Importante: Limites</h4>
                    <ul class="text-sm text-gray-300 space-y-1">
                        <li>• <strong class="text-white">Canales</strong>: Llamadas simultaneas permitidas. Si se supera, nuevas llamadas reciben 503.</li>
                        <li>• <strong class="text-white">CPS</strong>: Llamadas por segundo. Protege contra rafagas de trafico.</li>
                        <li>• <strong class="text-white">Minutos</strong>: Cuando se agotan, las llamadas reciben 486. Se resetean diaria/mensualmente.</li>
                    </ul>
                </div>
            </div>

            <!-- Carriers -->
            <div id="carriers" class="dark-card p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                    Gestion de Carriers
                </h2>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-3">Crear un Carrier</h3>
                        <ol class="space-y-2 text-sm text-gray-300">
                            <li class="flex items-start">
                                <span class="bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5 flex-shrink-0">1</span>
                                <span>Ir a <strong class="text-white">Carriers → Nuevo Carrier</strong></span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5 flex-shrink-0">2</span>
                                <span>Nombre descriptivo (ej: "Carrier A - Europa")</span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5 flex-shrink-0">3</span>
                                <span><strong class="text-white">Host</strong>: IP o dominio del carrier</span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5 flex-shrink-0">4</span>
                                <span><strong class="text-white">Puerto</strong>: Normalmente 5060</span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2 mt-0.5 flex-shrink-0">5</span>
                                <span><strong class="text-white">Codecs</strong>: G729, PCMA, PCMU (ordenados por preferencia)</span>
                            </li>
                        </ol>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-white mb-3">Configuracion de Enrutamiento</h3>
                        <div class="space-y-3">
                            <div class="p-3 bg-gray-800/50 rounded-lg">
                                <p class="text-sm font-semibold text-white">Prioridad</p>
                                <p class="text-xs text-gray-400">Menor numero = mayor prioridad. Carrier con prioridad 1 se usa antes que prioridad 2.</p>
                            </div>
                            <div class="p-3 bg-gray-800/50 rounded-lg">
                                <p class="text-sm font-semibold text-white">Weight (Peso)</p>
                                <p class="text-xs text-gray-400">Para balanceo entre carriers de misma prioridad. Mayor peso = mas trafico.</p>
                            </div>
                            <div class="p-3 bg-gray-800/50 rounded-lg">
                                <p class="text-sm font-semibold text-white">Tech Prefix</p>
                                <p class="text-xs text-gray-400">Prefijo que se añade al numero antes de enviar (ej: "00" o "9").</p>
                            </div>
                            <div class="p-3 bg-gray-800/50 rounded-lg">
                                <p class="text-sm font-semibold text-white">Strip Digits</p>
                                <p class="text-xs text-gray-400">Digitos a eliminar del inicio del numero (ej: eliminar "00" de 0034...).</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-4 gap-4">
                    <div class="p-3 bg-green-900/30 border border-green-700/50 rounded-lg text-center">
                        <div class="text-2xl font-bold text-green-400">Active</div>
                        <div class="text-xs text-gray-400">Recibe trafico normalmente</div>
                    </div>
                    <div class="p-3 bg-yellow-900/30 border border-yellow-700/50 rounded-lg text-center">
                        <div class="text-2xl font-bold text-yellow-400">Probing</div>
                        <div class="text-xs text-gray-400">En recuperacion (OPTIONS)</div>
                    </div>
                    <div class="p-3 bg-red-900/30 border border-red-700/50 rounded-lg text-center">
                        <div class="text-2xl font-bold text-red-400">Inactive</div>
                        <div class="text-xs text-gray-400">No responde, no recibe trafico</div>
                    </div>
                    <div class="p-3 bg-gray-700/50 border border-gray-600 rounded-lg text-center">
                        <div class="text-2xl font-bold text-gray-400">Disabled</div>
                        <div class="text-xs text-gray-400">Deshabilitado manualmente</div>
                    </div>
                </div>
            </div>

            <!-- Failover -->
            <div id="failover" class="dark-card p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Sistema de Failover y Monitoreo
                </h2>

                <div class="prose prose-invert max-w-none mb-6">
                    <p class="text-gray-300">
                        El sistema implementa <strong class="text-white">failover automatico</strong> entre carriers.
                        Si un carrier falla, la llamada se enruta automaticamente al siguiente carrier disponible por prioridad.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-3">Seleccion de Carrier</h3>
                        <div class="p-4 bg-gray-800/50 rounded-lg">
                            <p class="text-sm text-gray-300 mb-3">Las llamadas se enrutan por <strong class="text-white">prioridad</strong>:</p>
                            <ul class="text-sm text-gray-400 space-y-2">
                                <li class="flex items-center">
                                    <span class="bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2">1</span>
                                    <span>Carrier con <strong class="text-white">menor numero</strong> de prioridad = primero</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2">2</span>
                                    <span>Si falla, salta al siguiente por prioridad</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="bg-yellow-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mr-2">3</span>
                                    <span>Continua hasta agotar carriers o exito</span>
                                </li>
                            </ul>
                            <div class="mt-3 p-2 bg-indigo-900/30 border border-indigo-700/50 rounded text-xs text-indigo-300">
                                <strong>Ejemplo:</strong> Carrier A (prioridad 1) → Carrier B (prioridad 2) → Carrier C (prioridad 5)
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-white mb-3">Condiciones de Failover</h3>
                        <div class="space-y-2">
                            <div class="p-3 bg-red-900/30 border border-red-700/50 rounded-lg">
                                <p class="text-sm font-semibold text-red-400">Timeout (10 segundos)</p>
                                <p class="text-xs text-gray-400">Si el carrier no responde en 10s, salta al siguiente.</p>
                            </div>
                            <div class="p-3 bg-orange-900/30 border border-orange-700/50 rounded-lg">
                                <p class="text-sm font-semibold text-orange-400">Error del Carrier (4xx, 5xx, 6xx)</p>
                                <p class="text-xs text-gray-400">Cualquier error SIP del carrier activa failover.</p>
                            </div>
                            <div class="p-3 bg-gray-700/50 border border-gray-600 rounded-lg">
                                <p class="text-sm font-semibold text-gray-300">Excepciones (NO failover)</p>
                                <p class="text-xs text-gray-400">486 Ocupado, 487 Cancelado, 480 No disponible - son errores del usuario destino, no del carrier.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-white mb-3">Monitoreo de Carriers (OPTIONS)</h3>
                    <div class="grid md:grid-cols-4 gap-4">
                        <div class="p-4 bg-blue-900/20 border border-blue-700/30 rounded-lg text-center">
                            <div class="text-2xl font-bold text-blue-400">30s</div>
                            <div class="text-xs text-gray-400">Intervalo OPTIONS</div>
                            <div class="text-xs text-gray-500 mt-1">Cada 30 segundos</div>
                        </div>
                        <div class="p-4 bg-yellow-900/20 border border-yellow-700/30 rounded-lg text-center">
                            <div class="text-2xl font-bold text-yellow-400">2</div>
                            <div class="text-xs text-gray-400">Fallos → Probing</div>
                            <div class="text-xs text-gray-500 mt-1">~60 segundos</div>
                        </div>
                        <div class="p-4 bg-red-900/20 border border-red-700/30 rounded-lg text-center">
                            <div class="text-2xl font-bold text-red-400">+2</div>
                            <div class="text-xs text-gray-400">Mas fallos → Inactive</div>
                            <div class="text-xs text-gray-500 mt-1">~120 segundos total</div>
                        </div>
                        <div class="p-4 bg-green-900/20 border border-green-700/30 rounded-lg text-center">
                            <div class="text-2xl font-bold text-green-400">1</div>
                            <div class="text-xs text-gray-400">Responde → Active</div>
                            <div class="text-xs text-gray-500 mt-1">Recuperacion inmediata</div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-indigo-900/20 border border-indigo-700/30 rounded-lg">
                    <h4 class="text-sm font-semibold text-indigo-400 mb-2">Importante: Estado del Carrier</h4>
                    <p class="text-sm text-gray-300">
                        El estado del carrier (Active/Probing/Inactive) lo determina <strong class="text-white">unicamente el monitoreo OPTIONS</strong>.
                        Los errores durante llamadas activan failover pero <strong class="text-white">no cambian el estado</strong> del carrier.
                        Esto evita que errores puntuales marquen incorrectamente un carrier como caido.
                    </p>
                </div>
            </div>

            <!-- Tarifas -->
            <div id="tarifas" class="dark-card p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Sistema de Tarifas (LCR)
                </h2>

                <div class="prose prose-invert max-w-none mb-6">
                    <p class="text-gray-300">
                        El sistema soporta enrutamiento por <strong class="text-white">Least Cost Routing (LCR)</strong>.
                        Cada destino puede tener tarifas diferentes por carrier, y el sistema selecciona el mas economico.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-6">
                    <div class="p-4 bg-gray-800/50 rounded-lg">
                        <h4 class="font-semibold text-white mb-2">1. Destinos (Prefijos)</h4>
                        <p class="text-sm text-gray-400 mb-2">Define los prefijos de destino:</p>
                        <ul class="text-xs text-gray-500 space-y-1">
                            <li>• <code class="bg-gray-700 px-1 rounded">34</code> → España</li>
                            <li>• <code class="bg-gray-700 px-1 rounded">346</code> → España Movil</li>
                            <li>• <code class="bg-gray-700 px-1 rounded">349</code> → España Fijo</li>
                            <li>• <code class="bg-gray-700 px-1 rounded">1</code> → USA/Canada</li>
                        </ul>
                        <p class="text-xs text-gray-500 mt-2">Se usa el prefijo mas largo que coincida.</p>
                    </div>

                    <div class="p-4 bg-gray-800/50 rounded-lg">
                        <h4 class="font-semibold text-white mb-2">2. Tarifas Carrier (Coste)</h4>
                        <p class="text-sm text-gray-400 mb-2">Lo que nos cobra el carrier:</p>
                        <ul class="text-xs text-gray-500 space-y-1">
                            <li>• Coste por minuto</li>
                            <li>• Coste de conexion</li>
                            <li>• Incremento de facturacion (ej: 6 seg)</li>
                            <li>• Duracion minima</li>
                        </ul>
                        <p class="text-xs text-green-400 mt-2">LCR elige el carrier mas barato.</p>
                    </div>

                    <div class="p-4 bg-gray-800/50 rounded-lg">
                        <h4 class="font-semibold text-white mb-2">3. Tarifas Cliente (Precio)</h4>
                        <p class="text-sm text-gray-400 mb-2">Lo que cobramos al cliente:</p>
                        <ul class="text-xs text-gray-500 space-y-1">
                            <li>• Precio por minuto</li>
                            <li>• Precio de conexion</li>
                            <li>• Incremento de facturacion</li>
                        </ul>
                        <p class="text-xs text-blue-400 mt-2">Puede ser por cliente o por Plan de Tarifas.</p>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-blue-900/20 border border-blue-700/30 rounded-lg">
                    <h4 class="text-sm font-semibold text-blue-400 mb-2">Planes de Tarifas</h4>
                    <p class="text-sm text-gray-300">
                        Un <strong class="text-white">Plan de Tarifas</strong> agrupa precios para asignar a multiples clientes.
                        En lugar de configurar precios para cada cliente, asigna un plan.
                        Ejemplo: "Plan Europa Premium" con precios especiales para destinos europeos.
                    </p>
                </div>
            </div>

            <!-- Prepago/Postpago -->
            <div id="prepago" class="dark-card p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Facturacion: Prepago vs Postpago
                </h2>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="p-5 bg-green-900/20 border border-green-700/30 rounded-lg">
                        <h3 class="text-lg font-semibold text-green-400 mb-3">Prepago</h3>
                        <ul class="space-y-2 text-sm text-gray-300">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Cliente tiene <strong class="text-white">saldo positivo</strong>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Cada llamada <strong class="text-white">descuenta del saldo</strong>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Si saldo llega a 0, llamadas <strong class="text-white">se bloquean</strong>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Configurable: <strong class="text-white">suspension automatica</strong> en saldo 0
                            </li>
                        </ul>
                        <div class="mt-4 p-3 bg-green-900/30 rounded">
                            <p class="text-xs text-green-300">
                                <strong>Usar para:</strong> Clientes nuevos, trafico variable, control de riesgo.
                            </p>
                        </div>
                    </div>

                    <div class="p-5 bg-blue-900/20 border border-blue-700/30 rounded-lg">
                        <h3 class="text-lg font-semibold text-blue-400 mb-3">Postpago</h3>
                        <ul class="space-y-2 text-sm text-gray-300">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-blue-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Cliente tiene <strong class="text-white">limite de credito</strong>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-blue-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Llamadas se <strong class="text-white">acumulan como deuda</strong>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-blue-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Bloqueo cuando deuda supera <strong class="text-white">limite de credito</strong>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-blue-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Se factura <strong class="text-white">periodicamente</strong> (mensual)
                            </li>
                        </ul>
                        <div class="mt-4 p-3 bg-blue-900/30 rounded">
                            <p class="text-xs text-blue-300">
                                <strong>Usar para:</strong> Clientes de confianza, grandes volumenes, relaciones establecidas.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-white mb-3">Gestion de Saldo</h3>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="p-3 bg-gray-800/50 rounded-lg">
                            <p class="text-sm font-semibold text-white">Añadir Saldo</p>
                            <p class="text-xs text-gray-400">Facturacion → Transacciones → Nueva recarga</p>
                        </div>
                        <div class="p-3 bg-gray-800/50 rounded-lg">
                            <p class="text-sm font-semibold text-white">Alerta Saldo Bajo</p>
                            <p class="text-xs text-gray-400">Configurable por cliente (ej: avisar con saldo < 50€)</p>
                        </div>
                        <div class="p-3 bg-gray-800/50 rounded-lg">
                            <p class="text-sm font-semibold text-white">Suspension Manual</p>
                            <p class="text-xs text-gray-400">Editar cliente → Suspender (con motivo)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Planes de Marcacion -->
            <div id="dialing" class="dark-card p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Planes de Marcacion (Restricciones)
                </h2>

                <div class="prose prose-invert max-w-none mb-6">
                    <p class="text-gray-300">
                        Los <strong class="text-white">Planes de Marcacion</strong> permiten restringir a que destinos puede llamar un cliente.
                        Util para evitar fraude, controlar costes o limitar servicio geograficamente.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-3">Tipos de Reglas</h3>
                        <div class="space-y-3">
                            <div class="p-3 bg-green-900/30 border border-green-700/50 rounded-lg">
                                <p class="text-sm font-semibold text-green-400">ALLOW (Permitir)</p>
                                <p class="text-xs text-gray-400">El numero puede marcarse si coincide con el patron.</p>
                                <code class="text-xs bg-gray-800 px-2 py-1 rounded mt-1 inline-block">34* → Permitir España</code>
                            </div>
                            <div class="p-3 bg-red-900/30 border border-red-700/50 rounded-lg">
                                <p class="text-sm font-semibold text-red-400">DENY (Denegar)</p>
                                <p class="text-xs text-gray-400">El numero NO puede marcarse si coincide con el patron.</p>
                                <code class="text-xs bg-gray-800 px-2 py-1 rounded mt-1 inline-block">1900* → Bloquear numeros premium USA</code>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-white mb-3">Patrones (Wildcards)</h3>
                        <div class="p-4 bg-gray-800/50 rounded-lg">
                            <ul class="space-y-2 text-sm text-gray-300">
                                <li><code class="bg-gray-700 px-1 rounded">*</code> → Cualquier secuencia de digitos</li>
                                <li><code class="bg-gray-700 px-1 rounded">?</code> → Un solo digito</li>
                                <li><code class="bg-gray-700 px-1 rounded">34*</code> → Cualquier numero que empiece por 34</li>
                                <li><code class="bg-gray-700 px-1 rounded">346???????</code> → Movil España exacto (9 digitos)</li>
                                <li><code class="bg-gray-700 px-1 rounded">1800*</code> → Numeros gratuitos USA</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-yellow-900/20 border border-yellow-700/30 rounded-lg">
                    <h4 class="text-sm font-semibold text-yellow-400 mb-2">Orden de Evaluacion</h4>
                    <p class="text-sm text-gray-300">
                        Las reglas se evaluan por <strong class="text-white">prioridad</strong> (menor numero = mayor prioridad).
                        La primera regla que coincida determina si se permite o deniega.
                        Si ninguna regla coincide, se usa la <strong class="text-white">accion por defecto</strong> del plan (allow/deny).
                    </p>
                </div>
            </div>

            <!-- Normalizacion -->
            <div id="numeracion" class="dark-card p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>
                    Normalizacion de Numeros
                </h2>

                <div class="prose prose-invert max-w-none mb-6">
                    <p class="text-gray-300">
                        Cada cliente puede enviar numeros en diferente formato. El sistema <strong class="text-white">normaliza</strong>
                        automaticamente al formato E.164 antes del enrutamiento.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-4">
                    <div class="p-4 bg-blue-900/30 border border-blue-700/50 rounded-lg">
                        <h4 class="font-semibold text-blue-400 mb-2">Deteccion Automatica</h4>
                        <p class="text-xs text-gray-400 mb-2">Detecta si es nacional o internacional:</p>
                        <ul class="text-xs text-gray-500 space-y-1">
                            <li><code class="bg-gray-800 px-1 rounded">666123456</code> → <code class="text-green-400">34666123456</code></li>
                            <li><code class="bg-gray-800 px-1 rounded">34666123456</code> → <code class="text-green-400">34666123456</code></li>
                        </ul>
                    </div>

                    <div class="p-4 bg-green-900/30 border border-green-700/50 rounded-lg">
                        <h4 class="font-semibold text-green-400 mb-2">Internacional E.164</h4>
                        <p class="text-xs text-gray-400 mb-2">Cliente siempre envia con codigo pais:</p>
                        <ul class="text-xs text-gray-500 space-y-1">
                            <li><code class="bg-gray-800 px-1 rounded">+34666123456</code> → <code class="text-green-400">34666123456</code></li>
                            <li><code class="bg-gray-800 px-1 rounded">34666123456</code> → <code class="text-green-400">34666123456</code></li>
                        </ul>
                    </div>

                    <div class="p-4 bg-yellow-900/30 border border-yellow-700/50 rounded-lg">
                        <h4 class="font-semibold text-yellow-400 mb-2">Nacional España</h4>
                        <p class="text-xs text-gray-400 mb-2">Cliente envia 9 digitos sin prefijo:</p>
                        <ul class="text-xs text-gray-500 space-y-1">
                            <li><code class="bg-gray-800 px-1 rounded">666123456</code> → <code class="text-green-400">34666123456</code></li>
                            <li><code class="bg-gray-800 px-1 rounded">911234567</code> → <code class="text-green-400">34911234567</code></li>
                        </ul>
                    </div>
                </div>

                <p class="mt-4 text-sm text-gray-400">
                    Configurar en: <strong class="text-white">Editar Cliente → Formato de Numeracion</strong>
                </p>
            </div>

            <!-- CDRs -->
            <div id="cdrs" class="dark-card p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Registros de Llamadas (CDRs)
                </h2>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-3">Informacion del CDR</h3>
                        <ul class="space-y-2 text-sm text-gray-300">
                            <li>• <strong class="text-white">Fecha/Hora</strong>: Inicio de la llamada</li>
                            <li>• <strong class="text-white">Cliente</strong>: Quien origino la llamada</li>
                            <li>• <strong class="text-white">Origen/Destino</strong>: Numeros A y B</li>
                            <li>• <strong class="text-white">Duracion</strong>: Tiempo de conversacion</li>
                            <li>• <strong class="text-white">PDD</strong>: Post Dial Delay (tiempo hasta ring)</li>
                            <li>• <strong class="text-white">Carrier</strong>: Por donde salio la llamada</li>
                            <li>• <strong class-"text-white">Codigo SIP</strong>: Resultado (200=OK, 486=Ocupado...)</li>
                            <li>• <strong class="text-white">Coste/Precio/Margen</strong>: Facturacion</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-white mb-3">Filtros de Busqueda</h3>
                        <div class="p-4 bg-gray-800/50 rounded-lg">
                            <ul class="space-y-2 text-sm text-gray-400">
                                <li>• Por rango de fechas</li>
                                <li>• Por cliente o carrier</li>
                                <li>• Por numero origen o destino</li>
                                <li>• Por duracion minima/maxima</li>
                                <li>• Solo contestadas / solo fallidas</li>
                                <li>• Por codigo SIP especifico</li>
                            </ul>
                            <div class="mt-3 pt-3 border-t border-gray-700">
                                <p class="text-xs text-gray-500">
                                    <strong class="text-white">Exportar CSV:</strong> Descarga los CDRs filtrados para analisis externo.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-blue-900/20 border border-blue-700/30 rounded-lg">
                    <h4 class="text-sm font-semibold text-blue-400 mb-2">Trazas SIP</h4>
                    <p class="text-sm text-gray-300">
                        Si el cliente tiene <strong class="text-white">trazas habilitadas</strong>, puedes ver todos los mensajes SIP
                        de la llamada (INVITE, 180, 200, BYE...) en formato ladder diagram.
                        Util para diagnostico de problemas.
                    </p>
                </div>
            </div>

            <!-- Alertas -->
            <div id="alertas" class="dark-card p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    Sistema de Alertas
                </h2>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-3">Tipos de Alertas</h3>
                        <div class="space-y-2">
                            <div class="flex items-center p-2 bg-red-900/30 rounded">
                                <span class="badge badge-red mr-2">Critico</span>
                                <span class="text-sm text-gray-300">Carrier caido, seguridad</span>
                            </div>
                            <div class="flex items-center p-2 bg-yellow-900/30 rounded">
                                <span class="badge badge-yellow mr-2">Warning</span>
                                <span class="text-sm text-gray-300">Limites al 80%, ASR bajo</span>
                            </div>
                            <div class="flex items-center p-2 bg-blue-900/30 rounded">
                                <span class="badge badge-blue mr-2">Info</span>
                                <span class="text-sm text-gray-300">Carrier recuperado, cambios</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-white mb-3">Notificaciones</h3>
                        <ul class="space-y-2 text-sm text-gray-300">
                            <li>• <strong class="text-white">Email</strong>: A email de alerta del cliente</li>
                            <li>• <strong class="text-white">Telegram</strong>: Bot envia mensajes instantaneos</li>
                            <li>• <strong class="text-white">Webhooks</strong>: Integracion con sistemas externos</li>
                            <li>• <strong class="text-white">Panel</strong>: Badge en menu con contador</li>
                        </ul>
                        <div class="mt-3 p-2 bg-gray-800/50 rounded text-xs text-gray-400">
                            Configurar destinos en: Sistema → Configuracion → Notificaciones
                        </div>
                    </div>
                </div>
            </div>

            <!-- Arquitectura -->
            <div id="arquitectura" class="dark-card p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Arquitectura del Sistema
                </h2>

                <div class="p-4 bg-gray-800/50 rounded-lg font-mono text-sm">
                    <pre class="text-gray-300">
┌─────────────┐      ┌──────────────┐      ┌─────────────┐
│   Cliente   │─────▶│   Kamailio   │─────▶│   Carrier   │
│  (IP auth)  │      │   (SIP)      │      │  (Destino)  │
└─────────────┘      └──────┬───────┘      └─────────────┘
                           │
              ┌────────────┼────────────┐
              ▼            ▼            ▼
        ┌──────────┐ ┌──────────┐ ┌──────────┐
        │  MySQL   │ │  Redis   │ │  Laravel │
        │  (CDRs)  │ │ (Tiempo  │ │  (Panel) │
        │          │ │   Real)  │ │          │
        └──────────┘ └──────────┘ └──────────┘
                    </pre>
                </div>

                <div class="mt-6 grid md:grid-cols-4 gap-4">
                    <div class="p-3 bg-gray-800/50 rounded-lg text-center">
                        <div class="text-lg font-bold text-blue-400">Kamailio</div>
                        <div class="text-xs text-gray-500">Proxy SIP, enrutamiento</div>
                    </div>
                    <div class="p-3 bg-gray-800/50 rounded-lg text-center">
                        <div class="text-lg font-bold text-green-400">MySQL</div>
                        <div class="text-xs text-gray-500">CDRs, clientes, carriers</div>
                    </div>
                    <div class="p-3 bg-gray-800/50 rounded-lg text-center">
                        <div class="text-lg font-bold text-red-400">Redis</div>
                        <div class="text-xs text-gray-500">Contadores tiempo real</div>
                    </div>
                    <div class="p-3 bg-gray-800/50 rounded-lg text-center">
                        <div class="text-lg font-bold text-purple-400">Laravel</div>
                        <div class="text-xs text-gray-500">Panel web, API</div>
                    </div>
                </div>
            </div>

            <!-- Comandos Utiles -->
            <div class="dark-card p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Comandos Utiles (Consola)
                </h2>

                <div class="grid md:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-800/50 rounded-lg">
                        <h4 class="font-semibold text-white mb-2">Kamailio</h4>
                        <ul class="text-sm text-gray-400 space-y-1 font-mono">
                            <li><code class="text-green-400">kamctl ul show</code> - Ver registros</li>
                            <li><code class="text-green-400">kamcmd dispatcher.list</code> - Ver carriers</li>
                            <li><code class="text-green-400">kamcmd dispatcher.reload</code> - Recargar carriers</li>
                            <li><code class="text-green-400">kamcmd permissions.addressReload</code> - Recargar IPs</li>
                        </ul>
                    </div>
                    <div class="p-4 bg-gray-800/50 rounded-lg">
                        <h4 class="font-semibold text-white mb-2">Laravel (Artisan)</h4>
                        <ul class="text-sm text-gray-400 space-y-1 font-mono">
                            <li><code class="text-blue-400">php artisan kamailio:sync</code> - Recargar Kamailio</li>
                            <li><code class="text-blue-400">php artisan queue:work</code> - Procesar jobs</li>
                            <li><code class="text-blue-400">php artisan stats:daily</code> - Calcular stats</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-500 py-4">
                <p>VoIP Termination System v1.0 &bull; TellMe Telecom</p>
                <p class="mt-1">Para soporte tecnico: soporte@tellmetelecom.com</p>
            </div>
        </div>
    </div>
</x-app-layout>
