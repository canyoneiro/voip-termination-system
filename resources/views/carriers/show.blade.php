<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $carrier->name }}</h2>
            <a href="{{ route('carriers.edit', $carrier) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">Edit</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Carrier Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Connection Info</h3>
                    <dl class="space-y-3">
                        <div><dt class="text-sm text-gray-500">Host</dt><dd class="font-medium">{{ $carrier->host }}:{{ $carrier->port }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Transport</dt><dd class="font-medium uppercase">{{ $carrier->transport }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Priority</dt><dd class="font-medium">{{ $carrier->priority }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Weight</dt><dd class="font-medium">{{ $carrier->weight }}</dd></div>
                        <div><dt class="text-sm text-gray-500">State</dt>
                            <dd>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $carrier->state === 'active' ? 'bg-green-100 text-green-800' :
                                       ($carrier->state === 'probing' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($carrier->state) }}
                                </span>
                            </dd>
                        </div>
                        <div><dt class="text-sm text-gray-500">Last OPTIONS</dt><dd class="text-sm">{{ $carrier->last_options_time ?? 'Never' }}</dd></div>
                    </dl>
                </div>

                <!-- Limits & Usage -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Limits & Usage</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm text-gray-500">Channels</dt>
                            <dd class="mt-1">
                                <div class="flex justify-between text-sm mb-1">
                                    <span>{{ $carrier->activeCalls->count() }} / {{ $carrier->max_channels }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min(100, ($carrier->activeCalls->count() / $carrier->max_channels) * 100) }}%"></div>
                                </div>
                            </dd>
                        </div>
                        <div><dt class="text-sm text-gray-500">Max CPS</dt><dd class="font-medium">{{ $carrier->max_cps }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Daily Calls</dt><dd class="font-medium">{{ $carrier->daily_calls }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Daily Minutes</dt><dd class="font-medium">{{ $carrier->daily_minutes }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Daily Failed</dt><dd class="font-medium">{{ $carrier->daily_failed }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Failover Count</dt><dd class="font-medium">{{ $carrier->failover_count }}</dd></div>
                    </dl>
                </div>

                <!-- Manipulation -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Call Manipulation</h3>
                    <dl class="space-y-3">
                        <div><dt class="text-sm text-gray-500">Tech Prefix</dt><dd class="font-medium font-mono">{{ $carrier->tech_prefix ?? 'None' }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Strip Digits</dt><dd class="font-medium">{{ $carrier->strip_digits }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Codecs</dt><dd class="font-medium">{{ $carrier->codecs ?? 'Default' }}</dd></div>
                        <div>
                            <dt class="text-sm text-gray-500">Allowed Prefixes</dt>
                            <dd class="font-mono text-xs mt-1">{{ $carrier->prefix_filter ?: 'All allowed' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Denied Prefixes</dt>
                            <dd class="font-mono text-xs mt-1">{{ $carrier->prefix_deny ?: 'None' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Recent CDRs -->
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Recent Calls</h3>
                        <a href="{{ route('cdrs.index', ['carrier_id' => $carrier->id]) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View all &rarr;</a>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Caller</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Callee</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($recentCdrs as $cdr)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $cdr->start_time->format('Y-m-d H:i:s') }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $cdr->customer->name ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $cdr->caller }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $cdr->callee }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ gmdate('i:s', $cdr->duration) }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        @if($cdr->answer_time)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ $cdr->sip_code }}</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $cdr->sip_code }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-4 text-center text-gray-500">No calls yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
