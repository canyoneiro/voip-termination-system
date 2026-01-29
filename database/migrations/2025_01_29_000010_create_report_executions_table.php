<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_executions', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->unsignedBigInteger('scheduled_report_id');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->enum('trigger_type', ['scheduled', 'manual'])->default('scheduled');
            $table->integer('triggered_by')->nullable();
            $table->date('report_date_from');
            $table->date('report_date_to');
            $table->string('file_path', 500)->nullable();
            $table->string('file_path_csv', 500)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->unsignedInteger('records_count')->nullable();
            $table->json('metadata')->nullable(); // summary stats
            $table->text('error_message')->nullable();
            $table->unsignedSmallInteger('email_sent_count')->default(0);
            $table->unsignedSmallInteger('email_failed_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('scheduled_report_id')->references('id')->on('scheduled_reports')->onDelete('cascade');
            $table->foreign('triggered_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['scheduled_report_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_executions');
    }
};
