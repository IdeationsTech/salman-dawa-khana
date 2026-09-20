<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id('visit_id');

            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('recorded_by_user_id');

            $table->dateTime('visit_date');
            $table->string('visit_reason', 255)->nullable();
            $table->text('general_notes')->nullable();
            $table->dateTime('created_at');

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('clinics');

            $table->foreign('patient_id')
                ->references('patient_id')
                ->on('patients');

            $table->foreign('recorded_by_user_id')
                ->references('user_id')
                ->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};