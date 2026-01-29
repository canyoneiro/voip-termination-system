<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Active Calls -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Active Calls</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $activeCallsCount }}</div>
                </div>

                <!-- CPS -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Current CPS</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $cps }}</div>
                </div>

                <!-- ASR Today -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">ASR Today</div>
                    <div class="mt-1 text-3xl font-semibold {{ $asr >= 50 ? 'text-green-600' : ($asr >= 30 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ $asr }}%
                    </div>
                </div>

                <!-- ACD Today -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">ACD Today</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900">{{ gmdate('i:s', $acd) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Carriers Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Carriers Status</h3>
                        <div class="space-y-3">
                            @forelse($carriers as $carrier)
                                <div class="flex items-center justify-between p-3 rounded-lg {{ $carrier->state === 'active' ? 'bg-green-50' : ($carrier->state === 'probing' ? 'bg-yellow-50' : 'bg-red-50') }}">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $carrier->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $carrier->host }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $carrier->state === 'active' ? 'bg-green-100 text-green-800' :
                                                   ($carrier->state === 'probing' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($carrier->state) }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $carrier->active_calls_count }}/{{ $carrier->max_channels }} ch
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-gray-500 text-center py-4">No carriers configured</div>
                            @endforelse
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('carriers.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View all carriers &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Active Calls -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Active Calls</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Caller</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Callee</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($activeCalls->take(10) as $call)
                                        <tr>
                                            <td class="px-3 py-2 text-sm text-gray-900">{{ $call->customer->name ?? 'Unknown' }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-500">{{ $call->caller }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-500">{{ $call->callee }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-500">
                                                {{ $call->start_time->diffForHumans(null, true) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-3 py-4 text-sm text-gray-500 text-center">No active calls</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Alerts -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Alerts</h3>
                        <div class="space-y-3">
                            @forelse($alerts as $alert)
                                <div class="p-3 rounded-lg {{ $alert->severity === 'critical' ? 'bg-red-50' : ($alert->severity === 'warning' ? 'bg-yellow-50' : 'bg-blue-50') }}">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                {{ $alert->severity === 'critical' ? 'bg-red-100 text-red-800' :
                                                   ($alert->severity === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                                {{ ucfirst($alert->severity) }}
                                            </span>
                                            <span class="ml-2 text-xs text-gray-500">{{ $alert->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-1 text-sm text-gray-900">{{ $alert->title }}</div>
                                </div>
                            @empty
                                <div class="text-gray-500 text-center py-4">No unacknowledged alerts</div>
                            @endforelse
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('alerts.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View all alerts &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Customers -->
            @if($topCustomers->count() > 0)
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Top Customers by Active Calls</h3>
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        @foreach($topCustomers as $customer)
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-indigo-600">{{ $customer->active_calls_count }}</div>
                                <div class="text-sm text-gray-600">{{ $customer->name }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
