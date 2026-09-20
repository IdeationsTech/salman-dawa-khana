<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id('patient_id');

            $table->unsignedBigInteger('clinic_id');
            $table->string('patient_code', 30);
            $table->string('full_name', 160);
            $table->string('father_or_husband_name', 160)->nullable();
            $table->string('phone', 30)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('address', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('active');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();

            $table->unique(['clinic_id', 'patient_code']);
            $table->index(['clinic_id', 'phone']);

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('clinics');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};