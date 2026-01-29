<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">CDRs</h2>
            <a href="{{ route('cdrs.export', request()->query()) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">Export CSV</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <form action="{{ route('cdrs.index') }}" method="GET" class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500">From</label>
                            <input type="date" name="from" value="{{ request('from') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">To</label>
                            <input type="date" name="to" value="{{ request('to') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Customer</label>
                            <select name="customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Carrier</label>
                            <select name="carrier_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">All Carriers</option>
                                @foreach($carriers as $carrier)
                                    <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>{{ $carrier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Status</label>
                            <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">All</option>
                                <option value="answered" {{ request('status') === 'answered' ? 'selected' : '' }}>Answered</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">Filter</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Stats Summary -->
            @if($stats)
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-sm text-gray-500">Total Calls</div>
                    <div class="text-2xl font-bold">{{ number_format($stats->total ?? 0) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-sm text-gray-500">Answered</div>
                    <div class="text-2xl font-bold text-green-600">{{ number_format($stats->answered ?? 0) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-sm text-gray-500">Total Duration</div>
                    <div class="text-2xl font-bold">{{ gmdate('H:i:s', $stats->total_duration ?? 0) }}</div>
                </div>
            </div>
            @endif

            <!-- CDRs Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Caller</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Callee</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">PDD</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Carrier</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($cdrs as $cdr)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">{{ $cdr->start_time->format('m-d H:i:s') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $cdr->customer->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-mono">{{ $cdr->caller }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-mono">{{ $cdr->callee }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ gmdate('i:s', $cdr->duration) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $cdr->pdd ? $cdr->pdd . 'ms' : '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $cdr->carrier->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($cdr->answer_time)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ $cdr->sip_code }}</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $cdr->sip_code }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <a href="{{ route('cdrs.show', $cdr) }}" class="text-indigo-600 hover:text-indigo-900">Details</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">No CDRs found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t">{{ $cdrs->withQueryString()->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
