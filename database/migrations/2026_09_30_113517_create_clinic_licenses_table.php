<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_licenses', function (Blueprint $table) {
            $table->id('clinic_license_id');

            $table->unsignedBigInteger('clinic_id')->unique();
            $table->unsignedBigInteger('subscription_plan_id');
            $table->unsignedBigInteger('issued_by_platform_admin_id');

            $table->string('status', 20);
            $table->string('grant_type', 30);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->text('grant_reason')->nullable();

            $table->timestamps();

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('clinics');

            $table->foreign('subscription_plan_id')
                ->references('subscription_plan_id')
                ->on('subscription_plans');

            $table->foreign('issued_by_platform_admin_id')
                ->references('platform_admin_id')
                ->on('platform_admins');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_licenses');
    }
};