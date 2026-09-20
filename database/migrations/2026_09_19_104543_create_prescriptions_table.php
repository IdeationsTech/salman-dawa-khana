<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id('prescription_id');

            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('visit_id')->nullable();
            $table->unsignedBigInteger('created_by_user_id');

            $table->string('prescription_no', 40);
            $table->dateTime('prescribed_at');
            $table->text('general_instructions')->nullable();
            $table->string('status', 20)->default('issued');

            $table->unique(['clinic_id', 'prescription_no']);

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
        Schema::dropIfExists('prescriptions');
    }
};