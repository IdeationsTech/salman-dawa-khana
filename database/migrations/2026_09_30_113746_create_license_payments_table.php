<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_payments', function (Blueprint $table) {
            $table->id('license_payment_id');

            $table->unsignedBigInteger('clinic_license_id');
            $table->unsignedBigInteger('recorded_by_platform_admin_id');
            $table->unsignedBigInteger('verified_by_platform_admin_id')->nullable();

            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('AED');
            $table->string('payment_method', 30);
            $table->string('reference_no', 100)->nullable();
            $table->timestamp('paid_at');
            $table->string('status', 25)->default('pending_verification');
            $table->string('receipt_path', 255)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('clinic_license_id')
                ->references('clinic_license_id')
                ->on('clinic_licenses');

            $table->foreign('recorded_by_platform_admin_id')
                ->references('platform_admin_id')
                ->on('platform_admins');

            $table->foreign('verified_by_platform_admin_id')
                ->references('platform_admin_id')
                ->on('platform_admins');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_payments');
    }
};