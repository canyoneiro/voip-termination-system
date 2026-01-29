<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destination_prefixes', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 20)->unique();
            $table->string('country_code', 3)->nullable();
            $table->string('country_name', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('description', 255)->nullable();
            $table->boolean('is_mobile')->default(false);
            $table->boolean('is_premium')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('prefix');
            $table->index('country_code');
            $table->index('is_premium');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_prefixes');
    }
};
