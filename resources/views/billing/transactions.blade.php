<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">Transacciones</h2>
            <a href="{{ route('billing.index') }}" class="btn-secondary text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="dark-card p-4 mb-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Cliente</label>
                        <select name="customer_id" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                            <option value="">Todos</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Tipo</label>
                        <select name="type" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                            <option value="">Todos</option>
                            <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Recarga</option>
                            <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Cargo</option>
                            <option value="call_charge" {{ request('type') === 'call_charge' ? 'selected' : '' }}>Llamada</option>
                            <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Ajuste</option>
                            <option value="refund" {{ request('type') === 'refund' ? 'selected' : '' }}>Reembolso</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Desde</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Hasta</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                    </div>
                    <button type="submit" class="btn-primary">Filtrar</button>
                    <a href="{{ route('billing.transactions') }}" class="btn-secondary">Limpiar</a>
                </form>
            </div>

            <!-- Transactions Table -->
            <div class="dark-card overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Fecha</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Cliente</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Tipo</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Descripcion</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Referencia</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Importe</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Saldo</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="py-3 px-4 text-sm text-slate-600">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('billing.customer', $tx->customer_id) }}" class="text-blue-600 hover:underline text-sm">
                                        {{ $tx->customer->name ?? 'N/A' }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge badge-{{ $tx->type_color }}">{{ $tx->type_label }}</span>
                                </td>
                                <td class="py-3 px-4 text-sm text-slate-600 max-w-xs truncate">{{ $tx->description }}</td>
                                <td class="py-3 px-4 text-sm text-slate-500">{{ $tx->reference ?? '-' }}</td>
                                <td class="py-3 px-4 text-sm text-right font-medium {{ $tx->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount, 4) }}
                                </td>
                                <td class="py-3 px-4 text-sm text-right text-slate-600">{{ number_format($tx->balance_after, 4) }}</td>
                                <td class="py-3 px-4 text-sm text-slate-500">{{ $tx->createdBy->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-500">No hay transacciones</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $transactions->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
