<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if columns already exist (for test environment)
        if (!Schema::hasColumn('customers', 'billing_type')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->enum('billing_type', ['prepaid', 'postpaid'])->default('postpaid')->after('active');
                $table->decimal('balance', 12, 4)->default(0)->after('billing_type');
                $table->decimal('credit_limit', 12, 4)->default(0)->after('balance');
                $table->decimal('low_balance_threshold', 12, 4)->default(10)->after('credit_limit');
                $table->string('currency', 3)->default('EUR')->after('low_balance_threshold');
                $table->boolean('auto_suspend_on_zero')->default(true)->after('currency');
                $table->timestamp('suspended_at')->nullable()->after('auto_suspend_on_zero');
                $table->string('suspended_reason')->nullable()->after('suspended_at');
            });
        }

        // Create billing_transactions table
        if (!Schema::hasTable('billing_transactions')) {
            Schema::create('billing_transactions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedInteger('customer_id');
                $table->enum('type', ['credit', 'debit', 'adjustment', 'refund', 'call_charge']);
                $table->decimal('amount', 12, 4);
                $table->decimal('balance_before', 12, 4);
                $table->decimal('balance_after', 12, 4);
                $table->string('description');
                $table->string('reference')->nullable();
                $table->unsignedBigInteger('cdr_id')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->foreign('cdr_id')->references('id')->on('cdrs')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->index(['customer_id', 'created_at']);
                $table->index('type');
                $table->index('reference');
            });
        }

        // Create invoices table
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedInteger('customer_id');
                $table->string('invoice_number')->unique();
                $table->date('period_start');
                $table->date('period_end');
                $table->date('issue_date');
                $table->date('due_date');
                $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
                $table->decimal('subtotal', 12, 4)->default(0);
                $table->decimal('tax_rate', 5, 2)->default(0);
                $table->decimal('tax_amount', 12, 4)->default(0);
                $table->decimal('total', 12, 4)->default(0);
                $table->decimal('paid_amount', 12, 4)->default(0);
                $table->string('currency', 3)->default('EUR');
                $table->text('notes')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->index(['customer_id', 'status']);
                $table->index('invoice_number');
            });
        }

        // Create invoice_items table
        if (!Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
                $table->string('description');
                $table->decimal('quantity', 12, 2)->default(1);
                $table->decimal('unit_price', 12, 4);
                $table->decimal('total', 12, 4);
                $table->string('period')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('billing_transactions');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'billing_type',
                'balance',
                'credit_limit',
                'low_balance_threshold',
                'currency',
                'auto_suspend_on_zero',
                'suspended_at',
                'suspended_reason',
            ]);
        });
    }
};
