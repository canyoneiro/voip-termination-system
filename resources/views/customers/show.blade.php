<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $customer->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('customers.edit', $customer) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">Edit</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Customer Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Info</h3>
                    <dl class="space-y-3">
                        <div><dt class="text-sm text-gray-500">Company</dt><dd class="font-medium">{{ $customer->company ?? '-' }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Email</dt><dd class="font-medium">{{ $customer->email ?? '-' }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Phone</dt><dd class="font-medium">{{ $customer->phone ?? '-' }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Status</dt>
                            <dd>
                                @if($customer->active)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                @endif
                            </dd>
                        </div>
                        <div><dt class="text-sm text-gray-500">UUID</dt><dd class="font-mono text-xs">{{ $customer->uuid }}</dd></div>
                    </dl>
                </div>

                <!-- Limits -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Limits & Usage</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm text-gray-500">Channels</dt>
                            <dd class="mt-1">
                                <div class="flex justify-between text-sm mb-1">
                                    <span>{{ $customer->activeCalls->count() }} / {{ $customer->max_channels }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min(100, ($customer->activeCalls->count() / $customer->max_channels) * 100) }}%"></div>
                                </div>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">CPS Limit</dt>
                            <dd class="font-medium">{{ $customer->max_cps }}</dd>
                        </div>
                        @if($customer->max_daily_minutes)
                        <div>
                            <dt class="text-sm text-gray-500">Daily Minutes</dt>
                            <dd class="mt-1">
                                <div class="flex justify-between text-sm mb-1">
                                    <span>{{ $customer->used_daily_minutes }} / {{ $customer->max_daily_minutes }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min(100, ($customer->used_daily_minutes / $customer->max_daily_minutes) * 100) }}%"></div>
                                </div>
                            </dd>
                        </div>
                        @endif
                        @if($customer->max_monthly_minutes)
                        <div>
                            <dt class="text-sm text-gray-500">Monthly Minutes</dt>
                            <dd class="mt-1">
                                <div class="flex justify-between text-sm mb-1">
                                    <span>{{ $customer->used_monthly_minutes }} / {{ $customer->max_monthly_minutes }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min(100, ($customer->used_monthly_minutes / $customer->max_monthly_minutes) * 100) }}%"></div>
                                </div>
                            </dd>
                        </div>
                        @endif
                    </dl>
                    <form action="{{ route('customers.reset-minutes', $customer) }}" method="POST" class="mt-4">
                        @csrf
                        <input type="hidden" name="type" value="both">
                        <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800" onclick="return confirm('Reset minute counters?')">Reset Minutes</button>
                    </form>
                </div>

                <!-- IPs -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Authorized IPs</h3>
                    <div class="space-y-2 mb-4">
                        @forelse($customer->ips as $ip)
                            <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                <div>
                                    <span class="font-mono text-sm">{{ $ip->ip_address }}</span>
                                    @if($ip->description)<span class="text-xs text-gray-500 ml-2">{{ $ip->description }}</span>@endif
                                </div>
                                <form action="{{ route('customers.remove-ip', [$customer, $ip]) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs" onclick="return confirm('Remove this IP?')">Remove</button>
                                </form>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">No IPs configured</p>
                        @endforelse
                    </div>
                    <form action="{{ route('customers.add-ip', $customer) }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="ip_address" placeholder="IP Address" required
                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <input type="text" name="description" placeholder="Description"
                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-md text-sm">Add</button>
                    </form>
                </div>
            </div>

            <!-- Recent CDRs -->
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Recent Calls</h3>
                        <a href="{{ route('cdrs.index', ['customer_id' => $customer->id]) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View all &rarr;</a>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Caller</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Callee</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Carrier</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($recentCdrs as $cdr)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $cdr->start_time->format('Y-m-d H:i:s') }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $cdr->caller }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $cdr->callee }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ gmdate('i:s', $cdr->duration) }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $cdr->carrier->name ?? '-' }}</td>
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
