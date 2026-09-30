<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->unsignedBigInteger('saas_customer_id')->nullable();
            $table->string('onboarding_status', 20)->default('approved');
            $table->unsignedBigInteger('reviewed_by_platform_admin_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('logo_path', 255)->nullable();

            $table->foreign('saas_customer_id')
                ->references('saas_customer_id')
                ->on('saas_customers')
                ->nullOnDelete();

            $table->foreign('reviewed_by_platform_admin_id')
                ->references('platform_admin_id')
                ->on('platform_admins')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropForeign(['saas_customer_id']);
            $table->dropForeign(['reviewed_by_platform_admin_id']);

            $table->dropColumn([
                'saas_customer_id',
                'onboarding_status',
                'reviewed_by_platform_admin_id',
                'reviewed_at',
                'rejection_reason',
                'logo_path',
            ]);
        });
    }
};