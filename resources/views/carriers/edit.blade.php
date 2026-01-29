<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Carrier: {{ $carrier->name }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('carriers.update', $carrier) }}" method="POST" class="p-6 space-y-6">
                    @csrf @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                            <input type="text" name="name" id="name" required value="{{ old('name', $carrier->name) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="host" class="block text-sm font-medium text-gray-700">Host *</label>
                            <input type="text" name="host" id="host" required value="{{ old('host', $carrier->host) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="port" class="block text-sm font-medium text-gray-700">Port *</label>
                            <input type="number" name="port" id="port" required min="1" max="65535" value="{{ old('port', $carrier->port) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="transport" class="block text-sm font-medium text-gray-700">Transport *</label>
                            <select name="transport" id="transport" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="udp" {{ old('transport', $carrier->transport) === 'udp' ? 'selected' : '' }}>UDP</option>
                                <option value="tcp" {{ old('transport', $carrier->transport) === 'tcp' ? 'selected' : '' }}>TCP</option>
                                <option value="tls" {{ old('transport', $carrier->transport) === 'tls' ? 'selected' : '' }}>TLS</option>
                            </select>
                        </div>

                        <div>
                            <label for="state" class="block text-sm font-medium text-gray-700">State *</label>
                            <select name="state" id="state" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="active" {{ old('state', $carrier->state) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('state', $carrier->state) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="probing" {{ old('state', $carrier->state) === 'probing' ? 'selected' : '' }}>Probing</option>
                                <option value="disabled" {{ old('state', $carrier->state) === 'disabled' ? 'selected' : '' }}>Disabled</option>
                            </select>
                        </div>

                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority *</label>
                            <input type="number" name="priority" id="priority" required min="1" max="100" value="{{ old('priority', $carrier->priority) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700">Weight *</label>
                            <input type="number" name="weight" id="weight" required min="1" max="100" value="{{ old('weight', $carrier->weight) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="max_channels" class="block text-sm font-medium text-gray-700">Max Channels *</label>
                            <input type="number" name="max_channels" id="max_channels" required min="1" value="{{ old('max_channels', $carrier->max_channels) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="max_cps" class="block text-sm font-medium text-gray-700">Max CPS *</label>
                            <input type="number" name="max_cps" id="max_cps" required min="1" value="{{ old('max_cps', $carrier->max_cps) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="codecs" class="block text-sm font-medium text-gray-700">Codecs</label>
                            <input type="text" name="codecs" id="codecs" value="{{ old('codecs', $carrier->codecs) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="tech_prefix" class="block text-sm font-medium text-gray-700">Tech Prefix</label>
                            <input type="text" name="tech_prefix" id="tech_prefix" value="{{ old('tech_prefix', $carrier->tech_prefix) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="strip_digits" class="block text-sm font-medium text-gray-700">Strip Digits *</label>
                            <input type="number" name="strip_digits" id="strip_digits" required min="0" max="20" value="{{ old('strip_digits', $carrier->strip_digits) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label for="prefix_filter" class="block text-sm font-medium text-gray-700">Allowed Prefixes</label>
                        <textarea name="prefix_filter" id="prefix_filter" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('prefix_filter', $carrier->prefix_filter) }}</textarea>
                    </div>

                    <div>
                        <label for="prefix_deny" class="block text-sm font-medium text-gray-700">Denied Prefixes</label>
                        <textarea name="prefix_deny" id="prefix_deny" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('prefix_deny', $carrier->prefix_deny) }}</textarea>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" id="notes" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $carrier->notes) }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('carriers.show', $carrier) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">Cancel</a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">Update Carrier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
