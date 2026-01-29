<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('type', [
                'cdr_summary',
                'customer_usage',
                'carrier_performance',
                'billing',
                'qos_report',
                'profit_loss',
                'traffic_analysis'
            ]);
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'custom']);
            $table->string('cron_expression', 100)->nullable(); // for custom frequency
            $table->time('send_time')->default('08:00:00');
            $table->unsignedTinyInteger('day_of_week')->nullable(); // 0-6 for weekly
            $table->unsignedTinyInteger('day_of_month')->nullable(); // 1-31 for monthly
            $table->json('recipients'); // array of emails
            $table->json('formats')->default('["pdf"]'); // pdf, csv, both
            $table->integer('customer_id')->nullable(); // filter by customer
            $table->integer('carrier_id')->nullable(); // filter by carrier
            $table->json('filters')->nullable(); // additional filters as JSON
            $table->boolean('include_details')->default(true);
            $table->boolean('include_charts')->default(true);
            $table->boolean('active')->default(true);
            $table->integer('created_by')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('carrier_id')->references('id')->on('carriers')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['active', 'next_run_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_reports');
    }
};
