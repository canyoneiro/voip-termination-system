<x-portal.layouts.app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Detalle de Llamada
            </h2>
            <a href="{{ route('portal.cdrs.index') }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Call Info -->
                <div class="dark-card p-6">
                    <h3 class="font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">Informacion de la Llamada</h3>

                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">UUID</dt>
                            <dd class="mono text-sm text-slate-800">{{ $cdr->uuid }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Call-ID</dt>
                            <dd class="mono text-sm text-slate-800">{{ Str::limit($cdr->call_id, 30) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">IP Origen</dt>
                            <dd class="mono text-slate-800">{{ $cdr->source_ip }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Caller</dt>
                            <dd class="mono text-slate-800">{{ $cdr->caller }}</dd>
                        </div>
                        @if($cdr->caller !== $cdr->caller_original)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Caller Original</dt>
                            <dd class="mono text-slate-800">{{ $cdr->caller_original }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Callee</dt>
                            <dd class="mono text-slate-800">{{ $cdr->callee }}</dd>
                        </div>
                        @if($cdr->callee !== $cdr->callee_original)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Callee Original</dt>
                            <dd class="mono text-slate-800">{{ $cdr->callee_original }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Timing Info -->
                <div class="dark-card p-6">
                    <h3 class="font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">Tiempos</h3>

                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Inicio</dt>
                            <dd class="mono text-slate-800">{{ $cdr->start_time->format('Y-m-d H:i:s.v') }}</dd>
                        </div>
                        @if($cdr->progress_time)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Progress</dt>
                            <dd class="mono text-slate-800">{{ $cdr->progress_time->format('Y-m-d H:i:s.v') }}</dd>
                        </div>
                        @endif
                        @if($cdr->answer_time)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Respuesta</dt>
                            <dd class="mono text-slate-800">{{ $cdr->answer_time->format('Y-m-d H:i:s.v') }}</dd>
                        </div>
                        @endif
                        @if($cdr->end_time)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Fin</dt>
                            <dd class="mono text-slate-800">{{ $cdr->end_time->format('Y-m-d H:i:s.v') }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Duracion</dt>
                            <dd class="text-slate-800 font-medium">{{ gmdate('H:i:s', $cdr->duration) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Duracion Facturable</dt>
                            <dd class="text-slate-800 font-medium">{{ gmdate('H:i:s', $cdr->billable_duration) }}</dd>
                        </div>
                        @if($cdr->pdd)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">PDD</dt>
                            <dd class="text-slate-800">{{ $cdr->pdd }} ms</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- SIP Info -->
                <div class="dark-card p-6">
                    <h3 class="font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">Informacion SIP</h3>

                    <dl class="space-y-3">
                        <div class="flex justify-between items-center">
                            <dt class="text-slate-500">Codigo SIP</dt>
                            <dd>
                                @if($cdr->sip_code == 200)
                                    <span class="badge badge-green">{{ $cdr->sip_code }} OK</span>
                                @elseif($cdr->sip_code >= 400 && $cdr->sip_code < 500)
                                    <span class="badge badge-yellow">{{ $cdr->sip_code }}</span>
                                @elseif($cdr->sip_code >= 500)
                                    <span class="badge badge-red">{{ $cdr->sip_code }}</span>
                                @else
                                    <span class="badge badge-gray">{{ $cdr->sip_code }}</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Razon</dt>
                            <dd class="text-slate-800">{{ $cdr->sip_reason ?? '-' }}</dd>
                        </div>
                        @if($cdr->hangup_cause)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Causa Colgado</dt>
                            <dd class="text-slate-800">{{ ucfirst($cdr->hangup_cause) }}</dd>
                        </div>
                        @endif
                        @if($cdr->hangup_sip_code)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Codigo Colgado</dt>
                            <dd class="text-slate-800">{{ $cdr->hangup_sip_code }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Technical Info -->
                <div class="dark-card p-6">
                    <h3 class="font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">Informacion Tecnica</h3>

                    <dl class="space-y-3">
                        @if($cdr->codec_used)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Codec Usado</dt>
                            <dd class="mono text-slate-800">{{ $cdr->codec_used }}</dd>
                        </div>
                        @endif
                        @if($cdr->codecs_offered)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Codecs Ofrecidos</dt>
                            <dd class="text-slate-800 text-right text-sm">{{ $cdr->codecs_offered }}</dd>
                        </div>
                        @endif
                        @if($cdr->user_agent)
                        <div>
                            <dt class="text-slate-500 mb-1">User-Agent</dt>
                            <dd class="mono text-xs text-slate-800 bg-slate-100 p-2 rounded break-all">{{ $cdr->user_agent }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Call Timeline -->
            <div class="dark-card p-6 mt-6">
                <h3 class="font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">Timeline</h3>

                <div class="flex items-center justify-between overflow-x-auto pb-4">
                    @php
                        $events = [];
                        $events[] = ['time' => $cdr->start_time, 'label' => 'INVITE', 'color' => 'blue'];
                        if ($cdr->progress_time) {
                            $events[] = ['time' => $cdr->progress_time, 'label' => '183 Progress', 'color' => 'yellow'];
                        }
                        if ($cdr->answer_time) {
                            $events[] = ['time' => $cdr->answer_time, 'label' => '200 OK', 'color' => 'green'];
                        }
                        if ($cdr->end_time) {
                            $events[] = ['time' => $cdr->end_time, 'label' => 'BYE', 'color' => 'gray'];
                        }
                    @endphp

                    @foreach($events as $index => $event)
                        <div class="flex flex-col items-center min-w-[120px]">
                            <div class="w-4 h-4 rounded-full bg-{{ $event['color'] }}-500 mb-2"></div>
                            <span class="text-sm font-medium text-slate-800">{{ $event['label'] }}</span>
                            <span class="mono text-xs text-slate-500">{{ $event['time']->format('H:i:s.v') }}</span>
                        </div>
                        @if($index < count($events) - 1)
                            <div class="flex-1 h-0.5 bg-slate-200 mx-2"></div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-portal.layouts.app>
