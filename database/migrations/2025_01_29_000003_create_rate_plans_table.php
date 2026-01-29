<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_plans', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('default_markup_percent', 5, 2)->default(20.00);
            $table->decimal('default_markup_fixed', 10, 6)->default(0);
            $table->unsignedTinyInteger('billing_increment')->default(1); // seconds
            $table->unsignedTinyInteger('min_duration')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Rate plan specific rates (overrides default markup)
        Schema::create('rate_plan_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rate_plan_id');
            $table->unsignedBigInteger('destination_prefix_id');
            $table->decimal('price_per_minute', 10, 6);
            $table->decimal('connection_fee', 10, 6)->default(0);
            $table->unsignedTinyInteger('billing_increment')->nullable();
            $table->unsignedTinyInteger('min_duration')->nullable();
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('rate_plan_id')->references('id')->on('rate_plans')->onDelete('cascade');
            $table->foreign('destination_prefix_id')->references('id')->on('destination_prefixes')->onDelete('cascade');
            $table->unique(['rate_plan_id', 'destination_prefix_id', 'effective_date'], 'rate_plan_rate_unique');
            $table->index(['rate_plan_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_plan_rates');
        Schema::dropIfExists('rate_plans');
    }
};
