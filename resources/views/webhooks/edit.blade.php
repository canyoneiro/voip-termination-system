<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Webhook</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('webhooks.update', $webhook) }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="url" class="block text-sm font-medium text-gray-700">Webhook URL *</label>
                        <input type="url" name="url" id="url" required value="{{ old('url', $webhook->url) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700">Customer (optional)</label>
                        <select name="customer_id" id="customer_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Global (all events)</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $webhook->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Events to subscribe *</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($availableEvents as $event)
                                <label class="flex items-center p-2 border rounded hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox" name="events[]" value="{{ $event }}"
                                        {{ in_array($event, old('events', $webhook->events ?? [])) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">{{ $event }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('events')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center">
                        <input type="hidden" name="active" value="0">
                        <input type="checkbox" name="active" id="active" value="1" {{ old('active', $webhook->active) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <label for="active" class="ml-2 text-sm text-gray-700">Active</label>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('webhooks.show', $webhook) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">Cancel</a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">Update Webhook</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
