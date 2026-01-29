<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\BillingTransaction;
use App\Models\Cdr;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingService
{
    /**
     * Add credit to customer balance
     */
    public function addCredit(
        Customer $customer,
        float $amount,
        string $description,
        ?string $reference = null,
        ?int $createdBy = null
    ): BillingTransaction {
        return DB::transaction(function () use ($customer, $amount, $description, $reference, $createdBy) {
            $customer->lockForUpdate();

            $balanceBefore = $customer->balance;
            $customer->balance += $amount;
            $customer->save();

            $transaction = BillingTransaction::create([
                'customer_id' => $customer->id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $customer->balance,
                'description' => $description,
                'reference' => $reference,
                'created_by' => $createdBy,
            ]);

            // If customer was suspended due to low balance, check if we can unsuspend
            if ($customer->isSuspended() && $customer->suspended_reason === 'low_balance') {
                if ($customer->hasCredit()) {
                    $this->unsuspendCustomer($customer, 'Balance restored');
                }
            }

            Log::info("Credit added to customer {$customer->id}: {$amount} {$customer->currency}");

            return $transaction;
        });
    }

    /**
     * Debit from customer balance
     */
    public function debit(
        Customer $customer,
        float $amount,
        string $description,
        ?string $reference = null,
        ?int $createdBy = null
    ): BillingTransaction {
        return DB::transaction(function () use ($customer, $amount, $description, $reference, $createdBy) {
            $customer->lockForUpdate();

            $balanceBefore = $customer->balance;
            $customer->balance -= $amount;
            $customer->save();

            $transaction = BillingTransaction::create([
                'customer_id' => $customer->id,
                'type' => 'debit',
                'amount' => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $customer->balance,
                'description' => $description,
                'reference' => $reference,
                'created_by' => $createdBy,
            ]);

            // Check for low balance alert
            $this->checkBalanceAlerts($customer);

            return $transaction;
        });
    }

    /**
     * Charge for a call
     */
    public function chargeCall(Cdr $cdr): ?BillingTransaction
    {
        if (!$cdr->customer_id || $cdr->price <= 0) {
            return null;
        }

        $customer = $cdr->customer;
        if (!$customer) {
            return null;
        }

        return DB::transaction(function () use ($customer, $cdr) {
            $customer->lockForUpdate();

            $balanceBefore = $customer->balance;
            $customer->balance -= $cdr->price;
            $customer->save();

            $transaction = BillingTransaction::create([
                'customer_id' => $customer->id,
                'type' => 'call_charge',
                'amount' => -$cdr->price,
                'balance_before' => $balanceBefore,
                'balance_after' => $customer->balance,
                'description' => "Llamada a {$cdr->callee} ({$cdr->billable_duration}s)",
                'cdr_id' => $cdr->id,
                'metadata' => [
                    'caller' => $cdr->caller,
                    'callee' => $cdr->callee,
                    'duration' => $cdr->duration,
                    'billable_duration' => $cdr->billable_duration,
                ],
            ]);

            // Check for low balance alert
            $this->checkBalanceAlerts($customer);

            return $transaction;
        });
    }

    /**
     * Issue a refund
     */
    public function refund(
        Customer $customer,
        float $amount,
        string $description,
        ?string $reference = null,
        ?int $createdBy = null
    ): BillingTransaction {
        return DB::transaction(function () use ($customer, $amount, $description, $reference, $createdBy) {
            $customer->lockForUpdate();

            $balanceBefore = $customer->balance;
            $customer->balance += $amount;
            $customer->save();

            return BillingTransaction::create([
                'customer_id' => $customer->id,
                'type' => 'refund',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $customer->balance,
                'description' => $description,
                'reference' => $reference,
                'created_by' => $createdBy,
            ]);
        });
    }

    /**
     * Make an adjustment (positive or negative)
     */
    public function adjustment(
        Customer $customer,
        float $amount,
        string $description,
        ?int $createdBy = null
    ): BillingTransaction {
        return DB::transaction(function () use ($customer, $amount, $description, $createdBy) {
            $customer->lockForUpdate();

            $balanceBefore = $customer->balance;
            $customer->balance += $amount;
            $customer->save();

            $transaction = BillingTransaction::create([
                'customer_id' => $customer->id,
                'type' => 'adjustment',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $customer->balance,
                'description' => $description,
                'created_by' => $createdBy,
            ]);

            $this->checkBalanceAlerts($customer);

            return $transaction;
        });
    }

    /**
     * Check if customer can make a call based on billing
     */
    public function canMakeCall(Customer $customer, float $estimatedCost = 0): array
    {
        if ($customer->isSuspended()) {
            return [
                'allowed' => false,
                'reason' => 'suspended',
                'message' => 'Customer is suspended: ' . ($customer->suspended_reason ?? 'Unknown'),
            ];
        }

        if ($customer->isPrepaid()) {
            if ($customer->balance < $estimatedCost) {
                return [
                    'allowed' => false,
                    'reason' => 'insufficient_balance',
                    'message' => 'Insufficient prepaid balance',
                    'balance' => $customer->balance,
                    'required' => $estimatedCost,
                ];
            }
        } else {
            // Postpaid
            if ($customer->available_credit < $estimatedCost) {
                return [
                    'allowed' => false,
                    'reason' => 'credit_limit_exceeded',
                    'message' => 'Credit limit exceeded',
                    'available_credit' => $customer->available_credit,
                    'required' => $estimatedCost,
                ];
            }
        }

        return [
            'allowed' => true,
            'reason' => 'ok',
            'message' => 'Call allowed',
        ];
    }

    /**
     * Check balance and send alerts if needed
     */
    protected function checkBalanceAlerts(Customer $customer): void
    {
        // Low balance alert
        if ($customer->isLowBalance() && $customer->notify_low_balance) {
            $existingAlert = Alert::where('source_type', 'customer')
                ->where('source_id', $customer->id)
                ->where('type', 'minutes_warning')
                ->where('acknowledged', false)
                ->where('created_at', '>=', now()->subHours(4))
                ->exists();

            if (!$existingAlert) {
                Alert::create([
                    'type' => 'minutes_warning',
                    'severity' => 'warning',
                    'source_type' => 'customer',
                    'source_id' => $customer->id,
                    'source_name' => $customer->name,
                    'title' => 'Saldo bajo',
                    'message' => "El cliente {$customer->name} tiene saldo bajo: {$customer->balance} {$customer->currency}",
                    'metadata' => [
                        'balance' => $customer->balance,
                        'threshold' => $customer->low_balance_threshold,
                        'billing_type' => $customer->billing_type,
                    ],
                ]);
            }
        }

        // Auto-suspend for prepaid with zero balance
        if ($customer->isPrepaid() &&
            $customer->auto_suspend_on_zero &&
            $customer->balance <= 0 &&
            !$customer->isSuspended()
        ) {
            $this->suspendCustomer($customer, 'low_balance', 'Balance exhausted');
        }
    }

    /**
     * Suspend a customer
     */
    public function suspendCustomer(Customer $customer, string $reason, string $details = ''): void
    {
        $customer->update([
            'suspended_at' => now(),
            'suspended_reason' => $reason,
        ]);

        Alert::create([
            'type' => 'minutes_exhausted',
            'severity' => 'critical',
            'source_type' => 'customer',
            'source_id' => $customer->id,
            'source_name' => $customer->name,
            'title' => 'Cliente suspendido',
            'message' => "El cliente {$customer->name} ha sido suspendido: {$details}",
            'metadata' => [
                'reason' => $reason,
                'details' => $details,
            ],
        ]);

        Log::warning("Customer {$customer->id} suspended: {$reason}");
    }

    /**
     * Unsuspend a customer
     */
    public function unsuspendCustomer(Customer $customer, string $reason = ''): void
    {
        $customer->update([
            'suspended_at' => null,
            'suspended_reason' => null,
        ]);

        Log::info("Customer {$customer->id} unsuspended: {$reason}");
    }

    /**
     * Generate invoice for a period
     */
    public function generateInvoice(
        Customer $customer,
        \DateTime $periodStart,
        \DateTime $periodEnd,
        float $taxRate = 21.0
    ): Invoice {
        return DB::transaction(function () use ($customer, $periodStart, $periodEnd, $taxRate) {
            // Get CDRs for the period
            $cdrs = Cdr::where('customer_id', $customer->id)
                ->whereBetween('start_time', [$periodStart, $periodEnd])
                ->where('sip_code', 200)
                ->get();

            $totalMinutes = $cdrs->sum('billable_duration') / 60;
            $totalCost = $cdrs->sum('price');
            $totalCalls = $cdrs->count();

            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'issue_date' => now(),
                'due_date' => now()->addDays(30),
                'status' => 'draft',
                'tax_rate' => $taxRate,
                'currency' => $customer->currency,
            ]);

            // Add call charges as invoice item
            if ($totalCalls > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => "Llamadas VoIP ({$totalCalls} llamadas, " . number_format($totalMinutes, 2) . " minutos)",
                    'quantity' => $totalMinutes,
                    'unit_price' => $totalMinutes > 0 ? $totalCost / $totalMinutes : 0,
                    'total' => $totalCost,
                    'period' => $periodStart->format('Y-m'),
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Get billing summary for a customer
     */
    public function getCustomerSummary(Customer $customer): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $todayCharges = BillingTransaction::where('customer_id', $customer->id)
            ->where('type', 'call_charge')
            ->where('created_at', '>=', $today)
            ->sum('amount');

        $monthlyCharges = BillingTransaction::where('customer_id', $customer->id)
            ->where('type', 'call_charge')
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount');

        $monthlyCredits = BillingTransaction::where('customer_id', $customer->id)
            ->whereIn('type', ['credit', 'refund'])
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount');

        $lastPayment = BillingTransaction::where('customer_id', $customer->id)
            ->where('type', 'credit')
            ->orderByDesc('created_at')
            ->first();

        $pendingInvoices = Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['sent', 'overdue'])
            ->get();

        return [
            'balance' => $customer->balance,
            'available_credit' => $customer->available_credit,
            'billing_type' => $customer->billing_type,
            'currency' => $customer->currency,
            'is_suspended' => $customer->isSuspended(),
            'is_low_balance' => $customer->isLowBalance(),
            'today_charges' => abs($todayCharges),
            'monthly_charges' => abs($monthlyCharges),
            'monthly_credits' => $monthlyCredits,
            'last_payment' => $lastPayment ? [
                'amount' => $lastPayment->amount,
                'date' => $lastPayment->created_at,
                'reference' => $lastPayment->reference,
            ] : null,
            'pending_invoices_count' => $pendingInvoices->count(),
            'pending_invoices_total' => $pendingInvoices->sum('balance_due'),
        ];
    }

    /**
     * Get global billing statistics
     */
    public function getGlobalStats(): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $totalPrepaidBalance = Customer::where('billing_type', 'prepaid')
            ->where('active', true)
            ->sum('balance');

        $totalPostpaidDebt = Customer::where('billing_type', 'postpaid')
            ->where('active', true)
            ->where('balance', '<', 0)
            ->sum('balance');

        $todayRevenue = BillingTransaction::where('type', 'call_charge')
            ->where('created_at', '>=', $today)
            ->sum('amount');

        $monthlyRevenue = BillingTransaction::where('type', 'call_charge')
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount');

        $monthlyPayments = BillingTransaction::whereIn('type', ['credit'])
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount');

        $suspendedCustomers = Customer::whereNotNull('suspended_at')
            ->where('active', true)
            ->count();

        $lowBalanceCustomers = Customer::where('billing_type', 'prepaid')
            ->where('active', true)
            ->whereRaw('balance <= low_balance_threshold')
            ->count();

        return [
            'total_prepaid_balance' => $totalPrepaidBalance,
            'total_postpaid_debt' => abs($totalPostpaidDebt),
            'today_revenue' => abs($todayRevenue),
            'monthly_revenue' => abs($monthlyRevenue),
            'monthly_payments' => $monthlyPayments,
            'suspended_customers' => $suspendedCustomers,
            'low_balance_customers' => $lowBalanceCustomers,
        ];
    }
}
