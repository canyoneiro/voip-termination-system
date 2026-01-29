<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Alert Details</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 text-sm font-semibold rounded-full
                            {{ $alert->severity === 'critical' ? 'bg-red-100 text-red-800' :
                               ($alert->severity === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ ucfirst($alert->severity) }}
                        </span>
                        <span class="text-sm text-gray-500">{{ str_replace('_', ' ', $alert->type) }}</span>
                    </div>
                    @if(!$alert->acknowledged)
                        <form action="{{ route('alerts.acknowledge', $alert) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Acknowledge
                            </button>
                        </form>
                    @else
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Acknowledged</span>
                    @endif
                </div>

                <h3 class="text-xl font-medium text-gray-900 mb-4">{{ $alert->title }}</h3>

                <div class="prose max-w-none mb-6">
                    <p class="text-gray-700">{{ $alert->message }}</p>
                </div>

                <dl class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <dt class="text-sm text-gray-500">Created</dt>
                        <dd class="font-medium">{{ $alert->created_at->format('Y-m-d H:i:s') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Source</dt>
                        <dd class="font-medium">{{ $alert->source_type }}: {{ $alert->source_name ?? $alert->source_id ?? '-' }}</dd>
                    </div>
                    @if($alert->acknowledged)
                    <div>
                        <dt class="text-sm text-gray-500">Acknowledged At</dt>
                        <dd class="font-medium">{{ $alert->acknowledged_at }}</dd>
                    </div>
                    @endif
                </dl>

                @if($alert->metadata)
                <div class="border-t pt-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Metadata</h4>
                    <pre class="bg-gray-100 p-4 rounded text-xs overflow-x-auto">{{ json_encode($alert->metadata, JSON_PRETTY_PRINT) }}</pre>
                </div>
                @endif
            </div>

            <div class="mt-6">
                <a href="{{ route('alerts.index') }}" class="text-indigo-600 hover:text-indigo-800">&larr; Back to Alerts</a>
            </div>
        </div>
    </div>
</x-app-layout>
