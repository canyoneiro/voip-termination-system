<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BillingTransaction;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\BillingService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    ) {}

    public function index()
    {
        $stats = $this->billingService->getGlobalStats();

        $prepaidCustomers = Customer::where('billing_type', 'prepaid')
            ->where('active', true)
            ->orderByDesc('balance')
            ->limit(10)
            ->get();

        $postpaidCustomers = Customer::where('billing_type', 'postpaid')
            ->where('active', true)
            ->orderBy('balance')
            ->limit(10)
            ->get();

        $recentTransactions = BillingTransaction::with('customer')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $suspendedCustomers = Customer::whereNotNull('suspended_at')
            ->where('active', true)
            ->get();

        return view('billing.index', compact(
            'stats',
            'prepaidCustomers',
            'postpaidCustomers',
            'recentTransactions',
            'suspendedCustomers'
        ));
    }

    public function customers(Request $request)
    {
        $query = Customer::where('active', true);

        if ($request->filled('billing_type')) {
            $query->where('billing_type', $request->input('billing_type'));
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'suspended') {
                $query->whereNotNull('suspended_at');
            } elseif ($request->input('status') === 'low_balance') {
                $query->where('billing_type', 'prepaid')
                    ->whereRaw('balance <= low_balance_threshold');
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name')->paginate(50);

        return view('billing.customers', compact('customers'));
    }

    public function customerDetail(Customer $customer)
    {
        $summary = $this->billingService->getCustomerSummary($customer);

        $transactions = BillingTransaction::where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->paginate(50);

        $invoices = Invoice::where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('billing.customer-detail', compact('customer', 'summary', 'transactions', 'invoices'));
    }

    public function addCredit(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:99999999',
            'description' => 'required|string|max:255',
            'reference' => 'nullable|string|max:100',
        ]);

        $this->billingService->addCredit(
            $customer,
            $data['amount'],
            $data['description'],
            $data['reference'],
            auth()->id()
        );

        return redirect()->route('billing.customer', $customer)
            ->with('success', "Recarga de {$data['amount']} {$customer->currency} aplicada correctamente.");
    }

    public function adjustment(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:-99999999|max:99999999',
            'description' => 'required|string|max:255',
        ]);

        $this->billingService->adjustment(
            $customer,
            $data['amount'],
            $data['description'],
            auth()->id()
        );

        $type = $data['amount'] >= 0 ? 'aumento' : 'descuento';

        return redirect()->route('billing.customer', $customer)
            ->with('success', "Ajuste ({$type}) de {$data['amount']} {$customer->currency} aplicado.");
    }

    public function suspend(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $this->billingService->suspendCustomer($customer, 'manual', $data['reason']);

        return redirect()->route('billing.customer', $customer)
            ->with('success', 'Cliente suspendido correctamente.');
    }

    public function unsuspend(Customer $customer)
    {
        $this->billingService->unsuspendCustomer($customer, 'Manual unsuspend by ' . auth()->user()->name);

        return redirect()->route('billing.customer', $customer)
            ->with('success', 'Suspension del cliente eliminada.');
    }

    public function updateBillingType(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'billing_type' => 'required|in:prepaid,postpaid',
            'credit_limit' => 'required_if:billing_type,postpaid|numeric|min:0',
            'low_balance_threshold' => 'required|numeric|min:0',
            'auto_suspend_on_zero' => 'boolean',
        ]);

        $customer->update([
            'billing_type' => $data['billing_type'],
            'credit_limit' => $data['credit_limit'] ?? 0,
            'low_balance_threshold' => $data['low_balance_threshold'],
            'auto_suspend_on_zero' => $request->boolean('auto_suspend_on_zero'),
        ]);

        return redirect()->route('billing.customer', $customer)
            ->with('success', 'Configuracion de facturacion actualizada.');
    }

    public function transactions(Request $request)
    {
        $query = BillingTransaction::with('customer', 'createdBy');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->input('to') . ' 23:59:59');
        }

        $transactions = $query->orderByDesc('created_at')->paginate(100);
        $customers = Customer::orderBy('name')->get();

        return view('billing.transactions', compact('transactions', 'customers'));
    }

    public function invoices(Request $request)
    {
        $query = Invoice::with('customer');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $invoices = $query->orderByDesc('created_at')->paginate(50);
        $customers = Customer::orderBy('name')->get();

        return view('billing.invoices', compact('invoices', 'customers'));
    }

    public function generateInvoice(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        $invoice = $this->billingService->generateInvoice(
            $customer,
            new \DateTime($data['period_start']),
            new \DateTime($data['period_end']),
            $data['tax_rate']
        );

        return redirect()->route('billing.invoice', $invoice)
            ->with('success', 'Factura generada correctamente.');
    }

    public function showInvoice(Invoice $invoice)
    {
        $invoice->load('customer', 'items');

        return view('billing.invoice-detail', compact('invoice'));
    }

    public function updateInvoiceStatus(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
        ]);

        $invoice->update([
            'status' => $data['status'],
            'sent_at' => $data['status'] === 'sent' && !$invoice->sent_at ? now() : $invoice->sent_at,
            'paid_at' => $data['status'] === 'paid' && !$invoice->paid_at ? now() : $invoice->paid_at,
        ]);

        // If marking as paid, add credit to customer (for postpaid)
        if ($data['status'] === 'paid' && $invoice->customer->isPostpaid()) {
            $invoice->update(['paid_amount' => $invoice->total]);
        }

        return redirect()->route('billing.invoice', $invoice)
            ->with('success', 'Estado de factura actualizado.');
    }
}
