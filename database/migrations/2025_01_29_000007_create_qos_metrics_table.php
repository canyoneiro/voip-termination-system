<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qos_metrics', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('cdr_id');
            $table->integer('customer_id');
            $table->integer('carrier_id')->nullable();
            $table->decimal('mos_score', 3, 2)->nullable(); // 1.00 to 5.00
            $table->unsignedInteger('pdd')->nullable(); // ms
            $table->unsignedInteger('jitter')->nullable(); // ms
            $table->decimal('packet_loss', 5, 2)->nullable(); // percentage
            $table->unsignedInteger('rtt')->nullable(); // round trip time ms
            $table->string('codec_used', 50)->nullable();
            $table->enum('quality_rating', ['excellent', 'good', 'fair', 'poor', 'bad'])->nullable();
            $table->timestamp('call_time');
            $table->timestamps();

            $table->foreign('cdr_id')->references('id')->on('cdrs')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('carrier_id')->references('id')->on('carriers')->onDelete('set null');
            $table->index(['customer_id', 'call_time']);
            $table->index(['carrier_id', 'call_time']);
            $table->index(['quality_rating', 'call_time']);
            $table->index('mos_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qos_metrics');
    }
};
