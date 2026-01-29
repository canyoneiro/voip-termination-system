<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('billing.index') }}" class="mr-4 text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-slate-800 leading-tight">{{ $customer->name }}</h2>
                    <p class="text-sm text-slate-500">Billing - {{ $customer->isPrepaid() ? 'Prepago' : 'Postpago' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="badge badge-{{ $customer->billing_status_color }} text-sm px-3 py-1">
                    {{ $customer->billing_status_label }}
                </span>
                <a href="{{ route('customers.show', $customer) }}" class="btn-secondary text-sm">Ver Cliente</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Balance Card -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="stat-card {{ $customer->balance >= 0 ? 'green' : 'red' }} md:col-span-2">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Saldo Actual</p>
                    <p class="text-3xl font-bold {{ $customer->balance >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                        {{ number_format($customer->balance, 4) }} {{ $customer->currency }}
                    </p>
                    @if($customer->isPostpaid())
                        <p class="text-sm text-slate-500 mt-1">
                            Limite de credito: {{ number_format($customer->credit_limit, 2) }} {{ $customer->currency }}
                            | Disponible: {{ number_format($summary['available_credit'], 2) }} {{ $customer->currency }}
                        </p>
                    @endif
                </div>
                <div class="stat-card">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Cargos Hoy</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">-{{ number_format($summary['today_charges'], 4) }}</p>
                </div>
                <div class="stat-card">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Cargos Este Mes</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">-{{ number_format($summary['monthly_charges'], 4) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Quick Actions -->
                <div class="dark-card p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Acciones Rapidas</h3>

                    <!-- Add Credit Form -->
                    <form method="POST" action="{{ route('billing.add-credit', $customer) }}" class="mb-4 p-4 bg-green-50 rounded-lg">
                        @csrf
                        <h4 class="font-medium text-green-800 mb-3">Agregar Saldo</h4>
                        <div class="space-y-3">
                            <div>
                                <input type="number" name="amount" step="0.01" min="0.01" required
                                    class="w-full px-3 py-2 border border-green-200 rounded-lg text-sm"
                                    placeholder="Importe">
                            </div>
                            <div>
                                <input type="text" name="description" required
                                    class="w-full px-3 py-2 border border-green-200 rounded-lg text-sm"
                                    placeholder="Descripcion (ej: Pago PayPal)">
                            </div>
                            <div>
                                <input type="text" name="reference"
                                    class="w-full px-3 py-2 border border-green-200 rounded-lg text-sm"
                                    placeholder="Referencia (opcional)">
                            </div>
                            <button type="submit" class="w-full btn-primary bg-green-600 hover:bg-green-700">
                                Agregar Saldo
                            </button>
                        </div>
                    </form>

                    <!-- Adjustment Form -->
                    <form method="POST" action="{{ route('billing.adjustment', $customer) }}" class="mb-4 p-4 bg-purple-50 rounded-lg">
                        @csrf
                        <h4 class="font-medium text-purple-800 mb-3">Ajuste Manual</h4>
                        <div class="space-y-3">
                            <div>
                                <input type="number" name="amount" step="0.01" required
                                    class="w-full px-3 py-2 border border-purple-200 rounded-lg text-sm"
                                    placeholder="Importe (+ o -)">
                            </div>
                            <div>
                                <input type="text" name="description" required
                                    class="w-full px-3 py-2 border border-purple-200 rounded-lg text-sm"
                                    placeholder="Razon del ajuste">
                            </div>
                            <button type="submit" class="w-full btn-secondary">
                                Aplicar Ajuste
                            </button>
                        </div>
                    </form>

                    <!-- Suspend/Unsuspend -->
                    @if($customer->isSuspended())
                        <form method="POST" action="{{ route('billing.unsuspend', $customer) }}" class="p-4 bg-green-50 rounded-lg">
                            @csrf
                            <h4 class="font-medium text-green-800 mb-2">Cliente Suspendido</h4>
                            <p class="text-sm text-green-700 mb-3">{{ $customer->suspended_reason }}</p>
                            <button type="submit" class="w-full btn-primary bg-green-600 hover:bg-green-700">
                                Reactivar Cliente
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('billing.suspend', $customer) }}" class="p-4 bg-red-50 rounded-lg" onsubmit="return confirm('Estas seguro de suspender este cliente?')">
                            @csrf
                            <h4 class="font-medium text-red-800 mb-3">Suspender Cliente</h4>
                            <div class="space-y-3">
                                <input type="text" name="reason" required
                                    class="w-full px-3 py-2 border border-red-200 rounded-lg text-sm"
                                    placeholder="Razon de suspension">
                                <button type="submit" class="w-full btn-secondary text-red-600 border-red-300 hover:bg-red-100">
                                    Suspender
                                </button>
                            </div>
                        </form>
                    @endif
                </div>

                <!-- Billing Configuration -->
                <div class="dark-card p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Configuracion de Facturacion</h3>
                    <form method="POST" action="{{ route('billing.update-billing-type', $customer) }}">
                        @csrf
                        @method('PUT')
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de Facturacion</label>
                                <select name="billing_type" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                                    <option value="prepaid" {{ $customer->billing_type === 'prepaid' ? 'selected' : '' }}>Prepago</option>
                                    <option value="postpaid" {{ $customer->billing_type === 'postpaid' ? 'selected' : '' }}>Postpago</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Limite de Credito (Postpago)</label>
                                <input type="number" name="credit_limit" step="0.01" min="0"
                                    value="{{ $customer->credit_limit }}"
                                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Umbral Saldo Bajo</label>
                                <input type="number" name="low_balance_threshold" step="0.01" min="0"
                                    value="{{ $customer->low_balance_threshold }}"
                                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="auto_suspend_on_zero" value="1" id="auto_suspend"
                                    {{ $customer->auto_suspend_on_zero ? 'checked' : '' }}
                                    class="mr-2">
                                <label for="auto_suspend" class="text-sm text-slate-700">Suspender automaticamente al agotar saldo</label>
                            </div>
                            <button type="submit" class="w-full btn-primary">
                                Guardar Configuracion
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Summary Info -->
                <div class="dark-card p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Resumen</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Tipo:</span>
                            <span class="font-medium">{{ $customer->isPrepaid() ? 'Prepago' : 'Postpago' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Moneda:</span>
                            <span class="font-medium">{{ $customer->currency }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Creditos este mes:</span>
                            <span class="font-medium text-green-600">+{{ number_format($summary['monthly_credits'], 2) }}</span>
                        </div>
                        @if($summary['last_payment'])
                            <div class="pt-3 border-t border-slate-200">
                                <p class="text-xs text-slate-500 mb-1">Ultimo pago:</p>
                                <p class="font-medium">{{ number_format($summary['last_payment']['amount'], 2) }} {{ $customer->currency }}</p>
                                <p class="text-xs text-slate-500">{{ $summary['last_payment']['date']->format('d/m/Y H:i') }}</p>
                            </div>
                        @endif
                        @if($summary['pending_invoices_count'] > 0)
                            <div class="pt-3 border-t border-slate-200">
                                <p class="text-sm text-red-600">
                                    {{ $summary['pending_invoices_count'] }} factura(s) pendiente(s):
                                    <strong>{{ number_format($summary['pending_invoices_total'], 2) }} {{ $customer->currency }}</strong>
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Transactions History -->
            <div class="dark-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-800">Historial de Transacciones</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-200">
                                <th class="text-left py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Fecha</th>
                                <th class="text-left py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Tipo</th>
                                <th class="text-left py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Descripcion</th>
                                <th class="text-left py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Referencia</th>
                                <th class="text-right py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Importe</th>
                                <th class="text-right py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $tx)
                                <tr class="border-b border-slate-100 hover:bg-slate-50">
                                    <td class="py-2 px-3 text-sm text-slate-600">{{ $tx->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td class="py-2 px-3">
                                        <span class="badge badge-{{ $tx->type_color }}">{{ $tx->type_label }}</span>
                                    </td>
                                    <td class="py-2 px-3 text-sm text-slate-600">{{ $tx->description }}</td>
                                    <td class="py-2 px-3 text-sm text-slate-500">{{ $tx->reference ?? '-' }}</td>
                                    <td class="py-2 px-3 text-sm text-right font-medium {{ $tx->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount, 4) }}
                                    </td>
                                    <td class="py-2 px-3 text-sm text-right text-slate-600">{{ number_format($tx->balance_after, 4) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-500">No hay transacciones</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
