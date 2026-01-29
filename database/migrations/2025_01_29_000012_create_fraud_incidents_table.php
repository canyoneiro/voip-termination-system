<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fraud_incidents', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->unsignedBigInteger('fraud_rule_id')->nullable();
            $table->integer('customer_id');
            $table->bigInteger('cdr_id')->nullable();
            $table->enum('type', [
                'high_cost_destination',
                'traffic_spike',
                'wangiri',
                'unusual_destination',
                'high_failure_rate',
                'off_hours_traffic',
                'caller_id_manipulation',
                'accelerated_consumption',
                'simultaneous_calls',
                'short_calls_burst'
            ]);
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('title', 255);
            $table->text('description');
            $table->json('metadata')->nullable(); // details: numbers, prefixes, amounts, etc.
            $table->decimal('estimated_cost', 10, 6)->nullable();
            $table->unsignedInteger('affected_calls')->nullable();
            $table->enum('status', ['pending', 'investigating', 'false_positive', 'confirmed', 'resolved'])->default('pending');
            $table->enum('action_taken', ['none', 'notified', 'throttled', 'blocked'])->default('none');
            $table->integer('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->boolean('notified_admin')->default(false);
            $table->boolean('notified_customer')->default(false);
            $table->timestamps();

            $table->foreign('fraud_rule_id')->references('id')->on('fraud_rules')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('cdr_id')->references('id')->on('cdrs')->onDelete('set null');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['customer_id', 'status']);
            $table->index(['type', 'status']);
            $table->index(['severity', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_incidents');
    }
};
