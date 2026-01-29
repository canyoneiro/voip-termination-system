<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qos_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('customer_id')->nullable();
            $table->integer('carrier_id')->nullable();
            $table->unsignedInteger('total_calls')->default(0);
            $table->unsignedInteger('measured_calls')->default(0);
            $table->decimal('avg_mos', 3, 2)->nullable();
            $table->decimal('min_mos', 3, 2)->nullable();
            $table->decimal('max_mos', 3, 2)->nullable();
            $table->unsignedInteger('avg_pdd')->nullable();
            $table->unsignedInteger('min_pdd')->nullable();
            $table->unsignedInteger('max_pdd')->nullable();
            $table->decimal('avg_jitter', 6, 2)->nullable();
            $table->decimal('avg_packet_loss', 5, 2)->nullable();
            $table->unsignedInteger('excellent_count')->default(0);
            $table->unsignedInteger('good_count')->default(0);
            $table->unsignedInteger('fair_count')->default(0);
            $table->unsignedInteger('poor_count')->default(0);
            $table->unsignedInteger('bad_count')->default(0);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('carrier_id')->references('id')->on('carriers')->onDelete('cascade');
            $table->unique(['date', 'customer_id', 'carrier_id'], 'qos_daily_stats_unique');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qos_daily_stats');
    }
};
