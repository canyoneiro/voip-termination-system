<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">Facturas</h2>
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
                        <label class="block text-xs text-slate-500 mb-1">Estado</label>
                        <select name="status" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                            <option value="">Todos</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Borrador</option>
                            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Enviada</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Pagada</option>
                            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Vencida</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">Filtrar</button>
                </form>
            </div>

            <!-- Invoices Table -->
            <div class="dark-card overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Numero</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Cliente</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Periodo</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Estado</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Total</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Pendiente</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Vencimiento</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="py-3 px-4 text-sm font-medium text-slate-800">{{ $invoice->invoice_number }}</td>
                                <td class="py-3 px-4 text-sm text-slate-600">{{ $invoice->customer->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm text-slate-600">
                                    {{ $invoice->period_start->format('d/m/Y') }} - {{ $invoice->period_end->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="badge badge-{{ $invoice->status_color }}">{{ $invoice->status_label }}</span>
                                </td>
                                <td class="py-3 px-4 text-sm text-right font-medium">
                                    {{ number_format($invoice->total, 2) }} {{ $invoice->currency }}
                                </td>
                                <td class="py-3 px-4 text-sm text-right {{ $invoice->balance_due > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($invoice->balance_due, 2) }}
                                </td>
                                <td class="py-3 px-4 text-sm {{ $invoice->is_overdue ? 'text-red-600' : 'text-slate-600' }}">
                                    {{ $invoice->due_date->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('billing.invoice', $invoice) }}" class="btn-secondary text-xs">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-500">No hay facturas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $invoices->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
