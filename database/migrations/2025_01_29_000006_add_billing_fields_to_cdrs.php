<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cdrs', function (Blueprint $table) {
            $table->unsignedBigInteger('destination_prefix_id')->nullable()->after('user_agent');
            $table->decimal('cost', 10, 6)->nullable()->after('destination_prefix_id');
            $table->decimal('price', 10, 6)->nullable()->after('cost');
            $table->decimal('profit', 10, 6)->nullable()->after('price');
            $table->decimal('margin_percent', 5, 2)->nullable()->after('profit');

            $table->foreign('destination_prefix_id')->references('id')->on('destination_prefixes')->onDelete('set null');
            $table->index(['customer_id', 'start_time'], 'cdrs_customer_time_idx');
            $table->index(['carrier_id', 'start_time'], 'cdrs_carrier_time_idx');
        });
    }

    public function down(): void
    {
        Schema::table('cdrs', function (Blueprint $table) {
            $table->dropForeign(['destination_prefix_id']);
            $table->dropIndex('cdrs_customer_time_idx');
            $table->dropIndex('cdrs_carrier_time_idx');
            $table->dropColumn(['destination_prefix_id', 'cost', 'price', 'profit', 'margin_percent']);
        });
    }
};
