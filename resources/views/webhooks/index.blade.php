<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Webhooks</h2>
            <a href="{{ route('webhooks.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">Add Webhook</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">URL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Events</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Trigger</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($webhooks as $webhook)
                                <tr>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('webhooks.show', $webhook) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            {{ Str::limit($webhook->url, 50) }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $webhook->customer->name ?? 'Global' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ count($webhook->events ?? []) }} events
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        @if($webhook->last_triggered_at)
                                            {{ $webhook->last_triggered_at->diffForHumans() }}
                                            @if($webhook->last_status_code)
                                                <span class="ml-1 px-1.5 py-0.5 text-xs rounded {{ $webhook->last_status_code < 400 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $webhook->last_status_code }}
                                                </span>
                                            @endif
                                        @else
                                            Never
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($webhook->active)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                        @endif
                                        @if($webhook->failure_count > 0)
                                            <span class="ml-1 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $webhook->failure_count }} failures</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <a href="{{ route('webhooks.edit', $webhook) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <form action="{{ route('webhooks.destroy', $webhook) }}" method="POST" class="inline" onsubmit="return confirm('Delete this webhook?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No webhooks configured</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t">{{ $webhooks->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
