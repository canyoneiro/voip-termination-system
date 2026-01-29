<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Webhook Details</h2>
            <div class="flex gap-2">
                <form action="{{ route('webhooks.test', $webhook) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">Send Test</button>
                </form>
                <a href="{{ route('webhooks.edit', $webhook) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">Edit</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            @if(session('new_secret'))
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded">
                    <h4 class="font-bold">Save your webhook secret!</h4>
                    <p class="text-sm mt-1">This will only be shown once:</p>
                    <code class="block mt-2 p-2 bg-yellow-50 rounded font-mono text-sm break-all">{{ session('new_secret') }}</code>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Webhook Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Configuration</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm text-gray-500">URL</dt>
                            <dd class="font-mono text-sm break-all">{{ $webhook->url }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Customer</dt>
                            <dd class="font-medium">{{ $webhook->customer->name ?? 'Global (all customers)' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Status</dt>
                            <dd>
                                @if($webhook->active)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">UUID</dt>
                            <dd class="font-mono text-xs">{{ $webhook->uuid }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Created</dt>
                            <dd class="text-sm">{{ $webhook->created_at }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 pt-4 border-t">
                        <form action="{{ route('webhooks.regenerate-secret', $webhook) }}" method="POST" onsubmit="return confirm('This will invalidate the current secret. Continue?')">
                            @csrf
                            <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800">Regenerate Secret</button>
                        </form>
                    </div>
                </div>

                <!-- Events & Stats -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Subscribed Events</h3>
                    <div class="flex flex-wrap gap-2 mb-6">
                        @foreach($webhook->events ?? [] as $event)
                            <span class="px-3 py-1 text-sm bg-indigo-100 text-indigo-800 rounded-full">{{ $event }}</span>
                        @endforeach
                    </div>

                    <h4 class="text-sm font-medium text-gray-700 mb-2">Delivery Stats</h4>
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs text-gray-500">Last Triggered</dt>
                            <dd class="font-medium">{{ $webhook->last_triggered_at?->diffForHumans() ?? 'Never' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Last Status</dt>
                            <dd>
                                @if($webhook->last_status_code)
                                    <span class="px-2 py-1 text-xs font-semibold rounded {{ $webhook->last_status_code < 400 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $webhook->last_status_code }}
                                    </span>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Failure Count</dt>
                            <dd class="font-medium {{ $webhook->failure_count > 0 ? 'text-red-600' : '' }}">{{ $webhook->failure_count }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Recent Deliveries -->
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Recent Deliveries</h3>
                        <a href="{{ route('webhooks.deliveries', $webhook) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View all &rarr;</a>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Attempts</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($recentDeliveries as $delivery)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $delivery->created_at->format('m-d H:i:s') }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $delivery->event }}</td>
                                    <td class="px-4 py-2">
                                        @if($delivery->success)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ $delivery->response_code }}</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $delivery->response_code ?? 'Error' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $delivery->attempts }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-4 text-center text-gray-500">No deliveries yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Signature Verification Example -->
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Signature Verification</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Verify webhook authenticity by checking the <code class="bg-gray-100 px-1 rounded">X-Webhook-Signature</code> header.
                </p>
                <pre class="bg-gray-800 text-gray-100 p-4 rounded text-sm overflow-x-auto">
// PHP example
$timestamp = $_SERVER['HTTP_X_WEBHOOK_TIMESTAMP'];
$body = file_get_contents('php://input');
$payload = "{$timestamp}.{$body}";
$expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (hash_equals($expected, $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'])) {
    // Valid signature
}</pre>
            </div>

            <div class="mt-6">
                <a href="{{ route('webhooks.index') }}" class="text-indigo-600 hover:text-indigo-800">&larr; Back to Webhooks</a>
            </div>
        </div>
    </div>
</x-app-layout>
