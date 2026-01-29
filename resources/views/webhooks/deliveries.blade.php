<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Webhook Deliveries: {{ Str::limit($webhook->url, 40) }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Attempts</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Response</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($deliveries as $delivery)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">{{ $delivery->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $delivery->event }}</td>
                                    <td class="px-4 py-3">
                                        @if($delivery->success)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Success</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $delivery->attempts }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($delivery->response_code)
                                            <span class="px-2 py-1 text-xs rounded {{ $delivery->response_code < 400 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $delivery->response_code }}
                                            </span>
                                        @endif
                                        @if($delivery->response_body)
                                            <details class="mt-1">
                                                <summary class="text-xs text-indigo-600 cursor-pointer">View response</summary>
                                                <pre class="mt-1 p-2 bg-gray-100 rounded text-xs overflow-x-auto max-w-md">{{ Str::limit($delivery->response_body, 500) }}</pre>
                                            </details>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No deliveries yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t">{{ $deliveries->links() }}</div>
            </div>

            <div class="mt-6">
                <a href="{{ route('webhooks.show', $webhook) }}" class="text-indigo-600 hover:text-indigo-800">&larr; Back to Webhook</a>
            </div>
        </div>
    </div>
</x-app-layout>
