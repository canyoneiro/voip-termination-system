<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_imports', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->integer('user_id')->nullable();
            $table->enum('type', ['carrier', 'customer', 'rate_plan', 'destinations']);
            $table->integer('carrier_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->unsignedBigInteger('rate_plan_id')->nullable();
            $table->string('filename', 255);
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->unsignedInteger('skipped_rows')->default(0);
            $table->json('errors')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('carrier_id')->references('id')->on('carriers')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('rate_plan_id')->references('id')->on('rate_plans')->onDelete('cascade');
            $table->index(['type', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_imports');
    }
};
