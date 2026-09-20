<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id('bill_id');

            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('visit_id')->nullable();
            $table->unsignedBigInteger('created_by_user_id');

            $table->string('bill_no', 40);
            $table->dateTime('bill_date');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('unpaid');

            $table->unique(['clinic_id', 'bill_no']);

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('clinics');

            $table->foreign('patient_id')
                ->references('patient_id')
                ->on('patients');

            $table->foreign('visit_id')
                ->references('visit_id')
                ->on('visits');

            $table->foreign('created_by_user_id')
                ->references('user_id')
                ->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};