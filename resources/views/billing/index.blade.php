<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Billing / Saldos
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('billing.transactions') }}" class="btn-secondary text-sm">Transacciones</a>
                <a href="{{ route('billing.invoices') }}" class="btn-secondary text-sm">Facturas</a>
                <a href="{{ route('billing.customers') }}" class="btn-primary text-sm">Ver Clientes</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="stat-card green">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Balance Prepago Total</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['total_prepaid_balance'], 2) }} EUR</p>
                </div>
                <div class="stat-card red">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Deuda Postpago Total</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['total_postpaid_debt'], 2) }} EUR</p>
                </div>
                <div class="stat-card blue">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Ingresos Hoy</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['today_revenue'], 2) }} EUR</p>
                </div>
                <div class="stat-card purple">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Ingresos Mes</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['monthly_revenue'], 2) }} EUR</p>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="stat-card">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Pagos Recibidos (Mes)</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">+{{ number_format($stats['monthly_payments'], 2) }} EUR</p>
                </div>
                <div class="stat-card yellow">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Clientes Saldo Bajo</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['low_balance_customers'] }}</p>
                </div>
                <div class="stat-card red">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Clientes Suspendidos</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['suspended_customers'] }}</p>
                </div>
                <div class="stat-card">
                    <p class="text-slate-500 text-xs uppercase tracking-wide font-semibold">Prepago vs Postpago</p>
                    <p class="text-lg font-bold text-slate-800 mt-1">{{ $prepaidCustomers->count() }} / {{ $postpaidCustomers->count() }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Top Prepaid Customers -->
                <div class="dark-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-800">Top Clientes Prepago (por saldo)</h3>
                        <span class="badge badge-green">Prepaid</span>
                    </div>
                    @if($prepaidCustomers->count() > 0)
                        <div class="space-y-2">
                            @foreach($prepaidCustomers as $customer)
                                <a href="{{ route('billing.customer', $customer) }}" class="flex items-center justify-between p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-{{ $customer->billing_status_color }}-100 flex items-center justify-center mr-3">
                                            <span class="text-{{ $customer->billing_status_color }}-600 font-bold text-sm">{{ substr($customer->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-800">{{ $customer->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $customer->billing_status_label }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-{{ $customer->balance >= 0 ? 'green' : 'red' }}-600">
                                            {{ number_format($customer->balance, 2) }} {{ $customer->currency }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-500 text-center py-8">No hay clientes prepago</p>
                    @endif
                </div>

                <!-- Postpaid Customers (by debt) -->
                <div class="dark-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-800">Clientes Postpago (por deuda)</h3>
                        <span class="badge badge-blue">Postpaid</span>
                    </div>
                    @if($postpaidCustomers->count() > 0)
                        <div class="space-y-2">
                            @foreach($postpaidCustomers as $customer)
                                <a href="{{ route('billing.customer', $customer) }}" class="flex items-center justify-between p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-{{ $customer->billing_status_color }}-100 flex items-center justify-center mr-3">
                                            <span class="text-{{ $customer->billing_status_color }}-600 font-bold text-sm">{{ substr($customer->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-800">{{ $customer->name }}</p>
                                            <p class="text-xs text-slate-500">Limite: {{ number_format($customer->credit_limit, 2) }} {{ $customer->currency }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-{{ $customer->balance >= 0 ? 'green' : 'red' }}-600">
                                            {{ number_format($customer->balance, 2) }} {{ $customer->currency }}
                                        </p>
                                        @if($customer->balance < 0)
                                            <p class="text-xs text-slate-500">Disponible: {{ number_format($customer->available_credit, 2) }}</p>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-500 text-center py-8">No hay clientes postpago</p>
                    @endif
                </div>
            </div>

            <!-- Suspended Customers Alert -->
            @if($suspendedCustomers->count() > 0)
                <div class="dark-card p-6 mb-6 border-l-4 border-red-500">
                    <h3 class="font-semibold text-red-600 mb-4">Clientes Suspendidos ({{ $suspendedCustomers->count() }})</h3>
                    <div class="space-y-2">
                        @foreach($suspendedCustomers as $customer)
                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-slate-800">{{ $customer->name }}</p>
                                    <p class="text-sm text-red-600">{{ $customer->suspended_reason ?? 'Sin razon' }} - {{ $customer->suspended_at->diffForHumans() }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-red-600">{{ number_format($customer->balance, 2) }} {{ $customer->currency }}</span>
                                    <a href="{{ route('billing.customer', $customer) }}" class="btn-primary text-xs">Gestionar</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Recent Transactions -->
            <div class="dark-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-800">Transacciones Recientes</h3>
                    <a href="{{ route('billing.transactions') }}" class="text-sm text-blue-600 hover:text-blue-800">Ver todas</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-200">
                                <th class="text-left py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Fecha</th>
                                <th class="text-left py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Cliente</th>
                                <th class="text-left py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Tipo</th>
                                <th class="text-left py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Descripcion</th>
                                <th class="text-right py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Importe</th>
                                <th class="text-right py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTransactions as $tx)
                                <tr class="border-b border-slate-100 hover:bg-slate-50">
                                    <td class="py-2 px-3 text-sm text-slate-600">{{ $tx->created_at->format('d/m H:i') }}</td>
                                    <td class="py-2 px-3 text-sm">
                                        <a href="{{ route('billing.customer', $tx->customer_id) }}" class="text-blue-600 hover:underline">
                                            {{ $tx->customer->name ?? 'N/A' }}
                                        </a>
                                    </td>
                                    <td class="py-2 px-3">
                                        <span class="badge badge-{{ $tx->type_color }}">{{ $tx->type_label }}</span>
                                    </td>
                                    <td class="py-2 px-3 text-sm text-slate-600 max-w-xs truncate">{{ $tx->description }}</td>
                                    <td class="py-2 px-3 text-sm text-right font-medium {{ $tx->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount, 4) }}
                                    </td>
                                    <td class="py-2 px-3 text-sm text-right text-slate-600">{{ number_format($tx->balance_after, 4) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
