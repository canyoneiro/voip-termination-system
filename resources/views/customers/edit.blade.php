<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Customer: {{ $customer->name }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('customers.update', $customer) }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                            <input type="text" name="name" id="name" required value="{{ old('name', $customer->name) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="company" class="block text-sm font-medium text-gray-700">Company</label>
                            <input type="text" name="company" id="company" value="{{ old('company', $customer->company) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $customer->email) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $customer->phone) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="max_channels" class="block text-sm font-medium text-gray-700">Max Channels *</label>
                            <input type="number" name="max_channels" id="max_channels" required min="1" value="{{ old('max_channels', $customer->max_channels) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="max_cps" class="block text-sm font-medium text-gray-700">Max CPS *</label>
                            <input type="number" name="max_cps" id="max_cps" required min="1" value="{{ old('max_cps', $customer->max_cps) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="max_daily_minutes" class="block text-sm font-medium text-gray-700">Max Daily Minutes</label>
                            <input type="number" name="max_daily_minutes" id="max_daily_minutes" min="0" value="{{ old('max_daily_minutes', $customer->max_daily_minutes) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="max_monthly_minutes" class="block text-sm font-medium text-gray-700">Max Monthly Minutes</label>
                            <input type="number" name="max_monthly_minutes" id="max_monthly_minutes" min="0" value="{{ old('max_monthly_minutes', $customer->max_monthly_minutes) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="alert_email" class="block text-sm font-medium text-gray-700">Alert Email</label>
                            <input type="email" name="alert_email" id="alert_email" value="{{ old('alert_email', $customer->alert_email) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="alert_telegram_chat_id" class="block text-sm font-medium text-gray-700">Telegram Chat ID</label>
                            <input type="text" name="alert_telegram_chat_id" id="alert_telegram_chat_id" value="{{ old('alert_telegram_chat_id', $customer->alert_telegram_chat_id) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" id="notes" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $customer->notes) }}</textarea>
                    </div>

                    <div class="flex items-center">
                        <input type="hidden" name="active" value="0">
                        <input type="checkbox" name="active" id="active" value="1" {{ old('active', $customer->active) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <label for="active" class="ml-2 text-sm text-gray-700">Active</label>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('customers.show', $customer) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">Cancel</a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">Update Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
