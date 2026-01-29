<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users table (Laravel default)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->enum('role', ['admin', 'operator', 'viewer'])->default('operator');
            $table->boolean('active')->default(true);
            $table->timestamp('last_login')->nullable();
            $table->timestamps();
        });

        // Password reset tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Cache
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // Jobs
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Customers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 100);
            $table->string('company', 150)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->integer('max_channels')->default(10);
            $table->integer('max_cps')->default(5);
            $table->integer('max_daily_minutes')->nullable();
            $table->integer('max_monthly_minutes')->nullable();
            $table->integer('used_daily_minutes')->default(0);
            $table->integer('used_monthly_minutes')->default(0);
            $table->boolean('active')->default(true);
            $table->enum('billing_type', ['prepaid', 'postpaid'])->default('postpaid');
            $table->decimal('balance', 12, 4)->default(0);
            $table->decimal('credit_limit', 12, 4)->default(0);
            $table->decimal('low_balance_threshold', 12, 4)->default(10);
            $table->string('currency', 3)->default('EUR');
            $table->boolean('auto_suspend_on_zero')->default(true);
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspended_reason')->nullable();
            $table->boolean('portal_enabled')->default(false);
            $table->unsignedBigInteger('rate_plan_id')->nullable();
            $table->unsignedBigInteger('dialing_plan_id')->nullable();
            $table->text('notes')->nullable();
            $table->string('alert_email')->nullable();
            $table->string('alert_telegram_chat_id', 100)->nullable();
            $table->boolean('notify_low_balance')->default(true);
            $table->boolean('notify_channels_warning')->default(true);
            $table->boolean('traces_enabled')->default(false);
            $table->timestamp('traces_until')->nullable();
            $table->timestamps();
        });

        // Customer IPs
        Schema::create('customer_ips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->string('description', 100)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['customer_id', 'ip_address']);
        });

        // Carriers
        Schema::create('carriers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 100);
            $table->string('host');
            $table->integer('port')->default(5060);
            $table->enum('transport', ['udp', 'tcp', 'tls'])->default('udp');
            $table->string('codecs')->nullable();
            $table->integer('priority')->default(1);
            $table->integer('weight')->default(100);
            $table->string('tech_prefix', 50)->nullable();
            $table->integer('strip_digits')->default(0);
            $table->text('prefix_filter')->nullable();
            $table->text('prefix_deny')->nullable();
            $table->integer('max_cps')->default(10);
            $table->integer('max_channels')->default(50);
            $table->enum('state', ['active', 'inactive', 'probing', 'disabled'])->default('active');
            $table->integer('last_options_reply')->nullable();
            $table->timestamp('last_options_time')->nullable();
            $table->integer('failover_count')->default(0);
            $table->integer('daily_calls')->default(0);
            $table->integer('daily_minutes')->default(0);
            $table->integer('daily_failed')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Carrier IPs
        Schema::create('carrier_ips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->string('description', 100)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['carrier_id', 'ip_address']);
        });

        // CDRs
        Schema::create('cdrs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('call_id');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('carrier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_ip', 45)->nullable();
            $table->string('caller', 100)->nullable();
            $table->string('caller_original', 100)->nullable();
            $table->string('callee', 100)->nullable();
            $table->string('callee_original', 100)->nullable();
            $table->string('destination_ip', 45)->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('progress_time')->nullable();
            $table->timestamp('answer_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('duration')->default(0);
            $table->integer('billable_duration')->default(0);
            $table->integer('pdd')->nullable();
            $table->integer('sip_code')->default(0);
            $table->string('sip_reason', 100)->nullable();
            $table->enum('hangup_cause', ['caller', 'callee', 'system', 'timeout', 'rejected', 'failed'])->nullable();
            $table->integer('hangup_sip_code')->nullable();
            $table->string('codecs_offered')->nullable();
            $table->string('codec_used', 50)->nullable();
            $table->string('user_agent')->nullable();
            $table->unsignedBigInteger('destination_prefix_id')->nullable();
            $table->decimal('cost', 12, 6)->default(0);
            $table->decimal('price', 12, 6)->default(0);
            $table->decimal('profit', 12, 6)->default(0);
            $table->decimal('margin_percent', 8, 4)->default(0);
            $table->timestamps();
            $table->index('start_time');
            $table->index('customer_id');
            $table->index('carrier_id');
            $table->index('call_id');
        });

        // Active calls
        Schema::create('active_calls', function (Blueprint $table) {
            $table->id();
            $table->string('call_id')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('carrier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('caller', 100)->nullable();
            $table->string('callee', 100)->nullable();
            $table->string('source_ip', 45)->nullable();
            $table->timestamp('start_time')->nullable();
            $table->boolean('answered')->default(false);
            $table->timestamp('answer_time')->nullable();
            $table->timestamps();
        });

        // Alerts
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->enum('type', [
                'carrier_down', 'carrier_recovered', 'high_failure_rate',
                'cps_exceeded', 'channels_exceeded', 'minutes_warning',
                'minutes_exhausted', 'security_ip_blocked', 'security_flood_detected',
                'system_error', 'qos_degradation', 'fraud_detected'
            ]);
            $table->enum('severity', ['info', 'warning', 'critical'])->default('warning');
            $table->enum('source_type', ['customer', 'carrier', 'system'])->default('system');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_name', 100)->nullable();
            $table->string('title');
            $table->text('message')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('notified_email')->default(false);
            $table->boolean('notified_telegram')->default(false);
            $table->boolean('acknowledged')->default(false);
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
        });

        // SIP traces
        Schema::create('sip_traces', function (Blueprint $table) {
            $table->id();
            $table->string('call_id');
            $table->timestamp('timestamp')->nullable();
            $table->string('source_ip', 45)->nullable();
            $table->integer('source_port')->nullable();
            $table->string('dest_ip', 45)->nullable();
            $table->integer('dest_port')->nullable();
            $table->string('transport', 10)->nullable();
            $table->string('method', 20)->nullable();
            $table->integer('response_code')->nullable();
            $table->enum('direction', ['in', 'out'])->nullable();
            $table->string('from_uri')->nullable();
            $table->string('to_uri')->nullable();
            $table->mediumText('sip_message')->nullable();
            $table->timestamps();
            $table->index('call_id');
        });

        // IP blacklist
        Schema::create('ip_blacklist', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique();
            $table->string('reason')->nullable();
            $table->enum('source', ['manual', 'fail2ban', 'flood_detection', 'scanner'])->default('manual');
            $table->integer('attempts')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('permanent')->default(false);
            $table->timestamps();
        });

        // Daily stats
        Schema::create('daily_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('carrier_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('total_calls')->default(0);
            $table->integer('answered_calls')->default(0);
            $table->integer('failed_calls')->default(0);
            $table->integer('total_duration')->default(0);
            $table->integer('billable_duration')->default(0);
            $table->decimal('asr', 5, 2)->nullable();
            $table->decimal('acd', 8, 2)->nullable();
            $table->integer('avg_pdd')->nullable();
            $table->integer('max_concurrent')->default(0);
            $table->timestamps();
            $table->unique(['date', 'customer_id']);
        });

        // System settings
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50);
            $table->string('name', 100);
            $table->text('value')->nullable();
            $table->enum('type', ['string', 'int', 'bool', 'json'])->default('string');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->unique(['category', 'name']);
        });

        // Audit log
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 100);
            $table->string('entity_type', 50)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        // API tokens
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 100);
            $table->string('token_hash');
            $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('type', ['customer', 'admin', 'integration'])->default('integration');
            $table->json('permissions')->nullable();
            $table->integer('rate_limit')->default(100);
            $table->integer('rate_limit_window')->default(60);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Webhook endpoints
        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('url', 500);
            $table->string('secret');
            $table->json('events')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('last_status_code')->nullable();
            $table->integer('failure_count')->default(0);
            $table->timestamps();
        });

        // Webhook deliveries
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained('webhook_endpoints')->cascadeOnDelete();
            $table->string('event', 50);
            $table->json('payload')->nullable();
            $table->integer('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->integer('attempts')->default(1);
            $table->boolean('success')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhook_endpoints');
        Schema::dropIfExists('api_tokens');
        Schema::dropIfExists('audit_log');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('daily_stats');
        Schema::dropIfExists('ip_blacklist');
        Schema::dropIfExists('sip_traces');
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('active_calls');
        Schema::dropIfExists('cdrs');
        Schema::dropIfExists('carrier_ips');
        Schema::dropIfExists('carriers');
        Schema::dropIfExists('customer_ips');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
