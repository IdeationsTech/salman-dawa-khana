<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id('ledger_entry_id');

            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('bill_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('expense_id')->nullable();
            $table->unsignedBigInteger('created_by_user_id');

            $table->dateTime('entry_date');
            $table->string('entry_type', 20);
            $table->decimal('debit', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);
            $table->string('description', 255)->nullable();

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('clinics');

            $table->foreign('patient_id')
                ->references('patient_id')
                ->on('patients');

            $table->foreign('bill_id')
                ->references('bill_id')
                ->on('bills');

            $table->foreign('payment_id')
                ->references('payment_id')
                ->on('payments');

            $table->foreign('expense_id')
                ->references('expense_id')
                ->on('expenses');

            $table->foreign('created_by_user_id')
                ->references('user_id')
                ->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};