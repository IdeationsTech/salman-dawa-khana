<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id('setting_id');

            $table->unsignedBigInteger('clinic_id');
            $table->string('setting_key', 100);
            $table->text('setting_value')->nullable();

            $table->unique(['clinic_id', 'setting_key']);

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('clinics');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};