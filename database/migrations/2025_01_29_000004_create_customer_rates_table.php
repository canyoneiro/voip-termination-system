<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Customer specific rates (direct pricing)
        Schema::create('customer_rates', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->unsignedBigInteger('destination_prefix_id');
            $table->decimal('price_per_minute', 10, 6);
            $table->decimal('connection_fee', 10, 6)->default(0);
            $table->unsignedTinyInteger('billing_increment')->default(1);
            $table->unsignedTinyInteger('min_duration')->default(0);
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('destination_prefix_id')->references('id')->on('destination_prefixes')->onDelete('cascade');
            $table->unique(['customer_id', 'destination_prefix_id', 'effective_date'], 'customer_rate_unique');
            $table->index(['customer_id', 'active']);
        });

        // Add rate_plan_id to customers table (if not exists)
        if (!Schema::hasColumn('customers', 'rate_plan_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->unsignedBigInteger('rate_plan_id')->nullable();
                $table->foreign('rate_plan_id')->references('id')->on('rate_plans')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customers', 'rate_plan_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropForeign(['rate_plan_id']);
                $table->dropColumn('rate_plan_id');
            });
        }
        Schema::dropIfExists('customer_rates');
    }
};
