<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_audit_logs', function (Blueprint $table) {
            $table->id('platform_audit_log_id');

            $table->unsignedBigInteger('platform_admin_id')->nullable();
            $table->unsignedBigInteger('clinic_id')->nullable();

            $table->string('action', 100);
            $table->string('entity_type', 100);
            $table->unsignedBigInteger('entity_id')->nullable();

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['platform_admin_id', 'created_at']);
            $table->index(['clinic_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);

            $table->foreign('platform_admin_id')
                ->references('platform_admin_id')
                ->on('platform_admins')
                ->nullOnDelete();

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('clinics')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_audit_logs');
    }
};

