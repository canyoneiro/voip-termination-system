<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Carriers</h2>
            <a href="{{ route('carriers.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">Add Carrier</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Host</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Channels</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">State</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($carriers as $carrier)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('carriers.show', $carrier) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">{{ $carrier->name }}</a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $carrier->host }}:{{ $carrier->port }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $carrier->priority }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="{{ $carrier->active_calls_count >= $carrier->max_channels * 0.8 ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ $carrier->active_calls_count }}/{{ $carrier->max_channels }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $carrier->state === 'active' ? 'bg-green-100 text-green-800' :
                                               ($carrier->state === 'probing' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($carrier->state) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('carriers.edit', $carrier) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <form action="{{ route('carriers.destroy', $carrier) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No carriers found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t">{{ $carriers->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
