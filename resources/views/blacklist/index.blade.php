<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">IP Blacklist</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <!-- Add IP Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <form action="{{ route('blacklist.store') }}" method="POST" class="p-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500">IP Address</label>
                            <input type="text" name="ip_address" required placeholder="192.168.1.1"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('ip_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Reason</label>
                            <input type="text" name="reason" required placeholder="Reason for blocking"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Expires At (optional)</label>
                            <input type="datetime-local" name="expires_at"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div class="flex items-end gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="permanent" value="1"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Permanent</span>
                            </label>
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">Block IP</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Blacklist Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Attempts</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expires</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($blacklist as $entry)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">{{ $entry->ip_address }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($entry->reason, 40) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $entry->source }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $entry->attempts }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($entry->permanent)
                                            <span class="text-red-600 font-medium">Permanent</span>
                                        @elseif($entry->expires_at)
                                            {{ \Carbon\Carbon::parse($entry->expires_at)->diffForHumans() }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($entry->permanent)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Permanent</span>
                                        @elseif($entry->expires_at && \Carbon\Carbon::parse($entry->expires_at)->isPast())
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Expired</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Active</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <form action="{{ route('blacklist.toggle-permanent', $entry) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                {{ $entry->permanent ? 'Make Temp' : 'Make Perm' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('blacklist.destroy', $entry) }}" method="POST" class="inline" onsubmit="return confirm('Remove this IP from blacklist?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">No blocked IPs</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t">{{ $blacklist->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
