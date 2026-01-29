<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_portal_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->unique();
            $table->boolean('portal_enabled')->default(false);
            $table->boolean('allow_api_tokens')->default(true);
            $table->boolean('allow_ip_requests')->default(true);
            $table->boolean('allow_webhook_management')->default(false);
            $table->boolean('show_billing_summary')->default(true);
            $table->boolean('show_carrier_names')->default(false);
            $table->boolean('show_sip_traces')->default(false);
            $table->boolean('show_cost_info')->default(false);
            $table->unsignedInteger('cdr_retention_days')->default(90);
            $table->unsignedInteger('max_api_tokens')->default(5);
            $table->unsignedInteger('max_users')->default(5);
            $table->json('allowed_features')->nullable();
            $table->string('custom_logo', 255)->nullable();
            $table->string('custom_theme', 50)->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        // Add portal_enabled to customers for quick check
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('portal_enabled')->default(false)->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('portal_enabled');
        });
        Schema::dropIfExists('customer_portal_settings');
    }
};
