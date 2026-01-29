<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Carrier</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('carriers.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                            <input type="text" name="name" id="name" required value="{{ old('name') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="host" class="block text-sm font-medium text-gray-700">Host *</label>
                            <input type="text" name="host" id="host" required value="{{ old('host') }}" placeholder="IP or domain"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="port" class="block text-sm font-medium text-gray-700">Port *</label>
                            <input type="number" name="port" id="port" required min="1" max="65535" value="{{ old('port', 5060) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="transport" class="block text-sm font-medium text-gray-700">Transport *</label>
                            <select name="transport" id="transport" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="udp" {{ old('transport') === 'udp' ? 'selected' : '' }}>UDP</option>
                                <option value="tcp" {{ old('transport') === 'tcp' ? 'selected' : '' }}>TCP</option>
                                <option value="tls" {{ old('transport') === 'tls' ? 'selected' : '' }}>TLS</option>
                            </select>
                        </div>

                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority * (lower = higher priority)</label>
                            <input type="number" name="priority" id="priority" required min="1" max="100" value="{{ old('priority', 1) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700">Weight *</label>
                            <input type="number" name="weight" id="weight" required min="1" max="100" value="{{ old('weight', 100) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="max_channels" class="block text-sm font-medium text-gray-700">Max Channels *</label>
                            <input type="number" name="max_channels" id="max_channels" required min="1" value="{{ old('max_channels', 50) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="max_cps" class="block text-sm font-medium text-gray-700">Max CPS *</label>
                            <input type="number" name="max_cps" id="max_cps" required min="1" value="{{ old('max_cps', 10) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="codecs" class="block text-sm font-medium text-gray-700">Codecs (comma-separated)</label>
                            <input type="text" name="codecs" id="codecs" value="{{ old('codecs', 'G729,PCMA,PCMU') }}" placeholder="G729,PCMA,PCMU"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="tech_prefix" class="block text-sm font-medium text-gray-700">Tech Prefix</label>
                            <input type="text" name="tech_prefix" id="tech_prefix" value="{{ old('tech_prefix') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="strip_digits" class="block text-sm font-medium text-gray-700">Strip Digits *</label>
                            <input type="number" name="strip_digits" id="strip_digits" required min="0" max="20" value="{{ old('strip_digits', 0) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label for="prefix_filter" class="block text-sm font-medium text-gray-700">Allowed Prefixes (one per line)</label>
                        <textarea name="prefix_filter" id="prefix_filter" rows="3" placeholder="34*&#10;351*"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('prefix_filter') }}</textarea>
                    </div>

                    <div>
                        <label for="prefix_deny" class="block text-sm font-medium text-gray-700">Denied Prefixes (one per line)</label>
                        <textarea name="prefix_deny" id="prefix_deny" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('prefix_deny') }}</textarea>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" id="notes" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('carriers.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">Cancel</a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">Create Carrier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
