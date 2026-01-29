<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('cdrs.index') }}" class="text-gray-400 hover:text-white mr-3 p-1 hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-white">Detalle de Llamada</h2>
                    <p class="text-sm text-gray-400 mt-0.5 font-mono">{{ Str::limit($cdr->call_id, 40) }}</p>
                </div>
            </div>
            @if($cdr->answer_time)
                <span class="badge badge-green text-sm px-4 py-1.5">Contestada</span>
            @else
                <span class="badge badge-red text-sm px-4 py-1.5">Fallida - {{ $cdr->sip_code }}</span>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Timeline Visual -->
            <div class="dark-card p-6 mb-6">
                <h3 class="text-sm font-semibold text-white mb-6">Timeline de la Llamada</h3>
                <div class="relative">
                    <div class="absolute top-5 left-0 right-0 h-1 bg-gray-700 rounded"></div>
                    <div class="flex justify-between relative">
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center mx-auto relative z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <div class="text-xs text-gray-500 mt-2">INVITE</div>
                            <div class="text-xs font-mono text-gray-300">{{ $cdr->start_time->format('H:i:s.v') }}</div>
                        </div>
                        @if($cdr->progress_time)
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-full bg-yellow-500 text-white flex items-center justify-center mx-auto relative z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            </div>
                            <div class="text-xs text-gray-500 mt-2">180 Ringing</div>
                            <div class="text-xs font-mono text-gray-300">{{ $cdr->progress_time->format('H:i:s.v') }}</div>
                        </div>
                        @endif
                        @if($cdr->answer_time)
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center mx-auto relative z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div class="text-xs text-gray-500 mt-2">200 OK</div>
                            <div class="text-xs font-mono text-gray-300">{{ $cdr->answer_time->format('H:i:s.v') }}</div>
                        </div>
                        @else
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-full bg-red-500 text-white flex items-center justify-center mx-auto relative z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                            <div class="text-xs text-red-400 mt-2">{{ $cdr->sip_code }} {{ $cdr->sip_reason }}</div>
                            <div class="text-xs font-mono text-gray-500">-</div>
                        </div>
                        @endif
                        @if($cdr->end_time)
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-full bg-gray-600 text-white flex items-center justify-center mx-auto relative z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path></svg>
                            </div>
                            <div class="text-xs text-gray-500 mt-2">BYE</div>
                            <div class="text-xs font-mono text-gray-300">{{ $cdr->end_time->format('H:i:s.v') }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Informacion de la Llamada -->
                <div class="dark-card p-5">
                    <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Informacion de la Llamada</h3>
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">UUID</dt>
                            <dd class="font-mono text-xs text-gray-400 bg-gray-800 p-2 rounded mt-1">{{ $cdr->uuid }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Call-ID</dt>
                            <dd class="font-mono text-xs text-gray-400 bg-gray-800 p-2 rounded mt-1 break-all">{{ Str::limit($cdr->call_id, 25) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Cliente</dt>
                            <dd class="mt-1">
                                @if($cdr->customer)
                                    <a href="{{ route('customers.show', $cdr->customer) }}" class="text-blue-400 hover:text-blue-300 font-medium">{{ $cdr->customer->name }}</a>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Carrier</dt>
                            <dd class="mt-1">
                                @if($cdr->carrier)
                                    <a href="{{ route('carriers.show', $cdr->carrier) }}" class="text-blue-400 hover:text-blue-300 font-medium">{{ $cdr->carrier->name }}</a>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </dd>
                        </div>
                        <div class="col-span-2 grid grid-cols-2 gap-4 pt-4 border-t border-gray-700/50">
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wider">Origen</dt>
                                <dd class="font-mono text-lg font-bold text-white mt-1">{{ $cdr->caller }}</dd>
                                @if($cdr->caller !== $cdr->caller_original)
                                    <dd class="text-xs text-gray-500">Original: {{ $cdr->caller_original }}</dd>
                                @endif
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wider">Destino</dt>
                                <dd class="font-mono text-lg font-bold text-white mt-1">{{ $cdr->callee }}</dd>
                                @if($cdr->callee !== $cdr->callee_original)
                                    <dd class="text-xs text-gray-500">Original: {{ $cdr->callee_original }}</dd>
                                @endif
                            </div>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">IP Origen</dt>
                            <dd class="font-mono text-sm text-gray-300 mt-1">{{ $cdr->source_ip }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">IP Destino</dt>
                            <dd class="font-mono text-sm text-gray-300 mt-1">{{ $cdr->destination_ip ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Tiempos y Duracion -->
                <div class="dark-card p-5">
                    <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Tiempos y Duracion</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between items-center p-3 bg-gray-800/50 rounded-lg">
                            <dt class="text-sm text-gray-400">Inicio</dt>
                            <dd class="font-mono text-sm text-gray-200">{{ $cdr->start_time->format('d/m/Y H:i:s.v') }}</dd>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-800/50 rounded-lg">
                            <dt class="text-sm text-gray-400">Progreso (Ring)</dt>
                            <dd class="font-mono text-sm text-gray-200">{{ $cdr->progress_time ? $cdr->progress_time->format('H:i:s.v') : '-' }}</dd>
                        </div>
                        <div class="flex justify-between items-center p-3 rounded-lg {{ $cdr->answer_time ? 'bg-green-500/10' : 'bg-red-500/10' }}">
                            <dt class="text-sm text-gray-400">Respuesta</dt>
                            <dd class="font-mono text-sm {{ $cdr->answer_time ? 'text-green-400' : 'text-red-400' }}">{{ $cdr->answer_time ? $cdr->answer_time->format('H:i:s.v') : 'No contestada' }}</dd>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-800/50 rounded-lg">
                            <dt class="text-sm text-gray-400">Fin</dt>
                            <dd class="font-mono text-sm text-gray-200">{{ $cdr->end_time ? $cdr->end_time->format('H:i:s.v') : '-' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-3 pt-3 border-t border-gray-700/50">
                            <div class="text-center p-3 bg-blue-500/10 rounded-lg">
                                <div class="text-xl font-bold text-blue-400">{{ gmdate('H:i:s', $cdr->duration) }}</div>
                                <div class="text-xs text-gray-500">Duracion</div>
                            </div>
                            <div class="text-center p-3 bg-green-500/10 rounded-lg">
                                <div class="text-xl font-bold text-green-400">{{ gmdate('H:i:s', $cdr->billable_duration) }}</div>
                                <div class="text-xs text-gray-500">Facturable</div>
                            </div>
                            <div class="text-center p-3 bg-purple-500/10 rounded-lg">
                                <div class="text-xl font-bold text-purple-400">{{ $cdr->pdd ?: '-' }}</div>
                                <div class="text-xs text-gray-500">PDD (ms)</div>
                            </div>
                        </div>
                    </dl>
                </div>

                <!-- Estado SIP -->
                <div class="dark-card p-5">
                    <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Estado SIP</h3>
                    <dl class="space-y-4">
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-400">Codigo SIP</dt>
                            <dd>
                                <span class="px-4 py-2 text-lg font-bold rounded-lg {{ $cdr->answer_time ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                    {{ $cdr->sip_code }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-800/50 rounded-lg">
                            <dt class="text-sm text-gray-400">Razon SIP</dt>
                            <dd class="text-gray-200">{{ $cdr->sip_reason ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-800/50 rounded-lg">
                            <dt class="text-sm text-gray-400">Causa de Colgado</dt>
                            <dd class="text-gray-200 capitalize">{{ $cdr->hangup_cause ?? '-' }}</dd>
                        </div>
                        @if($cdr->hangup_sip_code)
                        <div class="flex justify-between items-center p-3 bg-gray-800/50 rounded-lg">
                            <dt class="text-sm text-gray-400">Codigo Colgado</dt>
                            <dd class="font-mono text-gray-200">{{ $cdr->hangup_sip_code }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Detalles Tecnicos -->
                <div class="dark-card p-5">
                    <h3 class="text-sm font-semibold text-white mb-4 pb-2 border-b border-gray-700/50">Detalles Tecnicos</h3>
                    <dl class="space-y-4">
                        <div class="p-3 bg-gray-800/50 rounded-lg">
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Codecs Ofrecidos</dt>
                            <dd class="font-mono text-sm text-gray-300 mt-1">{{ $cdr->codecs_offered ?? '-' }}</dd>
                        </div>
                        <div class="p-3 bg-green-500/10 rounded-lg">
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Codec Usado</dt>
                            <dd class="font-mono text-lg font-bold text-green-400 mt-1">{{ $cdr->codec_used ?? '-' }}</dd>
                        </div>
                        <div class="p-3 bg-gray-800/50 rounded-lg">
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">User-Agent</dt>
                            <dd class="font-mono text-xs text-gray-400 mt-1 break-all">{{ $cdr->user_agent ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Diagrama Ladder de Trazas SIP -->
            @if($traces->count() > 0)
            @php
                // Detectar IPs de las entidades
                $clientIp = $cdr->source_ip;
                $carrierIp = $cdr->destination_ip ?? '127.0.0.1';
                $kamailioIp = '165.22.130.17'; // IP local de Kamailio

                // Función para determinar entidad por IP
                $getEntity = function($ip) use ($clientIp, $carrierIp) {
                    if ($ip === $clientIp) return 'client';
                    if (in_array($ip, ['165.22.130.17', '127.0.0.1'])) return 'kamailio';
                    return 'carrier';
                };
            @endphp
            <div class="mt-6 dark-card overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-700/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-white">Diagrama de Senalizacion SIP</h3>
                        <p class="text-xs text-gray-500">Click en cualquier mensaje para ver el contenido completo</p>
                    </div>
                    <span class="badge badge-blue">{{ $traces->count() }} mensajes</span>
                </div>

                <div class="p-4 overflow-x-auto">
                    <!-- Leyenda de colores -->
                    <div class="flex flex-wrap gap-3 mb-4 text-xs">
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-500"></span> Request</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-500"></span> 1xx Provisional</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-500"></span> 2xx Success</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-yellow-500"></span> 3xx Redirect</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-500"></span> 4xx/5xx/6xx Error</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-orange-500"></span> BYE/CANCEL</span>
                    </div>

                    <div class="min-w-[700px]">
                        <!-- Headers con lineas verticales -->
                        <div class="flex relative">
                            <!-- Columna tiempo -->
                            <div class="w-24 shrink-0"></div>

                            <!-- Cliente -->
                            <div class="flex-1 text-center pb-4 relative">
                                <div class="inline-block bg-purple-500/20 border border-purple-500/50 rounded-lg px-4 py-2">
                                    <div class="font-bold text-purple-400">Cliente</div>
                                    <div class="text-xs text-gray-400 font-mono">{{ $clientIp }}</div>
                                </div>
                            </div>

                            <!-- Kamailio -->
                            <div class="flex-1 text-center pb-4 relative">
                                <div class="inline-block bg-blue-500/20 border border-blue-500/50 rounded-lg px-4 py-2">
                                    <div class="font-bold text-blue-400">Kamailio</div>
                                    <div class="text-xs text-gray-400 font-mono">165.22.130.17</div>
                                </div>
                            </div>

                            <!-- Carrier -->
                            <div class="flex-1 text-center pb-4 relative">
                                <div class="inline-block bg-green-500/20 border border-green-500/50 rounded-lg px-4 py-2">
                                    <div class="font-bold text-green-400">Carrier</div>
                                    <div class="text-xs text-gray-400 font-mono">{{ $carrierIp ?: '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Messages con lineas verticales -->
                        <div class="relative">
                            <!-- Lineas verticales de fondo -->
                            <div class="absolute inset-0 flex pointer-events-none" style="left: 96px;">
                                <div class="flex-1 flex justify-center">
                                    <div class="w-0.5 h-full bg-purple-500/30"></div>
                                </div>
                                <div class="flex-1 flex justify-center">
                                    <div class="w-0.5 h-full bg-blue-500/30"></div>
                                </div>
                                <div class="flex-1 flex justify-center">
                                    <div class="w-0.5 h-full bg-green-500/30"></div>
                                </div>
                            </div>

                            <!-- Mensajes -->
                            @foreach($traces as $index => $trace)
                                @php
                                    $sourceEntity = $getEntity($trace->source_ip);
                                    $destEntity = $getEntity($trace->dest_ip);

                                    // Para loopback testing, ajustar basado en puertos
                                    if ($trace->source_ip === '127.0.0.1' && $trace->dest_ip === '127.0.0.1') {
                                        // Puerto 5091 = cliente test, 5060 = kamailio, 5080 = carrier test
                                        $sourceEntity = match(true) {
                                            $trace->source_port == 5091 => 'client',
                                            $trace->source_port == 5080 => 'carrier',
                                            default => 'kamailio'
                                        };
                                        $destEntity = match(true) {
                                            $trace->dest_port == 5091 => 'client',
                                            $trace->dest_port == 5080 => 'carrier',
                                            default => 'kamailio'
                                        };
                                    }

                                    $label = $trace->response_code
                                        ? $trace->response_code . ' ' . ($trace->method === 'INVITE' ? '' : $trace->method)
                                        : $trace->method;

                                    $colorClass = match(true) {
                                        $trace->response_code && $trace->response_code >= 200 && $trace->response_code < 300 => 'bg-green-500 text-white',
                                        $trace->response_code && $trace->response_code >= 100 && $trace->response_code < 200 => 'bg-gray-600 text-white',
                                        $trace->response_code && $trace->response_code >= 300 && $trace->response_code < 400 => 'bg-yellow-500 text-black',
                                        $trace->response_code && $trace->response_code >= 400 => 'bg-red-500 text-white',
                                        $trace->method === 'BYE' || $trace->method === 'CANCEL' => 'bg-orange-500 text-white',
                                        default => 'bg-blue-500 text-white'
                                    };

                                    // Calcular posiciones (0=cliente, 1=kamailio, 2=carrier)
                                    $positions = ['client' => 0, 'kamailio' => 1, 'carrier' => 2];
                                    $fromPos = $positions[$sourceEntity] ?? 1;
                                    $toPos = $positions[$destEntity] ?? 1;
                                    $goingRight = $toPos > $fromPos;
                                    $startCol = min($fromPos, $toPos);
                                    $spanCols = abs($toPos - $fromPos);
                                @endphp

                                <div class="flex items-center h-10 hover:bg-gray-800/30 cursor-pointer group relative"
                                     onclick="document.getElementById('trace-{{ $index }}').classList.toggle('hidden')">

                                    <!-- Timestamp -->
                                    <div class="w-24 shrink-0 text-xs text-gray-500 font-mono pl-2">
                                        {{ $trace->timestamp->format('H:i:s') }}
                                    </div>

                                    <!-- Diagram area -->
                                    <div class="flex-1 flex relative">
                                        <!-- 3 columnas -->
                                        <div class="flex-1 relative flex items-center justify-center">
                                            @if($startCol === 0 && $spanCols > 0)
                                                <!-- Flecha desde/hacia cliente -->
                                                <div class="absolute inset-y-0 flex items-center {{ $goingRight ? 'left-1/2 right-0' : 'left-0 right-1/2' }}">
                                                    <div class="w-full h-0.5 {{ $goingRight ? 'bg-gradient-to-r' : 'bg-gradient-to-l' }} from-purple-400 to-blue-400"></div>
                                                </div>
                                                @if(!$goingRight)
                                                    <svg class="absolute left-2 w-3 h-3 text-purple-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                @endif
                                            @endif
                                        </div>

                                        <div class="flex-1 relative flex items-center justify-center">
                                            @if($spanCols > 0)
                                                @if($startCol === 0 && $spanCols === 2)
                                                    <!-- Flecha atraviesa kamailio -->
                                                    <div class="absolute inset-y-0 inset-x-0 flex items-center">
                                                        <div class="w-full h-0.5 {{ $goingRight ? 'bg-blue-400' : 'bg-blue-400' }}"></div>
                                                    </div>
                                                @elseif($startCol === 0 && $spanCols === 1)
                                                    <!-- Flecha termina/empieza en kamailio (desde cliente) -->
                                                    <div class="absolute inset-y-0 flex items-center {{ $goingRight ? 'left-0 right-1/2' : 'left-1/2 right-0' }}">
                                                        <div class="w-full h-0.5 {{ $goingRight ? 'bg-gradient-to-r from-blue-400 to-blue-400' : 'bg-gradient-to-l from-blue-400 to-blue-400' }}"></div>
                                                    </div>
                                                    @if($goingRight)
                                                        <svg class="absolute left-1/2 -ml-1.5 w-3 h-3 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                                    @endif
                                                @elseif($startCol === 1 && $spanCols === 1)
                                                    <!-- Flecha desde kamailio hacia carrier -->
                                                    <div class="absolute inset-y-0 flex items-center {{ $goingRight ? 'left-1/2 right-0' : 'left-0 right-1/2' }}">
                                                        <div class="w-full h-0.5 {{ $goingRight ? 'bg-gradient-to-r from-blue-400 to-green-400' : 'bg-gradient-to-l from-green-400 to-blue-400' }}"></div>
                                                    </div>
                                                    @if(!$goingRight)
                                                        <svg class="absolute left-1/2 -ml-1.5 w-3 h-3 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                    @endif
                                                @endif
                                            @endif

                                            <!-- Label del mensaje (centrado en la flecha) -->
                                            @if($spanCols > 0)
                                                <span class="relative z-10 px-2 py-0.5 text-xs font-bold rounded shadow-lg {{ $colorClass }}">
                                                    {{ $label }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex-1 relative flex items-center justify-center">
                                            @if(($startCol === 1 && $spanCols === 1 && $goingRight) || ($startCol === 0 && $spanCols === 2))
                                                <!-- Flecha hacia carrier -->
                                                <div class="absolute inset-y-0 flex items-center {{ $goingRight ? 'left-0 right-1/2' : 'left-1/2 right-0' }}">
                                                    <div class="w-full h-0.5 bg-green-400"></div>
                                                </div>
                                                @if($goingRight)
                                                    <svg class="absolute right-1/2 mr-0 w-3 h-3 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                                @endif
                                            @elseif($startCol === 2 || (!$goingRight && $toPos === 1 && $fromPos === 2))
                                                <!-- Flecha desde carrier -->
                                                <div class="absolute inset-y-0 flex items-center left-1/2 right-0">
                                                    <div class="w-full h-0.5 bg-gradient-to-l from-green-400 to-blue-400"></div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Detalle del mensaje (oculto por defecto) -->
                                <div id="trace-{{ $index }}" class="hidden mx-2 mb-2 ml-24 p-3 bg-gray-900 rounded-lg border border-gray-700 relative z-10">
                                    <div class="flex justify-between items-center mb-2">
                                        <div class="flex items-center gap-4 text-xs">
                                            <span class="text-purple-400">{{ $trace->source_ip }}:{{ $trace->source_port }}</span>
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                            <span class="text-green-400">{{ $trace->dest_ip }}:{{ $trace->dest_port }}</span>
                                            <span class="text-gray-600">|</span>
                                            <span class="text-gray-500">{{ $trace->transport }}</span>
                                        </div>
                                        <button onclick="event.stopPropagation(); navigator.clipboard.writeText(document.getElementById('msg-{{ $index }}').innerText).then(() => this.textContent = 'Copiado!')"
                                                class="text-xs bg-gray-700 hover:bg-gray-600 text-gray-300 px-2 py-1 rounded transition-colors">
                                            Copiar
                                        </button>
                                    </div>
                                    <pre id="msg-{{ $index }}" class="text-xs font-mono text-gray-300 whitespace-pre-wrap overflow-x-auto max-h-48 bg-black/30 p-2 rounded">{{ $trace->sip_message }}</pre>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="mt-6 dark-card p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-yellow-500/50 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <h3 class="text-lg font-medium text-yellow-400">No hay trazas SIP disponibles</h3>
                <p class="text-sm text-gray-500 mt-1">Las trazas pueden no estar habilitadas o ya fueron eliminadas.</p>
            </div>
            @endif

            <div class="mt-6">
                <a href="{{ route('cdrs.index') }}" class="text-blue-400 hover:text-blue-300 text-sm">← Volver a CDRs</a>
            </div>
        </div>
    </div>
</x-app-layout>
