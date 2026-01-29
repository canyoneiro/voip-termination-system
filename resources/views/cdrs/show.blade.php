<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">CDR Details</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Call Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Call Information</h3>
                    <dl class="grid grid-cols-2 gap-4">
                        <div><dt class="text-sm text-gray-500">UUID</dt><dd class="font-mono text-xs">{{ $cdr->uuid }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Call-ID</dt><dd class="font-mono text-xs break-all">{{ $cdr->call_id }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Customer</dt><dd class="font-medium">{{ $cdr->customer->name ?? '-' }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Carrier</dt><dd class="font-medium">{{ $cdr->carrier->name ?? '-' }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Caller</dt><dd class="font-mono">{{ $cdr->caller }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Caller Original</dt><dd class="font-mono">{{ $cdr->caller_original }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Callee</dt><dd class="font-mono">{{ $cdr->callee }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Callee Original</dt><dd class="font-mono">{{ $cdr->callee_original }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Source IP</dt><dd class="font-mono">{{ $cdr->source_ip }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Destination IP</dt><dd class="font-mono">{{ $cdr->destination_ip ?? '-' }}</dd></div>
                    </dl>
                </div>

                <!-- Timing -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Timing</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between"><dt class="text-sm text-gray-500">Start Time</dt><dd class="font-mono text-sm">{{ $cdr->start_time }}</dd></div>
                        <div class="flex justify-between"><dt class="text-sm text-gray-500">Progress Time</dt><dd class="font-mono text-sm">{{ $cdr->progress_time ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-sm text-gray-500">Answer Time</dt><dd class="font-mono text-sm">{{ $cdr->answer_time ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-sm text-gray-500">End Time</dt><dd class="font-mono text-sm">{{ $cdr->end_time ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-sm text-gray-500">Duration</dt><dd class="font-medium">{{ gmdate('H:i:s', $cdr->duration) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-sm text-gray-500">Billable Duration</dt><dd class="font-medium">{{ gmdate('H:i:s', $cdr->billable_duration) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-sm text-gray-500">PDD</dt><dd class="font-medium">{{ $cdr->pdd ? $cdr->pdd . ' ms' : '-' }}</dd></div>
                    </dl>
                </div>

                <!-- Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Status</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-500">SIP Code</dt>
                            <dd>
                                @if($cdr->answer_time)
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">{{ $cdr->sip_code }}</span>
                                @else
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">{{ $cdr->sip_code }}</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between"><dt class="text-sm text-gray-500">SIP Reason</dt><dd>{{ $cdr->sip_reason ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-sm text-gray-500">Hangup Cause</dt><dd>{{ $cdr->hangup_cause ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-sm text-gray-500">Hangup SIP Code</dt><dd>{{ $cdr->hangup_sip_code ?? '-' }}</dd></div>
                    </dl>
                </div>

                <!-- Technical -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Technical Details</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between"><dt class="text-sm text-gray-500">Codecs Offered</dt><dd class="font-mono text-sm">{{ $cdr->codecs_offered ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-sm text-gray-500">Codec Used</dt><dd class="font-mono">{{ $cdr->codec_used ?? '-' }}</dd></div>
                        <div><dt class="text-sm text-gray-500">User-Agent</dt><dd class="font-mono text-xs break-all mt-1">{{ $cdr->user_agent ?? '-' }}</dd></div>
                    </dl>
                </div>
            </div>

            <!-- SIP Traces -->
            @if($traces->count() > 0)
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">SIP Trace</h3>
                    <div class="space-y-2">
                        @foreach($traces as $trace)
                            <div class="border rounded p-3 {{ $trace->direction === 'in' ? 'border-l-4 border-l-blue-500' : 'border-r-4 border-r-green-500' }}">
                                <div class="flex justify-between items-center mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 text-xs font-semibold rounded {{ $trace->response_code ? ($trace->response_code < 400 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') : 'bg-blue-100 text-blue-800' }}">
                                            {{ $trace->response_code ?? $trace->method }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $trace->direction === 'in' ? 'Incoming' : 'Outgoing' }}</span>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $trace->timestamp }}</span>
                                </div>
                                <div class="text-xs text-gray-600">
                                    {{ $trace->source_ip }}:{{ $trace->source_port }} -> {{ $trace->dest_ip }}:{{ $trace->dest_port }}
                                </div>
                                <details class="mt-2">
                                    <summary class="text-xs text-indigo-600 cursor-pointer">View message</summary>
                                    <pre class="mt-2 p-2 bg-gray-100 rounded text-xs overflow-x-auto">{{ $trace->sip_message }}</pre>
                                </details>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <div class="mt-6">
                <a href="{{ route('cdrs.index') }}" class="text-indigo-600 hover:text-indigo-800">&larr; Back to CDRs</a>
            </div>
        </div>
    </div>
</x-app-layout>
