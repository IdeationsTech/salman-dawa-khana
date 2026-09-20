<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');

            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('bill_id')->nullable();
            $table->unsignedBigInteger('received_by_user_id');

            $table->dateTime('payment_date');
            $table->decimal('amount', 12, 2);
            $table->string('method', 20);
            $table->string('reference_no', 80)->nullable();
            $table->string('notes', 255)->nullable();

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('clinics');

            $table->foreign('patient_id')
                ->references('patient_id')
                ->on('patients');

            $table->foreign('bill_id')
                ->references('bill_id')
                ->on('bills');

            $table->foreign('received_by_user_id')
                ->references('user_id')
                ->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};