<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrier_rates', function (Blueprint $table) {
            $table->id();
            $table->integer('carrier_id');
            $table->unsignedBigInteger('destination_prefix_id');
            $table->decimal('cost_per_minute', 10, 6);
            $table->decimal('connection_fee', 10, 6)->default(0);
            $table->unsignedTinyInteger('billing_increment')->default(1); // seconds
            $table->unsignedTinyInteger('min_duration')->default(0); // minimum billable seconds
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('carrier_id')->references('id')->on('carriers')->onDelete('cascade');
            $table->foreign('destination_prefix_id')->references('id')->on('destination_prefixes')->onDelete('cascade');
            $table->unique(['carrier_id', 'destination_prefix_id', 'effective_date'], 'carrier_rate_unique');
            $table->index(['carrier_id', 'active']);
            $table->index('effective_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrier_rates');
    }
};
