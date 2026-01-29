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
            <div class="mt-6 dark-card overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-700/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-white">Diagrama de Senalizacion SIP</h3>
                        <p class="text-xs text-gray-500">Flujo completo de mensajes SIP</p>
                    </div>
                    <span class="badge badge-blue">{{ $traces->count() }} mensajes</span>
                </div>
                <div class="p-6">
                    <!-- Headers -->
                    <div class="flex justify-around text-center mb-6 pb-4 border-b border-gray-700/50">
                        <div class="w-1/3">
                            <div class="font-bold text-white">Cliente</div>
                            <div class="text-xs text-gray-500 font-mono">{{ $cdr->source_ip }}</div>
                        </div>
                        <div class="w-1/3">
                            <div class="font-bold text-blue-400">Kamailio</div>
                            <div class="text-xs text-gray-500 font-mono">{{ config('app.url') }}</div>
                        </div>
                        <div class="w-1/3">
                            <div class="font-bold text-white">Carrier</div>
                            <div class="text-xs text-gray-500 font-mono">{{ $cdr->destination_ip ?? '-' }}</div>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div class="space-y-2">
                        @foreach($traces as $index => $trace)
                            @php
                                $isIncoming = $trace->direction === 'in';
                                $method = $trace->response_code ? $trace->response_code : $trace->method;
                                $colors = match(true) {
                                    $trace->response_code && $trace->response_code >= 200 && $trace->response_code < 300 => 'bg-green-500',
                                    $trace->response_code && $trace->response_code >= 100 && $trace->response_code < 200 => 'bg-gray-500',
                                    $trace->response_code && $trace->response_code >= 300 && $trace->response_code < 400 => 'bg-yellow-500',
                                    $trace->response_code && $trace->response_code >= 400 => 'bg-red-500',
                                    $trace->method === 'INVITE' || $trace->method === 'ACK' => 'bg-blue-500',
                                    $trace->method === 'BYE' || $trace->method === 'CANCEL' => 'bg-orange-500',
                                    default => 'bg-gray-500'
                                };
                            @endphp
                            <div class="flex items-center justify-center py-2 hover:bg-gray-800/50 rounded cursor-pointer transition-colors"
                                 onclick="document.getElementById('trace-{{ $index }}').classList.toggle('hidden')">
                                <div class="w-20 text-xs text-gray-500 font-mono">{{ $trace->timestamp->format('H:i:s.v') }}</div>
                                @if($isIncoming)
                                    <div class="flex items-center flex-1">
                                        <div class="flex-1 flex items-center justify-end pr-2">
                                            <span class="px-2 py-0.5 text-xs font-bold rounded text-white {{ $colors }}">{{ $method }}</span>
                                        </div>
                                        <div class="w-32 h-0.5 bg-blue-500" style="background: linear-gradient(to left, transparent, #3b82f6)"></div>
                                        <div class="flex-1"></div>
                                    </div>
                                @else
                                    <div class="flex items-center flex-1">
                                        <div class="flex-1"></div>
                                        <div class="w-32 h-0.5 bg-blue-500" style="background: linear-gradient(to right, transparent, #3b82f6)"></div>
                                        <div class="flex-1 flex items-center pl-2">
                                            <span class="px-2 py-0.5 text-xs font-bold rounded text-white {{ $colors }}">{{ $method }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div id="trace-{{ $index }}" class="hidden mx-4 mb-4 p-4 bg-gray-900 rounded-lg border border-gray-700">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-gray-400 text-xs">{{ $trace->source_ip }}:{{ $trace->source_port }} → {{ $trace->dest_ip }}:{{ $trace->dest_port }}</span>
                                    <button onclick="navigator.clipboard.writeText(document.getElementById('msg-{{ $index }}').innerText)" class="text-xs bg-gray-700 hover:bg-gray-600 text-gray-300 px-2 py-1 rounded">Copiar</button>
                                </div>
                                <pre id="msg-{{ $index }}" class="text-xs font-mono text-gray-300 whitespace-pre-wrap overflow-x-auto max-h-64 scrollbar-dark">{{ $trace->sip_message }}</pre>
                            </div>
                        @endforeach
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
