<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fraud_rules', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
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
            $table->json('parameters'); // rule-specific parameters
            $table->decimal('threshold', 10, 2)->nullable();
            $table->enum('action', ['log', 'alert', 'throttle', 'block'])->default('alert');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->integer('customer_id')->nullable(); // null = global
            $table->boolean('active')->default(true);
            $table->unsignedInteger('cooldown_minutes')->default(60); // time before rule can trigger again
            $table->timestamp('last_triggered_at')->nullable();
            $table->unsignedInteger('trigger_count')->default(0);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->index(['type', 'active']);
            $table->index(['customer_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_rules');
    }
};
