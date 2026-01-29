<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dialing_plans', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('default_action', ['allow', 'deny'])->default('allow');
            $table->boolean('block_premium')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('active');
        });

        Schema::create('dialing_plan_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dialing_plan_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['allow', 'deny']);
            $table->string('pattern', 50);
            $table->string('description', 255)->nullable();
            $table->integer('priority')->default(100);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['dialing_plan_id', 'priority']);
            $table->index('pattern');
        });

        // Add dialing_plan_id to customers (if not exists)
        if (!Schema::hasColumn('customers', 'dialing_plan_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->unsignedBigInteger('dialing_plan_id')->nullable();
                $table->foreign('dialing_plan_id')->references('id')->on('dialing_plans')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customers', 'dialing_plan_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropForeign(['dialing_plan_id']);
                $table->dropColumn('dialing_plan_id');
            });
        }

        Schema::dropIfExists('dialing_plan_rules');
        Schema::dropIfExists('dialing_plans');
    }
};
