<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nuskha_order_refunds', function (Blueprint $table) {
            $table->bigIncrements('nuskha_order_refund_id');

            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('nuskha_order_id');
            $table->unsignedBigInteger('refunded_by_user_id');
            $table->unsignedBigInteger('refund_payment_id');

            $table->uuid('request_key');
            $table->dateTime('refunded_at');
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3);
            $table->string('method', 20);
            $table->string('reference_no', 100)->nullable();
            $table->text('reason');
            $table->timestamps();

            $table->foreign('clinic_id')
                ->references('clinic_id')->on('clinics')
                ->restrictOnDelete();

            $table->foreign('nuskha_order_id')
                ->references('nuskha_order_id')->on('nuskha_orders')
                ->restrictOnDelete();

            $table->foreign('refunded_by_user_id')
                ->references('user_id')->on('users')
                ->restrictOnDelete();

            $table->foreign('refund_payment_id')
                ->references('nuskha_order_payment_id')
                ->on('nuskha_order_payments')
                ->restrictOnDelete();

            $table->unique('refund_payment_id');
            $table->unique(
                ['clinic_id', 'request_key'],
                'order_refunds_request_unique'
            );

            $table->index(['clinic_id', 'refunded_at']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 160)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nuskha_order_refunds');
        Schema::dropIfExists('password_reset_tokens');
    }
};