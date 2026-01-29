<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Alerts
                @if($unacknowledgedCount > 0)
                    <span class="ml-2 px-2 py-1 text-sm font-bold text-white bg-red-600 rounded-full">{{ $unacknowledgedCount }}</span>
                @endif
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <form action="{{ route('alerts.index') }}" method="GET" class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Severity</label>
                            <select name="severity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">All</option>
                                <option value="info" {{ request('severity') === 'info' ? 'selected' : '' }}>Info</option>
                                <option value="warning" {{ request('severity') === 'warning' ? 'selected' : '' }}>Warning</option>
                                <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Type</label>
                            <select name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">All Types</option>
                                <option value="carrier_down" {{ request('type') === 'carrier_down' ? 'selected' : '' }}>Carrier Down</option>
                                <option value="carrier_recovered" {{ request('type') === 'carrier_recovered' ? 'selected' : '' }}>Carrier Recovered</option>
                                <option value="high_failure_rate" {{ request('type') === 'high_failure_rate' ? 'selected' : '' }}>High Failure Rate</option>
                                <option value="channels_exceeded" {{ request('type') === 'channels_exceeded' ? 'selected' : '' }}>Channels Exceeded</option>
                                <option value="minutes_warning" {{ request('type') === 'minutes_warning' ? 'selected' : '' }}>Minutes Warning</option>
                                <option value="security_ip_blocked" {{ request('type') === 'security_ip_blocked' ? 'selected' : '' }}>IP Blocked</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Status</label>
                            <select name="acknowledged" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">All</option>
                                <option value="0" {{ request('acknowledged') === '0' ? 'selected' : '' }}>Unacknowledged</option>
                                <option value="1" {{ request('acknowledged') === '1' ? 'selected' : '' }}>Acknowledged</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">From</label>
                            <input type="date" name="from" value="{{ request('from') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">Filter</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Alerts Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Severity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($alerts as $alert)
                                <tr class="{{ !$alert->acknowledged ? 'bg-yellow-50' : '' }}">
                                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">{{ $alert->created_at->format('m-d H:i') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            {{ $alert->severity === 'critical' ? 'bg-red-100 text-red-800' :
                                               ($alert->severity === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                            {{ ucfirst($alert->severity) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ str_replace('_', ' ', $alert->type) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ Str::limit($alert->title, 50) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $alert->source_name ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if($alert->acknowledged)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Ack</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">New</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <a href="{{ route('alerts.show', $alert) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                        @if(!$alert->acknowledged)
                                            <form action="{{ route('alerts.acknowledge', $alert) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900">Ack</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No alerts found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t">{{ $alerts->withQueryString()->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
