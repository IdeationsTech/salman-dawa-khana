<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nuskha_templates', function (Blueprint $table) {
            $table->id('nuskha_template_id');

            $table->unsignedBigInteger('clinic_id');
            $table->string('name', 160);
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('clinics');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nuskha_templates');
    }
};