<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id('expense_id');

            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('recorded_by_user_id');

            $table->dateTime('expense_date');
            $table->string('category', 80);
            $table->string('description', 255)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 20)->default('cash');

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('clinics');

            $table->foreign('recorded_by_user_id')
                ->references('user_id')
                ->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};