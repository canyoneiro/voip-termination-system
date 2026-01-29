<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_users', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->integer('customer_id');
            $table->string('name', 100);
            $table->string('email', 255);
            $table->string('password', 255);
            $table->enum('role', ['owner', 'manager', 'viewer'])->default('viewer');
            $table->string('phone', 50)->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret', 255)->nullable();
            $table->json('two_factor_recovery_codes')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->unsignedInteger('login_count')->default(0);
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->unique(['customer_id', 'email']);
            $table->index(['customer_id', 'active']);
        });

        // Password resets for customer users
        Schema::create('customer_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_password_reset_tokens');
        Schema::dropIfExists('customer_users');
    }
};
