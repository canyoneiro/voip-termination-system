<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">Clientes - Billing</h2>
            <a href="{{ route('billing.index') }}" class="btn-secondary text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="dark-card p-4 mb-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Tipo</label>
                        <select name="billing_type" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                            <option value="">Todos</option>
                            <option value="prepaid" {{ request('billing_type') === 'prepaid' ? 'selected' : '' }}>Prepago</option>
                            <option value="postpaid" {{ request('billing_type') === 'postpaid' ? 'selected' : '' }}>Postpago</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Estado</label>
                        <select name="status" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                            <option value="">Todos</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendidos</option>
                            <option value="low_balance" {{ request('status') === 'low_balance' ? 'selected' : '' }}>Saldo bajo</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs text-slate-500 mb-1">Buscar</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre o empresa..."
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                    </div>
                    <button type="submit" class="btn-primary">Filtrar</button>
                </form>
            </div>

            <!-- Customers Table -->
            <div class="dark-card overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Cliente</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Tipo</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Saldo</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Limite</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Estado</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="py-3 px-4">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-{{ $customer->billing_status_color }}-100 flex items-center justify-center mr-3">
                                            <span class="text-{{ $customer->billing_status_color }}-600 font-bold text-sm">{{ substr($customer->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-800">{{ $customer->name }}</p>
                                            @if($customer->company)
                                                <p class="text-xs text-slate-500">{{ $customer->company }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge badge-{{ $customer->isPrepaid() ? 'green' : 'blue' }}">
                                        {{ $customer->isPrepaid() ? 'Prepago' : 'Postpago' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-medium {{ $customer->balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($customer->balance, 2) }} {{ $customer->currency }}
                                </td>
                                <td class="py-3 px-4 text-right text-slate-600">
                                    @if($customer->isPostpaid())
                                        {{ number_format($customer->credit_limit, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="badge badge-{{ $customer->billing_status_color }}">
                                        {{ $customer->billing_status_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('billing.customer', $customer) }}" class="btn-primary text-xs">Gestionar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">No hay clientes</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $customers->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
