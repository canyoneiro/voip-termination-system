<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('cdrs', 'destination_prefix_id')) {
            Schema::table('cdrs', function (Blueprint $table) {
                $table->unsignedBigInteger('destination_prefix_id')->nullable();
                $table->decimal('cost', 10, 6)->nullable();
                $table->decimal('price', 10, 6)->nullable();
                $table->decimal('profit', 10, 6)->nullable();
                $table->decimal('margin_percent', 5, 2)->nullable();

                $table->foreign('destination_prefix_id')->references('id')->on('destination_prefixes')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cdrs', 'destination_prefix_id')) {
            Schema::table('cdrs', function (Blueprint $table) {
                $table->dropForeign(['destination_prefix_id']);
                $table->dropColumn(['destination_prefix_id', 'cost', 'price', 'profit', 'margin_percent']);
            });
        }
    }
};
